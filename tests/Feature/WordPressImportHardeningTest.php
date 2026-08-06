<?php

namespace Tests\Feature;

use App\Jobs\GenerateWordPressImportDryRun;
use App\Jobs\ImportWordPressPostsChunk;
use App\Jobs\RollbackWordPressImport;
use App\Jobs\StartWordPressImport;
use App\Models\BlogPost;
use App\Models\User;
use App\Models\WordPressImportItem;
use App\Models\WordPressImportRedirect;
use App\Models\WordPressImportRun;
use App\Services\WordPressMigration\WordPressImportExecutor;
use App\Services\WordPressMigration\WordPressImportLock;
use App\Services\WordPressMigration\WordPressImportPlanService;
use App\Services\WordPressMigration\WordPressMigrationManager;
use App\Services\WordPressMigration\WordPressMigrationReportNormalizer;
use App\Services\WordPressMigration\WordPressUploadsReportSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class WordPressImportHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_chunk_job_payload_contains_only_coordinates(): void
    {
        $job = new ImportWordPressPostsChunk((string) Str::uuid(), 20, 10);
        $payload = serialize($job);

        $this->assertStringNotContainsString('content', $payload);
        $this->assertStringNotContainsString('<p>', $payload);
        $this->assertSame(20, $job->offset);
        $this->assertSame(10, $job->limit);
    }

    public function test_stale_overwrite_is_rejected_without_changing_existing_blog_post(): void
    {
        $post = BlogPost::create(['title' => 'Esamas', 'category' => 'Publikacijos', 'description' => '<p>Senas</p>', 'position' => 1]);
        $fingerprint = $this->targetFingerprint($post);
        $post->update(['description' => '<p>Naujesnis administratoriaus turinys</p>']);
        $run = $this->makeRun(['slug_conflicts' => 'overwrite', 'redirect_handling' => 'none']);

        try {
            app(WordPressImportExecutor::class)->import($run, $this->source(['action' => 'overwrite', 'existing_id' => $post->id, 'target_fingerprint' => $fingerprint]));
            $this->fail('The stale overwrite should have been rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('pasikeitė', $exception->getMessage());
        }

        $this->assertSame('<p>Naujesnis administratoriaus turinys</p>', $post->fresh()->description);
        $this->assertSame('failed', WordPressImportItem::query()->where('run_id', $run->id)->firstOrFail()->status);
    }

    public function test_completed_item_retry_does_not_change_counters(): void
    {
        $run = $this->makeRun();
        WordPressImportItem::create(['run_id' => $run->id, 'migration_id' => $run->migration_id, 'source_type' => 'post', 'source_key' => '10', 'status' => 'completed', 'action' => 'create', 'metadata' => ['outcome' => 'created', 'owner_run_id' => $run->id]]);

        app(WordPressImportExecutor::class)->import($run, $this->source());
        app(WordPressImportExecutor::class)->import($run, $this->source());

        $run->refresh();
        $this->assertSame(1, $run->processed_items);
        $this->assertSame(1, $run->created_count);
        $this->assertSame(0, $run->skipped_count);
        $this->assertSame(0, $run->failed_count);
    }

    public function test_rollback_does_not_delete_reused_media(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media-library/shared.jpg', 'existing');
        $run = $this->makeRun();
        $run->update(['status' => 'rolling_back']);
        WordPressImportItem::create(['run_id' => $run->id, 'migration_id' => $run->migration_id, 'source_type' => 'media', 'source_key' => 'uploads/shared.jpg', 'status' => 'completed', 'target_path' => 'media-library/shared.jpg', 'metadata' => ['outcome' => 'reused', 'owner_run_id' => null]]);

        (new RollbackWordPressImport($run->id))->handle(app(WordPressImportLock::class));

        Storage::disk('public')->assertExists('media-library/shared.jpg');
    }

    public function test_only_one_import_lifecycle_lock_can_be_held(): void
    {
        Cache::flush();
        $first = $this->makeRun();
        $second = $this->makeRun();
        $lock = app(WordPressImportLock::class);
        $this->assertTrue($lock->acquire($first));
        $this->assertFalse($lock->acquire($second));
        $lock->release($first->fresh());
        $this->assertTrue($lock->acquire($second));
        $lock->release($second->fresh());
    }

    public function test_slug_is_revalidated_and_generated_inside_import_transaction(): void
    {
        BlogPost::create(['title' => 'Konfliktas', 'category' => 'Publikacijos', 'description' => '<p>Esamas</p>', 'position' => 1]);
        $run = $this->makeRun(['slug_conflicts' => 'generate', 'redirect_handling' => 'none']);
        app(WordPressImportExecutor::class)->import($run, $this->source(['final_slug' => 'konfliktas']));
        $this->assertDatabaseHas('portfolio_posts', ['title' => 'Importuojamas', 'slug' => 'konfliktas-1']);
    }

    public function test_redirects_only_apply_after_normal_routes_fail(): void
    {
        $run = $this->makeRun();
        WordPressImportRedirect::create(['run_id' => $run->id, 'source_path' => '/kontaktai', 'target_path' => '/blogas/importuotas']);
        WordPressImportRedirect::create(['run_id' => $run->id, 'source_path' => '/senas-wordpress', 'target_path' => '/blogas/importuotas']);
        $this->get('/kontaktai')->assertOk();
        $this->get('/senas-wordpress')->assertRedirect('/blogas/importuotas');
    }

    public function test_running_session_cannot_be_deleted(): void
    {
        $root = storage_path('framework/testing/wordpress-hardening-'.Str::uuid());
        config(['wordpress-migration.storage_path' => $root]);
        $run = $this->makeRun();
        File::ensureDirectoryExists($root.'/'.$run->migration_id);
        File::put($root.'/'.$run->migration_id.'/manifest.json', json_encode(['id' => $run->migration_id], JSON_THROW_ON_ERROR));
        try {
            $administrator = User::findOrFail($run->administrator_id);
            $this->actingAs($administrator)->from(route('admin.wordpress-migration.index'))->delete(route('admin.wordpress-migration.destroy', $run->migration_id))->assertRedirect(route('admin.wordpress-migration.index'))->assertSessionHas('error');
            $this->assertDirectoryExists($root.'/'.$run->migration_id);
        } finally {
            File::deleteDirectory($root);
        }
    }

    public function test_running_import_is_reported_as_stalled(): void
    {
        $run = $this->makeRun();
        $run->update(['last_activity_at' => now()->subMinutes(10)]);
        $this->actingAs(User::findOrFail($run->administrator_id))->getJson(route('admin.wordpress-migration.import.status', $run))->assertOk()->assertJsonPath('queue_stalled', true);
    }

    public function test_rollback_preserves_created_post_edited_after_import(): void
    {
        $run = $this->makeRun();
        $run->update(['status' => 'rolling_back']);
        $post = BlogPost::create(['title' => 'Importuotas', 'category' => 'Publikacijos', 'description' => '<p>Importuota</p>', 'position' => 1]);
        WordPressImportItem::create(['run_id' => $run->id, 'migration_id' => $run->migration_id, 'source_type' => 'post', 'source_key' => '44', 'status' => 'completed', 'target_id' => $post->id, 'metadata' => ['outcome' => 'created', 'owner_run_id' => $run->id, 'imported_fingerprint' => $this->targetFingerprint($post)]]);
        $post->update(['description' => '<p>Administratoriaus pakeitimas</p>']);
        (new RollbackWordPressImport($run->id))->handle(app(WordPressImportLock::class));
        $this->assertDatabaseHas('portfolio_posts', ['id' => $post->id, 'description' => '<p>Administratoriaus pakeitimas</p>']);
    }

    public function test_confirmation_dispatches_import_without_running_it_in_http_request(): void
    {
        Queue::fake();
        [$administrator, $migrationId] = $this->privatePlanSession();

        $this->actingAs($administrator)->post(route('admin.wordpress-migration.confirm', $migrationId), ['confirmed' => '1', 'confirmation_phrase' => 'IMPORTUOTI'])->assertRedirect();

        $this->assertDatabaseCount('portfolio_posts', 0);
        Queue::assertPushed(StartWordPressImport::class);
        $this->assertDatabaseHas('wordpress_import_runs', ['migration_id' => $migrationId, 'status' => 'queued']);
    }

    public function test_same_confirmed_plan_cannot_be_dispatched_twice(): void
    {
        Queue::fake();
        [$administrator, $migrationId] = $this->privatePlanSession();
        $payload = ['confirmed' => '1', 'confirmation_phrase' => 'IMPORTUOTI'];
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.confirm', $migrationId), $payload)->assertRedirect();
        $this->actingAs($administrator)->from(route('admin.wordpress-migration.dry-run.show', $migrationId))->post(route('admin.wordpress-migration.confirm', $migrationId), $payload)->assertRedirect(route('admin.wordpress-migration.dry-run.show', $migrationId))->assertSessionHas('error');
        $this->assertDatabaseCount('wordpress_import_runs', 1);
        Queue::assertPushed(StartWordPressImport::class, 1);
    }

    public function test_dry_run_is_dispatched_and_not_built_in_http_request(): void
    {
        Queue::fake();
        $migrationId = (string) Str::uuid();
        $plans = Mockery::mock(WordPressImportPlanService::class);
        $plans->shouldReceive('loadPrerequisites')->once()->with($migrationId)->andReturn([[], []]);
        $this->app->instance(WordPressImportPlanService::class, $plans);
        $manager = Mockery::mock(WordPressMigrationManager::class);
        $manager->shouldReceive('updateManifest')->once();
        $this->app->instance(WordPressMigrationManager::class, $manager);
        $administrator = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($administrator)->post(route('admin.wordpress-migration.dry-run', $migrationId))->assertRedirect(route('admin.wordpress-migration.dry-run.show', $migrationId));
        Queue::assertPushed(GenerateWordPressImportDryRun::class);
        $plans->shouldNotHaveReceived('build');
    }

    public function test_changed_source_is_rejected_after_dry_run(): void
    {
        [, $migrationId, $root] = $this->privatePlanSession();
        File::append($root.'/'.$migrationId.'/source/wordpress.xml', 'changed');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('pasikeitė');
        app(WordPressImportPlanService::class)->loadAndValidate($migrationId);
    }

    public function test_tampered_private_plan_is_rejected(): void
    {
        [, $migrationId, $root] = $this->privatePlanSession();
        $path = $root.'/'.$migrationId.'/reports/dry-run.json';
        $plan = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $plan['warnings'][] = 'tampered';
        File::put($path, json_encode($plan, JSON_THROW_ON_ERROR));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('sugadintas');
        app(WordPressImportPlanService::class)->loadAndValidate($migrationId);
    }

    public function test_chunk_loads_private_plan_and_imports_basic_post(): void
    {
        [, $migrationId] = $this->privatePlanSession();
        $run = WordPressImportRun::query()->where('migration_id', $migrationId)->first();
        if (! $run) {
            $run = $this->makeRun();
            $run->update(['migration_id' => $migrationId]);
        }
        $run->update(['plan_path' => 'reports/dry-run.json', 'status' => 'running']);

        (new ImportWordPressPostsChunk($run->id, 0, 1))->handle(app(WordPressImportExecutor::class), app(WordPressMigrationManager::class), app(WordPressImportLock::class));

        $this->assertDatabaseHas('portfolio_posts', ['title' => 'Importuojamas', 'slug' => 'importuojamas']);
    }

    public function test_checksum_verified_raster_media_is_streamed_to_media_library(): void
    {
        Storage::fake('public');
        [$run, $mediaRoot] = $this->mediaSession();
        app(WordPressImportExecutor::class)->import($run, $this->source(['content' => '<p><img src="https://old.test/wp-content/uploads/photo.png"></p>', 'media_urls' => ['https://old.test/wp-content/uploads/photo.png']]));
        $post = BlogPost::query()->where('title', 'Importuojamas')->firstOrFail();
        $this->assertStringContainsString('/storage/media-library/photo-', $post->description);
        Storage::disk('public')->assertExists(WordPressImportItem::query()->where('source_type', 'media')->firstOrFail()->target_path);
        File::deleteDirectory(dirname($mediaRoot));
    }

    public function test_media_changed_after_analysis_blocks_blog_write(): void
    {
        Storage::fake('public');
        [$run, $mediaRoot] = $this->mediaSession();
        File::put($mediaRoot.'/photo.png', 'changed after analysis');
        try {
            app(WordPressImportExecutor::class)->import($run, $this->source(['content' => '<img src="https://old.test/uploads/photo.png">', 'media_urls' => ['https://old.test/uploads/photo.png']]));
            $this->fail('Changed media must stop the item.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('pasikeitė', $exception->getMessage());
        }
        $this->assertDatabaseMissing('portfolio_posts', ['title' => 'Importuojamas']);
        File::deleteDirectory(dirname($mediaRoot));
    }

    private function makeRun(array $seo = ['redirect_handling' => 'none']): WordPressImportRun
    {
        $id = (string) Str::uuid();

        return WordPressImportRun::create(['id' => $id, 'migration_id' => (string) Str::uuid(), 'administrator_id' => User::factory()->create(['role' => 'administrator'])->id, 'source_type' => 'manual', 'status' => 'running', 'settings_snapshot' => [], 'seo_settings_snapshot' => $seo, 'source_fingerprints' => [], 'dry_run_fingerprint' => str_repeat('a', 64), 'import_fingerprint' => hash('sha256', $id), 'plan_path' => 'reports/dry-run.json']);
    }

    private function source(array $overrides = []): array
    {
        return array_replace(['source_id' => '10', 'title' => 'Importuojamas', 'final_slug' => 'importuojamas', 'action' => 'create', 'existing_id' => null, 'target_fingerprint' => null, 'author' => 'Autorius', 'category' => 'Publikacijos', 'content' => '<p>Turinys</p>', 'featured_image_url' => null, 'media_urls' => [], 'seo' => [], 'source_url' => ''], $overrides);
    }

    private function targetFingerprint(BlogPost $post): string
    {
        return hash('sha256', json_encode(['id' => $post->id, 'title' => $post->title, 'slug' => $post->slug, 'category' => $post->category, 'description' => $post->description, 'project_details' => $post->project_details, 'thumbnail' => $post->thumbnail, 'youtube_url' => $post->youtube_url, 'position' => $post->position, 'updated_at' => $post->updated_at?->toJSON()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function privatePlanSession(): array
    {
        $root = storage_path('framework/testing/wordpress-plan-'.Str::uuid());
        config(['wordpress-migration.storage_path' => $root]);
        $migrationId = (string) Str::uuid();
        $session = $root.'/'.$migrationId;
        File::ensureDirectoryExists($session.'/source');
        File::put($session.'/source/wordpress.xml', '<rss/>');
        File::put($session.'/source/wordpress.sql', '-- text only');
        $files = [
            'wordpress_xml' => ['stored_name' => 'wordpress.xml', 'sha256' => hash_file('sha256', $session.'/source/wordpress.xml'), 'size' => filesize($session.'/source/wordpress.xml'), 'modified_timestamp' => filemtime($session.'/source/wordpress.xml')],
            'wordpress_sql' => ['stored_name' => 'wordpress.sql', 'sha256' => hash_file('sha256', $session.'/source/wordpress.sql'), 'size' => filesize($session.'/source/wordpress.sql'), 'modified_timestamp' => filemtime($session.'/source/wordpress.sql')],
        ];
        $fingerprints = ['files' => array_map(fn ($file) => ['sha256' => $file['sha256'], 'size' => $file['size']], $files), 'media' => ['source_type' => 'zip', 'size' => 0, 'file_count' => null, 'latest_modified_timestamp' => null]];
        $settings = ['posts' => true, 'images' => false, 'featured_images' => false, 'authors' => true, 'categories' => true, 'tags' => false, 'comments' => false, 'preserve_upload_structure' => false, 'image_variants' => 'originals', 'duplicate_media' => 'skip', 'seo' => false];
        $seo = ['slug_conflicts' => 'skip', 'redirect_handling' => 'none', 'meta_title' => false, 'meta_description' => false, 'canonical_url' => false];
        $plan = ['plan_version' => 1, 'migration_id' => $migrationId, 'source_fingerprints' => $fingerprints, 'settings' => $settings, 'seo_settings' => $seo, 'items' => [$this->source()], 'skipped' => [], 'warnings' => [], 'blocking_errors' => [], 'forecast' => []];
        $plan['fingerprint'] = hash('sha256', json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $plan['generated_at'] = now()->toIso8601String();
        File::ensureDirectoryExists($session.'/reports');
        File::put($session.'/reports/dry-run.json', json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        File::put($session.'/manifest.json', json_encode(['id' => $migrationId, 'files' => $files, 'media_source' => ['source_type' => 'zip', 'size' => 0, 'file_count' => null, 'latest_modified_timestamp' => null], 'import_settings' => $settings, 'seo_settings' => $seo, 'dry_run_path' => 'reports/dry-run.json', 'dry_run_fingerprint' => $plan['fingerprint']], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return [User::factory()->create(['role' => 'administrator']), $migrationId, $root];
    }

    private function mediaSession(): array
    {
        $root = storage_path('framework/testing/wordpress-media-'.Str::uuid());
        $migrationId = (string) Str::uuid();
        $session = $root.'/'.$migrationId;
        $mediaRoot = $root.'/workspace/uploads';
        File::ensureDirectoryExists($session.'/reports');
        File::ensureDirectoryExists($mediaRoot);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);
        File::put($mediaRoot.'/photo.png', $png);
        $checksum = hash_file('sha256', $mediaRoot.'/photo.png');
        $uploads = app(WordPressUploadsReportSchema::class)->create();
        $uploads['importable_media'] = [['path' => 'photo.png', 'extension' => 'png', 'size' => strlen($png), 'classification' => 'importable_media', 'reason' => 'test', 'sha256' => $checksum]];
        $report = ['report_version' => WordPressMigrationReportNormalizer::CURRENT_VERSION, 'generated_at' => now()->toIso8601String(), 'summary' => [], 'validation' => [], 'wordpress' => ['site_url' => 'https://old.test'], 'sql' => [], 'uploads' => $uploads, 'warnings' => [], 'errors' => [], 'readiness' => []];
        File::put($session.'/reports/analysis.json', json_encode($report, JSON_THROW_ON_ERROR));
        File::put($session.'/manifest.json', json_encode(['id' => $migrationId, 'analysis_report_path' => 'reports/analysis.json', 'source_mode' => 'workspace', 'files' => [], 'media_source' => ['source_type' => 'directory', 'working_path' => $mediaRoot]], JSON_THROW_ON_ERROR));
        config(['wordpress-migration.storage_path' => $root]);
        $run = $this->makeRun(['slug_conflicts' => 'skip', 'redirect_handling' => 'none']);
        $run->update(['migration_id' => $migrationId, 'settings_snapshot' => ['image_variants' => 'originals', 'duplicate_media' => 'skip']]);

        return [$run, $mediaRoot];
    }
}
