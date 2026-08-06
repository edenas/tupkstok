<?php

namespace App\Services\WordPressMigration;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class WordPressUploadsAnalyzer
{
    public function __construct(private WordPressUploadsReportSchema $reportSchema) {}

    public function analyze(string $directory, array $referencedUrls): array
    {
        $result = $this->reportSchema->create();
        $files = [];
        $checksums = [];
        if (is_dir($directory)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                if ($file->isLink()) {
                    throw new RuntimeException('Medijos kataloge rasta nesaugi simbolinė nuoroda.');
                }
                if (! $file->isFile()) {
                    continue;
                }
                if (! $file->isReadable()) {
                    throw new RuntimeException('Medijos kataloge rastas neperskaitomas failas.');
                }
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen(rtrim($directory, '/\\')) + 1));
                $extension = strtolower($file->getExtension());
                $size = $file->getSize();
                $result['total_files']++;
                $result['total_size'] += $size;
                $result['file_types'][$extension ?: '(be plėtinio)'] = ($result['file_types'][$extension ?: '(be plėtinio)'] ?? 0) + 1;
                if ($result['total_files'] > config('wordpress-migration.maximum_directory_file_count') || $result['total_size'] > config('wordpress-migration.maximum_directory_total_size')) {
                    throw new RuntimeException('Medijos katalogas viršija analizės limitus.');
                }
                $files[$relative] = true;
                $hash = hash_file('sha256', $file->getPathname());
                $checksums[$hash][] = $relative;
                if (in_array($extension, config('wordpress-migration.image_extensions'), true)) {
                    $result['image_files']++;
                    $result['importable_media'][] = $this->finding($relative, $extension, $size, 'importable_media', 'Medijos failas paruoštas importuoti.', $hash);
                    if (preg_match('/-\d+x\d+\.[^.]+$/i', $relative)) {
                        $result['generated_variants']++;
                    } else {
                        $result['original_images']++;
                    }
                } elseif (in_array($extension, config('wordpress-migration.rejected_extensions'), true)) {
                    [$classification, $reason] = $this->classifyExecutable($file->getPathname(), $file->getFilename(), $extension, $size);
                    $result[$classification][] = $this->finding($relative, $extension, $size, $classification, $reason);
                } else {
                    $result['ignored_files'][] = $this->finding($relative, $extension, $size, 'ignored_files', 'Failas nėra reikalingas medijos migracijai.');
                }
                if (preg_match('~(?:^|/)(\d{4})/(0[1-9]|1[0-2])(?:/|$)~', $relative, $m)) {
                    $result['year_month_structure'][$m[1].'/'.$m[2]] = true;
                }
            }
        }
        $result['duplicates'] = array_values(array_filter($checksums, fn ($items) => count($items) > 1));
        $referenced = [];
        foreach ($referencedUrls as $url) {
            $path = ltrim((string) parse_url(html_entity_decode($url), PHP_URL_PATH), '/');
            if (($at = strpos($path, 'uploads/')) !== false) {
                $referenced[] = substr($path, $at);
            }
        }
        foreach (array_unique($referenced) as $path) {
            if (isset($files[$path]) || isset($files[substr($path, 8)])) {
                $result['referenced_files'][] = $path;
            } else {
                $result['missing_referenced_files'][] = $path;
            }
        }
        $referencedBasenames = array_flip(array_map('basename', $referenced));
        $result['orphaned_uploads'] = array_values(array_filter(array_keys($files), fn ($path) => ! isset($referencedBasenames[basename($path)])));
        $result['year_month_structure'] = array_keys($result['year_month_structure']);
        ksort($result['file_types']);

        return $result;
    }

    private function classifyExecutable(string $path, string $filename, string $extension, int $size): array
    {
        $limit = max(1, (int) config('wordpress-migration.executable_inspection_max_bytes', 65536));
        $handle = @fopen($path, 'rb');
        if (! $handle) {
            return ['suspicious_executable_files', 'Vykdomojo failo turinio nepavyko saugiai patikrinti.'];
        }
        try {
            $content = fread($handle, $limit + 1);
        } finally {
            fclose($handle);
        }
        if ($content === false) {
            return ['suspicious_executable_files', 'Vykdomojo failo turinio nepavyko saugiai patikrinti.'];
        }
        $inspected = substr($content, 0, $limit);
        if ($this->hasHighRiskIndicators($filename, $extension, $inspected, strlen($content) > $limit)) {
            return ['high_risk_executable_files', 'Aptikti galimai pavojingo arba užmaskuoto vykdomojo kodo požymiai.'];
        }
        if ($this->isProtectionFile($filename, $size, $inspected, strlen($content) > $limit)) {
            return ['protection_files', 'Patvirtintas mažas, nekenksmingas WordPress prieigos ribojimo failas.'];
        }

        return ['suspicious_executable_files', 'Vykdomasis failas neatitinka patvirtinto apsauginio failo turinio.'];
    }

    private function isProtectionFile(string $filename, int $size, string $content, bool $truncated): bool
    {
        if (strcasecmp($filename, 'index.php') !== 0 || $truncated || $size > (int) config('wordpress-migration.protection_file_max_bytes', 1024)) {
            return false;
        }
        $normalized = trim(str_replace(["\r\n", "\r"], "\n", preg_replace('/^\xEF\xBB\xBF/', '', $content)));

        return $normalized === '' || preg_match('/^<\?php\s*(?:(?:\/\/\s*Silence is golden\.?)\s*|exit\s*(?:\(\s*\))?\s*;\s*|http_response_code\s*\(\s*403\s*\)\s*;\s*exit\s*(?:\(\s*\))?\s*;\s*)$/i', $normalized) === 1;
    }

    private function hasHighRiskIndicators(string $filename, string $extension, string $content, bool $truncated): bool
    {
        if (preg_match('/(?:^|[._-])(c99|r57|wso|webshell|shell|b374k|alfa)(?:[._-]|$)/i', $filename)) {
            return true;
        }
        if ($extension === 'exe' || $truncated && preg_match('/[A-Za-z0-9+\/=]{300,}/', $content)) {
            return true;
        }
        $patterns = [
            '/\b(?:eval|assert|base64_decode|gzinflate|gzuncompress|shell_exec|system|passthru|proc_open|popen|exec)\s*\(/i',
            '/preg_replace\s*\([^\n]{0,200}[\'\"][^\'\"]*e[^\'\"]*[\'\"]/i',
            '/`[^`\r\n]+`/',
            '/\b(?:curl_exec|curl_multi_exec)\s*\(/i',
            '/\b(?:file_get_contents|fopen)\s*\(\s*[\'\"]https?:\/\//i',
            '/\b(?:file_put_contents|move_uploaded_file|fwrite)\s*\(/i',
            '/[A-Za-z0-9+\/=]{500,}/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    private function finding(string $relative, string $extension, int $size, string $classification, string $reason, ?string $checksum = null): array
    {
        return ['path' => $relative, 'extension' => $extension ?: null, 'size' => $size, 'classification' => $classification, 'reason' => $reason, 'sha256' => $checksum];
    }
}
