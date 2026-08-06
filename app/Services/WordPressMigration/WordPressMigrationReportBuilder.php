<?php

namespace App\Services\WordPressMigration;

class WordPressMigrationReportBuilder
{
    public function __construct(private WordPressUploadsReportSchema $uploadsSchema) {}

    public function build(array $xml, array $sql, array $uploads, array $warnings = []): array
    {
        $uploads = $this->uploadsSchema->normalize($uploads);
        $errors = [];
        if ($uploads['high_risk_executable_files']) $errors[] = 'Aptikti galimai pavojingi vykdomieji failai. Jie nebus importuojami.';
        if (! $xml['summary']['post_count']) $warnings[] = 'XML eksporte nerasta straipsnių.';
        if (! $sql['table_prefix']) $warnings[] = 'Nepavyko patikimai nustatyti WordPress lentelių priešdėlio.';
        if ($uploads['suspicious_executable_files']) $warnings[] = 'Aptikti vykdomieji failai, kuriuos reikia peržiūrėti. Jie nebus importuojami.';
        if ($uploads['missing_referenced_files']) $warnings[] = 'Rasta XML nurodytų, bet medijos šaltinyje nesančių paveikslėlių.';
        $warnings = array_values(array_unique($warnings));
        $validation = [
            ['label' => 'XML sėkmingai nuskaitytas', 'status' => 'success'],
            ['label' => 'SQL failas nuskaitytas kaip tekstas', 'status' => 'success'],
            ['label' => 'Medijos katalogas saugiai nuskaitytas', 'status' => 'success'],
            ['label' => 'Failų kontrolinės sumos sugeneruotos', 'status' => 'success'],
            ['label' => 'Standartiniai WordPress apsauginiai failai bus praleisti', 'status' => $uploads['protection_files'] ? 'info' : 'success'],
            ['label' => 'Medijos migracijai nereikalingi failai bus praleisti', 'status' => $uploads['ignored_files'] ? 'info' : 'success'],
            ['label' => 'Aptikti vykdomieji failai, kuriuos reikia peržiūrėti', 'status' => $uploads['suspicious_executable_files'] ? 'warning' : 'success'],
            ['label' => 'Aptikti galimai pavojingi vykdomieji failai', 'status' => $uploads['high_risk_executable_files'] ? 'error' : 'success'],
            ['label' => 'Aptiktos medijos kopijos', 'status' => $uploads['duplicates'] ? 'info' : 'success'],
            ['label' => 'Trūkstami paveikslėliai', 'status' => $uploads['missing_referenced_files'] ? 'warning' : 'success'],
        ];
        $summary = [
            'posts' => $xml['summary']['post_count'], 'pages' => $xml['summary']['page_count'], 'comments' => $xml['summary']['comment_count'], 'published_posts' => $xml['summary']['published_count'], 'draft_posts' => $xml['summary']['draft_count'],
            'categories' => count($xml['categories']), 'tags' => count($xml['tags']), 'authors' => count($xml['authors']), 'images' => $uploads['image_files'], 'media_files' => $uploads['total_files'], 'importable_media' => count($uploads['importable_media']),
            'protection_files' => count($uploads['protection_files']), 'ignored_files' => count($uploads['ignored_files']), 'suspicious_executable_files' => count($uploads['suspicious_executable_files']), 'high_risk_executable_files' => count($uploads['high_risk_executable_files']), 'duplicate_files' => array_sum(array_map('count', $uploads['duplicates'])),
            'broken_files' => count($uploads['high_risk_executable_files']), 'broken_references' => count($uploads['missing_referenced_files']), 'featured_images' => count($xml['featured_image_references']), 'seo_records' => $sql['seo']['records_found'], 'missing_files' => count($uploads['missing_referenced_files']), 'duplicate_slugs' => count($xml['duplicate_slugs']), 'warnings' => count($warnings),
        ];
        return ['report_version' => WordPressMigrationReportNormalizer::CURRENT_VERSION, 'generated_at' => now()->toIso8601String(), 'summary' => $summary, 'validation' => $validation, 'wordpress' => $xml, 'sql' => $sql, 'uploads' => $uploads, 'warnings' => $warnings, 'errors' => $errors, 'readiness' => ['status' => $errors ? 'errors' : ($warnings ? 'warnings' : 'ready'), 'analysis_complete' => true, 'critical_validations_passed' => ! $errors, 'import_ready' => ! $errors, 'blocking_errors' => count($errors)]];
    }
}
