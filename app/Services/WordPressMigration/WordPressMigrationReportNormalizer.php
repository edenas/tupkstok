<?php

namespace App\Services\WordPressMigration;

class WordPressMigrationReportNormalizer
{
    public const CURRENT_VERSION = 3;

    public function __construct(private WordPressUploadsReportSchema $uploadsSchema, private WordPressMigrationReportBuilder $reportBuilder) {}

    public function normalize(array $report): array
    {
        $hadImportableMedia = array_key_exists('importable_media', $report['uploads'] ?? []);
        $uploads = $this->uploadsSchema->normalize($report['uploads'] ?? []);
        if (($report['report_version'] ?? 1) < self::CURRENT_VERSION) {
            $warnings = array_values(array_filter($report['warnings'] ?? [], fn (string $warning) => ! str_starts_with($warning, 'Rasta pasikartojančių failų') && ! str_contains($warning, 'nepalaikomų failų') && ! str_contains($warning, 'nepalaikomi failų tipai')));
            $normalized = $this->reportBuilder->build($report['wordpress'], $report['sql'], $uploads, $warnings);
            if (! $hadImportableMedia) $normalized['summary']['importable_media'] = (int) $uploads['image_files'];
            $normalized['generated_at'] = $report['generated_at'] ?? $normalized['generated_at'];
            return $normalized;
        }
        $report['uploads'] = $uploads;
        foreach (['importable_media', 'protection_files', 'ignored_files', 'suspicious_executable_files', 'high_risk_executable_files'] as $key) $report['summary'][$key] = count($uploads[$key]);
        $report['summary']['duplicate_files'] = array_sum(array_map('count', $uploads['duplicates']));
        $report['report_version'] = self::CURRENT_VERSION;
        return $report;
    }
}
