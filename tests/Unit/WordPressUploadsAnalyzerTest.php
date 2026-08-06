<?php

namespace Tests\Unit;

use App\Services\WordPressMigration\WordPressMigrationReportBuilder;
use App\Services\WordPressMigration\WordPressMigrationWizardPresenter;
use App\Services\WordPressMigration\WordPressUploadsAnalyzer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class WordPressUploadsAnalyzerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('framework/testing/wp-uploads-classification-'.Str::uuid());
        File::ensureDirectoryExists($this->directory.'/2024/01');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_verified_index_files_are_protection_files(): void
    {
        file_put_contents($this->directory.'/index.php', "<?php\n// Silence is golden.\n");
        file_put_contents($this->directory.'/2024/01/index.php', "<?php\nexit;\n");
        file_put_contents($this->directory.'/empty-index.php', '');
        $uploads = $this->analyze();

        foreach (['importable_media', 'protection_files', 'ignored_files', 'suspicious_executable_files', 'high_risk_executable_files'] as $key) {
            $this->assertArrayHasKey($key, $uploads);
        }
        $paths = array_column($uploads['protection_files'], 'path');
        sort($paths);
        $this->assertSame(['2024/01/index.php', 'index.php'], $paths);
        $this->assertContains('empty-index.php', array_column($uploads['suspicious_executable_files'], 'path'));
        $this->assertEmpty($uploads['ignored_files']);
    }

    public function test_filename_alone_does_not_make_index_php_harmless(): void
    {
        file_put_contents($this->directory.'/index.php', '<?php echo "unexpected";');
        $uploads = $this->analyze();

        $this->assertEmpty($uploads['protection_files']);
        $this->assertSame('index.php', $uploads['suspicious_executable_files'][0]['path']);
    }

    public function test_empty_index_php_is_a_protection_file(): void
    {
        file_put_contents($this->directory.'/index.php', '');

        $uploads = $this->analyze();

        $this->assertSame('index.php', $uploads['protection_files'][0]['path']);
        $this->assertEmpty($uploads['suspicious_executable_files']);
    }

    public function test_high_risk_indicators_take_priority_and_expose_no_content_or_absolute_path(): void
    {
        file_put_contents($this->directory.'/index.php', '<?php eval(base64_decode($payload));');
        $uploads = $this->analyze();
        $finding = $uploads['high_risk_executable_files'][0];

        $this->assertSame('index.php', $finding['path']);
        $this->assertSame('high_risk_executable_files', $finding['classification']);
        $this->assertStringNotContainsString($this->directory, json_encode($finding));
        $this->assertStringNotContainsString('base64_decode', json_encode($finding));
        $this->assertEmpty($uploads['protection_files']);
        $this->assertEmpty($uploads['suspicious_executable_files']);
        $this->assertEmpty($uploads['ignored_files']);
    }

    public function test_executables_and_ignored_files_are_separate(): void
    {
        file_put_contents($this->directory.'/tool.php', '<?php echo "review me";');
        file_put_contents($this->directory.'/notes.pdf', 'document');
        $uploads = $this->analyze();

        $this->assertSame(['tool.php'], array_column($uploads['suspicious_executable_files'], 'path'));
        $this->assertSame(['notes.pdf'], array_column($uploads['ignored_files'], 'path'));
    }

    public function test_images_are_classified_as_importable_media_only(): void
    {
        file_put_contents($this->directory.'/2024/01/photo.jpg', 'image');

        $uploads = $this->analyze();

        $this->assertSame(['2024/01/photo.jpg'], array_column($uploads['importable_media'], 'path'));
        $this->assertEmpty($uploads['ignored_files']);
        $this->assertSame(1, $uploads['image_files']);
    }

    public function test_readiness_is_proportionate_to_security_classification(): void
    {
        $base = ['total_files' => 1, 'image_files' => 0, 'duplicates' => [], 'importable_media' => [], 'ignored_files' => [], 'missing_referenced_files' => [], 'protection_files' => [], 'suspicious_executable_files' => [], 'high_risk_executable_files' => []];
        $finding = ['path' => 'index.php', 'extension' => 'php', 'size' => 20, 'classification' => 'protection_files', 'reason' => 'test'];

        $protection = app(WordPressMigrationReportBuilder::class)->build($this->xmlResult(), $this->sqlResult(), array_replace($base, ['protection_files' => [$finding]]));
        $this->assertSame('ready', $protection['readiness']['status']);
        $this->assertTrue($protection['readiness']['import_ready']);

        $suspicious = app(WordPressMigrationReportBuilder::class)->build($this->xmlResult(), $this->sqlResult(), array_replace($base, ['suspicious_executable_files' => [$finding]]));
        $this->assertSame('warnings', $suspicious['readiness']['status']);
        $this->assertTrue($suspicious['readiness']['import_ready']);
        $this->assertEmpty($suspicious['errors']);

        $highRisk = app(WordPressMigrationReportBuilder::class)->build($this->xmlResult(), $this->sqlResult(), array_replace($base, ['high_risk_executable_files' => [$finding]]));
        $this->assertSame('errors', $highRisk['readiness']['status']);
        $this->assertFalse($highRisk['readiness']['import_ready']);
        $this->assertSame(1, $highRisk['readiness']['blocking_errors']);
    }

    public function test_ignored_files_and_duplicates_are_informational_only(): void
    {
        $finding = ['path' => 'notes.txt', 'extension' => 'txt', 'size' => 10, 'classification' => 'ignored_files', 'reason' => 'test'];
        $uploads = ['total_files' => 3, 'image_files' => 0, 'duplicates' => [['copy-a.jpg', 'copy-b.jpg']], 'importable_media' => [], 'ignored_files' => [$finding], 'missing_referenced_files' => [], 'protection_files' => [], 'suspicious_executable_files' => [], 'high_risk_executable_files' => []];

        $report = app(WordPressMigrationReportBuilder::class)->build($this->xmlResult(), $this->sqlResult(), $uploads);

        $this->assertSame('ready', $report['readiness']['status']);
        $this->assertSame(0, $report['summary']['warnings']);
        $this->assertSame(2, $report['summary']['duplicate_files']);
        $this->assertSame('info', collect($report['validation'])->firstWhere('label', 'Aptiktos medijos kopijos')['status']);
    }

    public function test_wizard_presenter_builds_report_backed_recommendations(): void
    {
        $report = ['warnings' => ['review'], 'summary' => ['posts' => 67, 'authors' => 2, 'categories' => 3, 'images' => 100, 'featured_images' => 10, 'seo_records' => 67, 'ignored_files' => 4, 'protection_files' => 2, 'suspicious_executable_files' => 11, 'high_risk_executable_files' => 0, 'duplicate_files' => 20, 'tags' => 5, 'comments' => 8]];

        $data = app(WordPressMigrationWizardPresenter::class)->settings($report);

        $this->assertSame('warning', $data['status']['tone']);
        $this->assertCount(2, $data['recommendations']);
        $this->assertSame(67, $data['forecast']['Straipsniai']);
        $this->assertSame(20, $data['summaryGroups'][3]['items']['Vienodos medijos kopijos']);
    }

    public function test_svg_is_never_classified_as_importable_media(): void
    {
        File::put($this->directory.'/active.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $uploads = $this->analyze();
        $this->assertNotContains('active.svg', array_column($uploads['importable_media'], 'path'));
        $this->assertContains('active.svg', array_column($uploads['ignored_files'], 'path'));
    }

    private function analyze(): array
    {
        return app(WordPressUploadsAnalyzer::class)->analyze($this->directory, []);
    }

    private function xmlResult(): array
    {
        return ['summary' => ['post_count' => 1, 'page_count' => 0, 'comment_count' => 0, 'published_count' => 1, 'draft_count' => 0], 'categories' => [], 'tags' => [], 'authors' => [], 'featured_image_references' => [], 'duplicate_slugs' => []];
    }

    private function sqlResult(): array
    {
        return ['table_prefix' => 'wp_', 'seo' => ['records_found' => 0]];
    }
}
