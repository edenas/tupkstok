<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeWordPressMigration;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\WordPressMigration\WordPressMigrationReportBuilder;
use App\Services\WordPressMigration\WordPressMigrationManager;
use App\Services\WordPressMigration\WordPressSqlAnalyzer;
use App\Services\WordPressMigration\WordPressUploadsAnalyzer;
use App\Services\WordPressMigration\WordPressXmlAnalyzer;
use App\Services\WordPressMigration\WordPressWorkspaceScanner;
use App\Services\WordPressMigration\SafeArchiveExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminWordPressMigrationTest extends TestCase
{
    use RefreshDatabase;

    private string $storageRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storageRoot = storage_path('framework/testing/wordpress-imports-'.Str::uuid());
        config(['wordpress-migration.storage_path' => $this->storageRoot]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->storageRoot);
        parent::tearDown();
    }

    public function test_access_requires_an_administrator(): void
    {
        $this->get(route('admin.wordpress-migration.index'))->assertRedirect('/admin');
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('admin.wordpress-migration.index'))->assertForbidden();
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->get(route('admin.wordpress-migration.index'))->assertOk()->assertSee('WordPress importavimo centras');
    }

    public function test_valid_sources_are_stored_privately_and_session_can_be_deleted(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator']);
        $response = $this->actingAs($administrator)->post(route('admin.wordpress-migration.store'), [
            'wordpress_xml' => UploadedFile::fake()->createWithContent('export.xml', $this->xml()),
            'wordpress_sql' => UploadedFile::fake()->createWithContent('database.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n"),
            'uploads_zip' => UploadedFile::fake()->createWithContent('uploads.zip', "PK\x05\x06".str_repeat("\0", 18)),
        ]);
        $response->assertRedirect(route('admin.wordpress-migration.index'))->assertSessionHas('success');
        $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0] ?? null;
        $this->assertNotNull($manifestPath);
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertFileExists(dirname($manifestPath).'/source/wordpress.xml');
        $this->assertStringNotContainsString('public', dirname($manifestPath));
        $this->actingAs($administrator)->delete(route('admin.wordpress-migration.destroy', $manifest['id']))->assertRedirect();
        $this->assertDirectoryDoesNotExist(dirname($manifestPath));
    }

    public function test_workspace_selects_sources_and_creates_a_private_session(): void
    {
        $workspace = storage_path('framework/testing/wp-workspace-'.Str::uuid());
        File::ensureDirectoryExists($workspace);
        config(['wordpress-migration.workspace_path' => $workspace]);
        file_put_contents($workspace.'/older.xml', $this->xml());
        touch($workspace.'/older.xml', time() - 60);
        file_put_contents($workspace.'/newest.xml', $this->xml());
        file_put_contents($workspace.'/wordpress.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n");
        file_put_contents($workspace.'/uploads.zip', "PK\x05\x06".str_repeat("\0", 18));
        try {
            $administrator = User::factory()->create(['role' => 'administrator']);
            $this->actingAs($administrator)->get(route('admin.wordpress-migration.index'))
                ->assertOk()->assertSee('WordPress importavimo centras')->assertSee('newest.xml')->assertSee('Naudoti Workspace');
            $this->actingAs($administrator)->post(route('admin.wordpress-migration.workspace.store'))
                ->assertRedirect(route('admin.wordpress-migration.index'))->assertSessionHas('success');
            $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0] ?? null;
            $this->assertNotNull($manifestPath);
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $this->assertSame('workspace', $manifest['source_mode']);
            $this->assertSame('newest.xml', $manifest['files']['wordpress_xml']['original_name']);
            $this->assertSame('zip', $manifest['media_source']['source_type']);
        } finally { File::deleteDirectory($workspace); }
    }

    public function test_raw_uploads_directory_has_priority_and_is_analyzed_without_zip_extension(): void
    {
        $workspace = storage_path('framework/testing/wp-raw-workspace-'.Str::uuid());
        File::ensureDirectoryExists($workspace.'/uploads/2020/01');
        config(['wordpress-migration.workspace_path' => $workspace]);
        file_put_contents($workspace.'/wordpress.xml', $this->xml());
        file_put_contents($workspace.'/wordpress.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n");
        file_put_contents($workspace.'/uploads/2020/01/image.jpg', 'image-content');
        file_put_contents($workspace.'/uploads/danger.php', '<?php echo 1;');
        file_put_contents($workspace.'/uploads.zip', "PK\x05\x06".str_repeat("\0", 18));
        $sourceHashes = [hash_file('sha256', $workspace.'/uploads/2020/01/image.jpg'), hash_file('sha256', $workspace.'/uploads/danger.php')];
        try {
            $scanner = app(WordPressWorkspaceScanner::class);
            $scan = $scanner->scan();
            $this->assertSame('directory', $scan['uploads']['source_type']);
            $this->assertFalse($scan['uploads']['requires_extraction']);

            $administrator = User::factory()->create(['role' => 'administrator']);
            $this->actingAs($administrator)->post(route('admin.wordpress-migration.workspace.store'))->assertSessionHas('success');
            $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0];
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $this->assertSame('directory', $manifest['media_source']['source_type']);
            $this->assertFileDoesNotExist(dirname($manifestPath).'/source/uploads.zip');

            $this->app->bind(SafeArchiveExtractor::class, fn () => throw new \RuntimeException('ZIP extractor must not resolve for a directory source.'));
            Queue::fake();
            $this->actingAs($administrator)->post(route('admin.wordpress-migration.analyze', $manifest['id']))
                ->assertRedirect(route('admin.wordpress-migration.index'))->assertSessionHas('success');
            Queue::assertPushed(AnalyzeWordPressMigration::class, fn ($job) => $job->importId === $manifest['id']);
            (new AnalyzeWordPressMigration($manifest['id']))->handle(app(\App\Services\WordPressMigration\WordPressMigrationManager::class));
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $report = json_decode(file_get_contents(dirname($manifestPath).'/'.$manifest['analysis_report_path']), true);
            $this->assertSame('directory', $report['uploads']['media_source_type']);
            $this->assertContains('danger.php', array_column($report['uploads']['suspicious_executable_files'], 'path'));
            $this->assertSame($sourceHashes, [hash_file('sha256', $workspace.'/uploads/2020/01/image.jpg'), hash_file('sha256', $workspace.'/uploads/danger.php')]);
        } finally { File::deleteDirectory($workspace); }
    }

    public function test_workspace_scanner_handles_directory_only_and_no_media_source(): void
    {
        $workspace = storage_path('framework/testing/wp-media-cases-'.Str::uuid()); File::ensureDirectoryExists($workspace.'/uploads');
        config(['wordpress-migration.workspace_path' => $workspace]);
        try {
            $this->assertSame('directory', app(WordPressWorkspaceScanner::class)->scan()['uploads']['source_type']);
            File::deleteDirectory($workspace.'/uploads');
            $this->assertNull(app(WordPressWorkspaceScanner::class)->scan()['uploads']);
        } finally { File::deleteDirectory($workspace); }
    }

    public function test_directory_outside_workspace_is_rejected(): void
    {
        $workspace = storage_path('framework/testing/wp-boundary-'.Str::uuid());
        $outside = storage_path('framework/testing/wp-outside-'.Str::uuid());
        File::ensureDirectoryExists($workspace); File::ensureDirectoryExists($outside);
        config(['wordpress-migration.workspace_path' => $workspace]);
        try {
            $this->expectException(\RuntimeException::class);
            app(WordPressWorkspaceScanner::class)->validateDirectory($outside);
        } finally { File::deleteDirectory($workspace); File::deleteDirectory($outside); }
    }

    public function test_zip_extension_error_is_reported_only_for_a_zip_source(): void
    {
        if (class_exists(\ZipArchive::class)) $this->markTestSkipped('This assertion covers runtimes without ext-zip.');
        $workspace = storage_path('framework/testing/wp-zip-workspace-'.Str::uuid()); File::ensureDirectoryExists($workspace);
        config(['wordpress-migration.workspace_path' => $workspace]);
        file_put_contents($workspace.'/wordpress.xml', $this->xml());
        file_put_contents($workspace.'/wordpress.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n");
        file_put_contents($workspace.'/uploads.zip', "PK\x05\x06".str_repeat("\0", 18));
        try {
            $administrator = User::factory()->create(['role' => 'administrator']);
            $this->actingAs($administrator)->post(route('admin.wordpress-migration.workspace.store'))->assertSessionHas('success');
            $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0]; $manifest = json_decode(file_get_contents($manifestPath), true);
            Queue::fake();
            $this->actingAs($administrator)->post(route('admin.wordpress-migration.analyze', $manifest['id']))->assertRedirect(route('admin.wordpress-migration.index'));
            (new AnalyzeWordPressMigration($manifest['id']))->handle(app(\App\Services\WordPressMigration\WordPressMigrationManager::class));
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $this->assertSame('failed', $manifest['analysis_status']);
            $this->assertStringContainsString('ZIP', $manifest['errors'][0]);
        } finally { File::deleteDirectory($workspace); }
    }

    public function test_symbolic_uploads_directory_is_rejected_when_supported(): void
    {
        $workspace = storage_path('framework/testing/wp-link-workspace-'.Str::uuid());
        $outside = storage_path('framework/testing/wp-link-target-'.Str::uuid());
        File::ensureDirectoryExists($workspace); File::ensureDirectoryExists($outside);
        config(['wordpress-migration.workspace_path' => $workspace]);
        if (! @symlink($outside, $workspace.'/uploads')) { File::deleteDirectory($workspace); File::deleteDirectory($outside); $this->markTestSkipped('Symbolic links are unavailable in this runtime.'); }
        try {
            $scan = app(WordPressWorkspaceScanner::class)->scan();
            $this->assertNull($scan['uploads']);
            $this->assertNotNull($scan['media_error']);
        } finally { @unlink($workspace.'/uploads'); File::deleteDirectory($workspace); File::deleteDirectory($outside); }
    }

    public function test_analyze_request_only_queues_the_job_and_does_not_run_analysis(): void
    {
        Queue::fake();
        $this->mock(WordPressUploadsAnalyzer::class, fn ($mock) => $mock->shouldNotReceive('analyze'));
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.store'), [
            'wordpress_xml' => UploadedFile::fake()->createWithContent('export.xml', $this->xml()),
            'wordpress_sql' => UploadedFile::fake()->createWithContent('database.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n"),
            'uploads_zip' => UploadedFile::fake()->createWithContent('uploads.zip', "PK\x05\x06".str_repeat("\0", 18)),
        ]);
        $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0];
        $manifest = json_decode(file_get_contents($manifestPath), true);

        $this->actingAs($administrator)->post(route('admin.wordpress-migration.analyze', $manifest['id']))
            ->assertRedirect(route('admin.wordpress-migration.index'));

        Queue::assertPushed(AnalyzeWordPressMigration::class, 1);
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertSame('queued', $manifest['analysis_status']);
        $this->assertArrayHasKey('analysis_queued_at', $manifest);
        $this->assertArrayNotHasKey('analysis_report_path', $manifest);
        $this->assertDirectoryDoesNotExist(dirname($manifestPath).'/extracted');
    }

    public function test_administrator_is_warned_when_a_queued_analysis_has_not_started(): void
    {
        config(['wordpress-migration.queue_stale_after_seconds' => 60]);
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.store'), [
            'wordpress_xml' => UploadedFile::fake()->createWithContent('export.xml', $this->xml()),
            'wordpress_sql' => UploadedFile::fake()->createWithContent('database.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n"),
            'uploads_zip' => UploadedFile::fake()->createWithContent('uploads.zip', "PK\x05\x06".str_repeat("\0", 18)),
        ]);
        $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0];
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $manifest['analysis_status'] = 'queued';
        $manifest['analysis_queued_at'] = now()->subMinutes(2)->toIso8601String();
        file_put_contents($manifestPath, json_encode($manifest));

        $this->actingAs($administrator)->get(route('admin.wordpress-migration.index'))
            ->assertOk()
            ->assertSee('Eilės užduotis nepradėta.')
            ->assertSee('php artisan queue:work');
    }

    public function test_database_queue_is_configured_and_has_its_required_tables(): void
    {
        $this->assertSame('database', config('queue.connections.database.driver'));
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertTrue(Schema::hasTable('failed_jobs'));
    }

    public function test_invalid_signatures_are_rejected(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.store'), [
            'wordpress_xml' => UploadedFile::fake()->createWithContent('export.xml', 'not xml'),
            'wordpress_sql' => UploadedFile::fake()->createWithContent('database.sql.gz', 'not gzip'),
            'uploads_zip' => UploadedFile::fake()->createWithContent('uploads.zip', 'not zip'),
        ])->assertSessionHasErrors(['wordpress_xml', 'wordpress_sql', 'uploads_zip']);
        $this->assertDirectoryDoesNotExist($this->storageRoot);
    }

    public function test_future_import_and_seo_settings_are_saved_in_the_manifest(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.store'), [
            'wordpress_xml' => UploadedFile::fake()->createWithContent('export.xml', $this->xml()),
            'wordpress_sql' => UploadedFile::fake()->createWithContent('database.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n"),
            'uploads_zip' => UploadedFile::fake()->createWithContent('uploads.zip', "PK\x05\x06".str_repeat("\0", 18)),
        ]);
        $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0];
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $root = dirname($manifestPath); $xml = app(WordPressXmlAnalyzer::class)->analyze($root.'/source/wordpress.xml'); $sql = app(WordPressSqlAnalyzer::class)->analyze($root.'/source/wordpress.sql');
        $uploads = ['total_files' => 0, 'image_files' => 0, 'duplicates' => [], 'importable_media' => [], 'protection_files' => [], 'ignored_files' => [], 'suspicious_executable_files' => [], 'high_risk_executable_files' => [], 'missing_referenced_files' => []];
        File::ensureDirectoryExists($root.'/reports'); file_put_contents($root.'/reports/analysis.json', json_encode(app(WordPressMigrationReportBuilder::class)->build($xml, $sql, $uploads)));
        $manifest['analysis_status'] = 'complete'; $manifest['analysis_report_path'] = 'reports/analysis.json';
        file_put_contents($manifestPath, json_encode($manifest));

        $this->actingAs($administrator)->get(route('admin.wordpress-migration.settings'))
            ->assertOk()->assertSee('Migracijos santrauka')->assertSee('Vienodos medijos kopijos')->assertSee('Medijos ir saugumo parinktys')->assertSee('Po importavimo turėsite')->assertSee('role="progressbar"', false)->assertSee('60%');

        $this->actingAs($administrator)->put(route('admin.wordpress-migration.settings.update'), [
            'options' => ['posts' => 1, 'images' => 1, 'skip_ignored_files' => 1, 'duplicate_media' => 'import', 'image_variants' => 'originals', 'verify_media_checksums' => 1, 'block_high_risk_executables' => 1, 'not_allowed' => 1],
        ])->assertRedirect(route('admin.wordpress-migration.seo'));
        $this->actingAs($administrator)->put(route('admin.wordpress-migration.seo.update'), [
            'slug_conflicts' => 'generate', 'meta_title' => 1, 'canonical_url' => 1, 'redirect_handling' => 'automatic',
        ])->assertRedirect(route('admin.wordpress-migration.ready'));

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertTrue($manifest['import_settings']['posts']);
        $this->assertTrue($manifest['import_settings']['images']);
        $this->assertFalse($manifest['import_settings']['comments']);
        $this->assertTrue($manifest['import_settings']['skip_ignored_files']);
        $this->assertTrue($manifest['import_settings']['import_duplicate_media']);
        $this->assertFalse($manifest['import_settings']['skip_duplicate_media']);
        $this->assertTrue($manifest['import_settings']['import_original_images_only']);
        $this->assertFalse($manifest['import_settings']['import_generated_thumbnails']);
        $this->assertTrue($manifest['import_settings']['block_high_risk_executables']);
        $this->assertSame('generate', $manifest['seo_settings']['slug_conflicts']);
        $this->assertFalse($manifest['seo_settings']['meta_description']);
        $this->assertArrayNotHasKey('not_allowed', $manifest['import_settings']);
    }

    public function test_import_settings_are_saved_asynchronously_with_normalized_radio_values(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.store'), ['wordpress_xml' => UploadedFile::fake()->createWithContent('export.xml', $this->xml()), 'wordpress_sql' => UploadedFile::fake()->createWithContent('database.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n"), 'uploads_zip' => UploadedFile::fake()->createWithContent('uploads.zip', "PK\x05\x06".str_repeat("\0", 18))]);
        $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0]; $manifest = json_decode(file_get_contents($manifestPath), true); $manifest['analysis_status'] = 'complete'; file_put_contents($manifestPath, json_encode($manifest));

        $this->actingAs($administrator)->putJson(route('admin.wordpress-migration.settings.update'), ['options' => ['duplicate_media' => 'both', 'image_variants' => 'all']])->assertUnprocessable();
        $this->actingAs($administrator)->putJson(route('admin.wordpress-migration.settings.update'), ['options' => ['duplicate_media' => 'skip', 'image_variants' => 'all', 'verify_media_checksums' => true]])
            ->assertOk()->assertJson(['message' => 'Nustatymai išsaugoti.']);

        $settings = json_decode(file_get_contents($manifestPath), true)['import_settings'];
        $this->assertTrue($settings['skip_duplicate_media']); $this->assertFalse($settings['import_duplicate_media']);
        $this->assertFalse($settings['import_original_images_only']); $this->assertTrue($settings['import_generated_thumbnails']);

        $this->actingAs($administrator)->putJson(route('admin.wordpress-migration.seo.update'), ['slug_conflicts' => 'skip', 'redirect_handling' => 'none'])
            ->assertOk()->assertJson(['message' => 'SEO nustatymai išsaugoti.']);
    }

    public function test_analysis_ui_shows_relative_findings_and_limits_each_visible_list(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.store'), [
            'wordpress_xml' => UploadedFile::fake()->createWithContent('export.xml', $this->xml()),
            'wordpress_sql' => UploadedFile::fake()->createWithContent('database.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n"),
            'uploads_zip' => UploadedFile::fake()->createWithContent('uploads.zip', "PK\x05\x06".str_repeat("\0", 18)),
        ]);
        $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0]; $root = dirname($manifestPath);
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $xml = app(WordPressXmlAnalyzer::class)->analyze($root.'/source/wordpress.xml');
        $sql = app(WordPressSqlAnalyzer::class)->analyze($root.'/source/wordpress.sql');
        $findings = array_map(fn ($number) => ['path' => "private/relative-{$number}.php", 'extension' => 'php', 'size' => 20, 'classification' => 'suspicious_executable_files', 'reason' => 'Vykdomasis failas neatitinka patvirtinto apsauginio failo turinio.'], range(1, 21));
        $uploads = ['total_files' => 21, 'image_files' => 0, 'duplicates' => [], 'importable_media' => [], 'protection_files' => [], 'ignored_files' => [], 'suspicious_executable_files' => $findings, 'high_risk_executable_files' => [], 'missing_referenced_files' => [], 'media_source_type' => 'zip'];
        $report = app(WordPressMigrationReportBuilder::class)->build($xml, $sql, $uploads);
        File::ensureDirectoryExists($root.'/reports'); file_put_contents($root.'/reports/analysis.json', json_encode($report));
        $manifest['analysis_status'] = 'complete'; $manifest['analysis_report_path'] = 'reports/analysis.json'; file_put_contents($manifestPath, json_encode($manifest));

        $this->actingAs($administrator)->get(route('admin.wordpress-migration.analysis'))
            ->assertOk()
            ->assertSee('Vykdomieji failai, kuriuos reikia peržiūrėti (21)')
            ->assertSee('private/relative-1.php')
            ->assertSee('Rodomi pirmi 20 iš 21 failų.')
            ->assertDontSee('private/relative-21.php');
    }

    public function test_legacy_report_is_normalized_before_the_complete_wizard_renders(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.store'), [
            'wordpress_xml' => UploadedFile::fake()->createWithContent('export.xml', $this->xml()),
            'wordpress_sql' => UploadedFile::fake()->createWithContent('database.sql', "CREATE TABLE `wp_posts` (`ID` bigint);\n"),
            'uploads_zip' => UploadedFile::fake()->createWithContent('uploads.zip', "PK\x05\x06".str_repeat("\0", 18)),
        ]);
        $manifestPath = glob($this->storageRoot.'/*/manifest.json')[0]; $root = dirname($manifestPath);
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $xml = app(WordPressXmlAnalyzer::class)->analyze($root.'/source/wordpress.xml');
        $sql = app(WordPressSqlAnalyzer::class)->analyze($root.'/source/wordpress.sql');
        $legacyReport = ['generated_at' => now()->toIso8601String(), 'wordpress' => $xml, 'sql' => $sql, 'uploads' => ['total_files' => 3, 'image_files' => 1, 'duplicates' => [], 'suspicious_files' => ['legacy/tool.php'], 'unsupported_files' => ['legacy/tool.php', 'legacy/document.pdf'], 'missing_referenced_files' => []], 'warnings' => [], 'errors' => ['Išskleistuose failuose rasta įtartinų vykdomųjų failų.']];
        File::ensureDirectoryExists($root.'/reports'); file_put_contents($root.'/reports/analysis.json', json_encode($legacyReport));
        $manifest['analysis_status'] = 'complete'; $manifest['analysis_report_path'] = 'reports/analysis.json'; file_put_contents($manifestPath, json_encode($manifest));

        $normalized = app(WordPressMigrationManager::class)->report($manifest);
        $this->assertSame(3, $normalized['report_version']);
        foreach (['importable_media', 'protection_files', 'ignored_files', 'suspicious_executable_files', 'high_risk_executable_files'] as $key) $this->assertArrayHasKey($key, $normalized['uploads']);
        $this->assertSame(['legacy/tool.php'], array_column($normalized['uploads']['suspicious_executable_files'], 'path'));
        $this->assertSame(['legacy/document.pdf'], array_column($normalized['uploads']['ignored_files'], 'path'));
        $this->assertSame(1, $normalized['summary']['importable_media']);

        $this->actingAs($administrator)->get(route('admin.wordpress-migration.index'))->assertOk();
        $this->actingAs($administrator)->get(route('admin.wordpress-migration.analysis'))->assertOk()->assertSee('legacy/tool.php');
        $this->actingAs($administrator)->get(route('admin.wordpress-migration.settings'))->assertOk();
        $this->actingAs($administrator)->put(route('admin.wordpress-migration.settings.update'), ['options' => ['posts' => 1]])->assertRedirect(route('admin.wordpress-migration.seo'));
        $this->actingAs($administrator)->get(route('admin.wordpress-migration.seo'))->assertOk();
        $this->actingAs($administrator)->put(route('admin.wordpress-migration.seo.update'), ['slug_conflicts' => 'skip', 'redirect_handling' => 'none'])->assertRedirect(route('admin.wordpress-migration.ready'));
        $this->actingAs($administrator)->get(route('admin.wordpress-migration.ready'))->assertOk()
            ->assertSee('Pasirengimo būsena')->assertSee('Bus importuojama')->assertSee('Reikia peržiūrėti')->assertSee('Bus praleista')->assertSee('Pasirengimas baigtas');
    }

    public function test_xml_sql_and_report_analysis_discovers_metadata(): void
    {
        $blogPostCount = BlogPost::count();
        $xmlPath = storage_path('framework/testing/wp-test.xml'); $sqlPath = storage_path('framework/testing/wp-test.sql');
        file_put_contents($xmlPath, $this->xml());
        file_put_contents($sqlPath, "CREATE TABLE `abc_posts` (`ID` bigint);\nCREATE TABLE `abc_postmeta` (`meta_key` text);\nINSERT INTO `abc_postmeta` VALUES (1,1,'_yoast_wpseo_title','Title'),(2,1,'views_count','3');\n");
        try {
            $xml = app(WordPressXmlAnalyzer::class)->analyze($xmlPath);
            $sql = app(WordPressSqlAnalyzer::class)->analyze($sqlPath);
            $uploads = ['image_files' => 0, 'missing_referenced_files' => [], 'importable_media' => [], 'protection_files' => [], 'ignored_files' => [], 'suspicious_executable_files' => [], 'high_risk_executable_files' => []];
            $report = app(WordPressMigrationReportBuilder::class)->build($xml, $sql, $uploads);
            $this->assertSame(1, $xml['summary']['post_count']);
            $this->assertSame('abc_', $sql['table_prefix']);
            $this->assertContains('Yoast SEO', $sql['seo']['detected_systems']);
            $this->assertSame(1, $report['summary']['posts']);
            $this->assertTrue($report['readiness']['import_ready']);
            $this->assertSame($blogPostCount, BlogPost::count());
        } finally { @unlink($xmlPath); @unlink($sqlPath); }
    }

    private function xml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:wp="http://wordpress.org/export/1.2/" xmlns:content="http://purl.org/rss/1.0/modules/content/"><channel><title>Svetainė</title><wp:wxr_version>1.2</wp:wxr_version><wp:base_site_url>https://example.test</wp:base_site_url><item><title>Įrašas</title><content:encoded><![CDATA[<p>Tekstas</p>]]></content:encoded><wp:post_id>10</wp:post_id><wp:post_date>2020-01-01 10:00:00</wp:post_date><wp:post_name>irasas</wp:post_name><wp:status>publish</wp:status><wp:post_type>post</wp:post_type></item></channel></rss>
XML;
    }
}
