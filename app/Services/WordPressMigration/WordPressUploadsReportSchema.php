<?php

namespace App\Services\WordPressMigration;

class WordPressUploadsReportSchema
{
    public function create(): array
    {
        return [
            'total_files' => 0, 'image_files' => 0, 'total_size' => 0, 'file_types' => [], 'duplicates' => [],
            'original_images' => 0, 'generated_variants' => 0, 'importable_media' => [], 'protection_files' => [],
            'ignored_files' => [], 'suspicious_executable_files' => [], 'high_risk_executable_files' => [],
            'year_month_structure' => [], 'referenced_files' => [], 'missing_referenced_files' => [],
            'orphaned_uploads' => [], 'media_source_type' => null,
        ];
    }

    public function normalize(array $uploads): array
    {
        $normalized = array_replace($this->create(), $uploads);
        $legacySuspicious = $this->normalizeFindings(array_key_exists('suspicious_files', $uploads) ? $uploads['suspicious_files'] : [], 'suspicious_executable_files', 'Vykdomasis failas aptiktas ankstesnės analizės metu.');
        $legacyIgnored = $this->normalizeFindings(array_key_exists('unsupported_files', $uploads) ? $uploads['unsupported_files'] : [], 'ignored_files', 'Failas nėra reikalingas medijos migracijai.');
        $normalized['importable_media'] = $this->normalizeFindings($normalized['importable_media'], 'importable_media', 'Medijos failas paruoštas importuoti.');
        $normalized['protection_files'] = $this->normalizeFindings($normalized['protection_files'], 'protection_files', 'Patvirtintas WordPress apsauginis failas.');
        $normalized['suspicious_executable_files'] = array_values(array_merge($this->normalizeFindings($normalized['suspicious_executable_files'], 'suspicious_executable_files', 'Vykdomasis failas turi būti peržiūrėtas.'), $legacySuspicious));
        $normalized['high_risk_executable_files'] = $this->normalizeFindings($normalized['high_risk_executable_files'], 'high_risk_executable_files', 'Aptikti galimai pavojingo vykdomojo kodo požymiai.');
        $normalized['ignored_files'] = array_values(array_filter(
            array_merge($this->normalizeFindings($normalized['ignored_files'], 'ignored_files', 'Failas nėra reikalingas medijos migracijai.'), $legacyIgnored),
            fn (array $finding) => ! in_array($finding['extension'], config('wordpress-migration.rejected_extensions'), true)
        ));
        $claimed = [];
        foreach (['high_risk_executable_files', 'protection_files', 'suspicious_executable_files', 'importable_media', 'ignored_files'] as $group) {
            $normalized[$group] = array_values(array_filter($normalized[$group], function (array $finding) use (&$claimed): bool {
                $key = strtolower($finding['path']);
                if ($key === '' || isset($claimed[$key])) {
                    return false;
                }
                $claimed[$key] = true;

                return true;
            }));
        }
        unset($normalized['suspicious_files'], $normalized['unsupported_files']);

        return $normalized;
    }

    private function normalizeFindings(array $findings, string $classification, string $reason): array
    {
        return array_values(array_map(function ($finding) use ($classification, $reason): array {
            if (is_string($finding)) {
                $extension = strtolower(pathinfo($finding, PATHINFO_EXTENSION));

                return ['path' => str_replace('\\', '/', ltrim($finding, '/\\')), 'extension' => $extension ?: null, 'size' => 0, 'classification' => $classification, 'reason' => $reason];
            }

            return ['path' => str_replace('\\', '/', ltrim((string) ($finding['path'] ?? ''), '/\\')), 'extension' => isset($finding['extension']) ? strtolower((string) $finding['extension']) : null, 'size' => max(0, (int) ($finding['size'] ?? 0)), 'classification' => $classification, 'reason' => (string) ($finding['reason'] ?? $reason), 'sha256' => isset($finding['sha256']) ? (string) $finding['sha256'] : null];
        }, $findings));
    }
}
