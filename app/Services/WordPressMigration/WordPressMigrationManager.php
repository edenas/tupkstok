<?php

namespace App\Services\WordPressMigration;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class WordPressMigrationManager
{
    public function __construct(private WordPressXmlAnalyzer $xmlAnalyzer, private WordPressSqlAnalyzer $sqlAnalyzer, private WordPressUploadsAnalyzer $uploadsAnalyzer, private WordPressMigrationReportBuilder $reportBuilder, private WordPressMigrationReportNormalizer $reportNormalizer, private WordPressWorkspaceScanner $workspaceScanner) {}

    public function create(array $files): array
    {
        $id = (string) Str::uuid();
        $root = $this->path($id);
        File::ensureDirectoryExists($root.'/source', 0750, true);
        $mapping = ['wordpress_xml' => 'wordpress.xml', 'wordpress_sql' => str_ends_with(strtolower($files['wordpress_sql']->getClientOriginalName()), '.gz') ? 'wordpress.sql.gz' : 'wordpress.sql', 'uploads_zip' => 'uploads.zip'];
        $manifest = ['id' => $id, 'created_at' => now()->toIso8601String(), 'source_mode' => 'manual', 'analysis_status' => 'pending', 'files' => [], 'warnings' => [], 'errors' => [], 'media_source' => ['source_type' => 'zip', 'original_name' => $files['uploads_zip']->getClientOriginalName(), 'source_path' => 'source/uploads.zip', 'working_path' => 'extracted', 'size' => $files['uploads_zip']->getSize(), 'checksum_strategy' => 'sha256', 'requires_extraction' => true]];
        foreach ($mapping as $key => $stored) { /** @var UploadedFile $file */ $file = $files[$key];
            $file->move($root.'/source', $stored);
            $path = $root.'/source/'.$stored;
            $manifest['files'][$key] = ['original_name' => basename($file->getClientOriginalName()), 'stored_name' => $stored, 'size' => filesize($path), 'sha256' => null, 'uploaded_at' => now()->toIso8601String()];
        }
        $this->writeJson($root.'/manifest.json', $manifest);

        return $manifest;
    }

    public function createFromWorkspace(array $workspace): array
    {
        foreach (['xml', 'sql', 'uploads'] as $key) {
            if (! isset($workspace[$key]['path'])) {
                throw new RuntimeException('Workspace nėra visų privalomų šaltinio failų.');
            }
        }
        if (! is_file($workspace['xml']['path']) || ! is_file($workspace['sql']['path']) || ($workspace['uploads']['source_type'] === 'zip' ? ! is_file($workspace['uploads']['path']) : ! is_dir($workspace['uploads']['path']))) {
            throw new RuntimeException('Workspace nėra visų privalomų šaltinio failų.');
        }
        $this->validateWorkspaceSources($workspace);
        $id = (string) Str::uuid();
        $root = $this->path($id);
        File::ensureDirectoryExists($root.'/source', 0750, true);
        $sources = ['wordpress_xml' => [$workspace['xml'], 'wordpress.xml'], 'wordpress_sql' => [$workspace['sql'], str_ends_with(strtolower($workspace['sql']['name']), '.gz') ? 'wordpress.sql.gz' : 'wordpress.sql']];
        if ($workspace['uploads']['source_type'] === 'zip') {
            $sources['uploads_zip'] = [$workspace['uploads'], 'uploads.zip'];
        }
        $media = $workspace['uploads'];
        $manifest = ['id' => $id, 'created_at' => now()->toIso8601String(), 'source_mode' => 'workspace', 'analysis_status' => 'pending', 'files' => [], 'warnings' => [], 'errors' => [], 'media_source' => $this->mediaManifest($media)];
        foreach ($sources as $key => [$source, $stored]) {
            File::copy($source['path'], $root.'/source/'.$stored);
            $manifest['files'][$key] = ['original_name' => $source['name'], 'stored_name' => $stored, 'size' => filesize($root.'/source/'.$stored), 'sha256' => null, 'uploaded_at' => now()->toIso8601String()];
        }
        $this->writeJson($root.'/manifest.json', $manifest);

        return $manifest;
    }

    public function latest(): ?array
    {
        if (! is_dir($this->base())) {
            return null;
        } $manifests = glob($this->base().'/*/manifest.json') ?: [];
        usort($manifests, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        if (! $manifests) {
            return null;
        }

        return $this->synchronizeWorkspaceDirectory($this->readJson($manifests[0]));
    }

    public function find(string $id): array
    {
        $this->assertId($id);
        $path = $this->path($id).'/manifest.json';
        if (! is_file($path)) {
            abort(404);
        }

        return $this->readJson($path);
    }

    public function analyze(string $id): array
    {
        $manifest = $this->synchronizeWorkspaceDirectory($this->find($id));
        $root = $this->path($id);
        $manifest['analysis_status'] = 'running';
        $manifest['analysis_started_at'] = now()->toIso8601String();
        $this->writeJson($root.'/manifest.json', $manifest);
        try {
            foreach ($manifest['files'] as $key => $file) {
                $sourcePath = $root.'/source/'.$file['stored_name'];
                $manifest['files'][$key]['sha256'] = hash_file('sha256', $sourcePath);
                $manifest['files'][$key]['size'] = filesize($sourcePath);
                $manifest['files'][$key]['modified_timestamp'] = filemtime($sourcePath);
            }
            $this->writeJson($root.'/manifest.json', $manifest);
            $mediaSource = $manifest['media_source'] ?? ['source_type' => 'zip', 'source_path' => 'source/uploads.zip', 'requires_extraction' => true];
            if ($mediaSource['source_type'] === 'directory') {
                $current = $this->workspaceScanner->validateDirectory($mediaSource['source_path']);
                if ($current['file_count'] !== $mediaSource['file_count'] || $current['size'] !== $mediaSource['size'] || $current['latest_modified_timestamp'] !== $mediaSource['latest_modified_timestamp']) {
                    throw new RuntimeException('Workspace „uploads“ katalogas pasikeitė po šaltinio pasirinkimo. Atnaujinkite migracijos sesiją.');
                }
                $uploadsDirectory = $current['path'];
            } else {
                if (is_dir($root.'/extracted') && count(scandir($root.'/extracted')) > 2) {
                    throw new RuntimeException('Išskleidimo katalogas nėra tuščias.');
                }
                File::ensureDirectoryExists($root.'/extracted', 0750, true);
                app(SafeArchiveExtractor::class)->extract($root.'/source/uploads.zip', $root.'/extracted');
                $uploadsDirectory = $root.'/extracted';
            }
            $xml = $this->xmlAnalyzer->analyze($root.'/source/'.$manifest['files']['wordpress_xml']['stored_name']);
            $sql = $this->sqlAnalyzer->analyze($root.'/source/'.$manifest['files']['wordpress_sql']['stored_name']);
            $uploads = $this->uploadsAnalyzer->analyze($uploadsDirectory, $xml['inline_image_urls']);
            $uploads['media_source_type'] = $mediaSource['source_type'];
            $report = $this->reportBuilder->build($xml, $sql, $uploads, $manifest['warnings'] ?? []);
            File::ensureDirectoryExists($root.'/reports', 0750, true);
            $this->writeJson($root.'/reports/analysis.json', $report);
            $manifest['analysis_status'] = 'complete';
            $manifest['analysis_report_path'] = 'reports/analysis.json';
            $manifest['analyzed_at'] = now()->toIso8601String();
        } catch (Throwable $exception) {
            report($exception);
            $manifest['analysis_status'] = 'failed';
            $manifest['errors'][] = $exception->getMessage();
        }
        $this->writeJson($root.'/manifest.json', $manifest);

        return $manifest;
    }

    public function markAnalysisQueued(string $id): array
    {
        $manifest = $this->find($id);
        $manifest['analysis_status'] = 'queued';
        $manifest['analysis_queued_at'] = now()->toIso8601String();
        $manifest['errors'] = [];
        $this->writeJson($this->path($id).'/manifest.json', $manifest);

        return $manifest;
    }

    public function analysisQueueIsStalled(array $manifest): bool
    {
        if (($manifest['analysis_status'] ?? null) !== 'queued' || empty($manifest['analysis_queued_at'])) {
            return false;
        }

        return Carbon::parse($manifest['analysis_queued_at'])
            ->addSeconds(max(1, (int) config('wordpress-migration.queue_stale_after_seconds', 120)))
            ->isPast();
    }

    public function report(array $manifest): ?array
    {
        $relative = $manifest['analysis_report_path'] ?? null;
        if (! $relative || ! is_file($this->path($manifest['id']).'/'.$relative)) {
            return null;
        }

        return $this->reportNormalizer->normalize($this->readJson($this->path($manifest['id']).'/'.$relative));
    }

    public function saveImportSettings(string $id, array $settings): array
    {
        $manifest = $this->find($id);
        $manifest['import_settings'] = $settings;
        $manifest['import_settings_saved_at'] = now()->toIso8601String();
        $this->writeJson($this->path($id).'/manifest.json', $manifest);

        return $manifest;
    }

    public function saveSeoSettings(string $id, array $settings): array
    {
        $manifest = $this->find($id);
        $manifest['seo_settings'] = $settings;
        $manifest['seo_settings_saved_at'] = now()->toIso8601String();
        $this->writeJson($this->path($id).'/manifest.json', $manifest);

        return $manifest;
    }

    public function sessionPath(string $id, string $relative = ''): string
    {
        $this->assertId($id);
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        if (in_array('..', explode('/', $relative), true)) {
            throw new RuntimeException('Neleistinas migracijos sesijos kelias.');
        }

        return $this->path($id).($relative !== '' ? DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative) : '');
    }

    public function storePrivateJson(string $id, string $relative, array $value): void
    {
        $this->writeJson($this->sessionPath($id, $relative), $value);
    }

    public function readPrivateJson(string $id, string $relative): array
    {
        $path = $this->sessionPath($id, $relative);
        if (! is_file($path)) {
            abort(404);
        }

        return $this->readJson($path);
    }

    public function updateManifest(string $id, array $changes): array
    {
        $manifest = array_replace($this->find($id), $changes);
        $this->writeJson($this->sessionPath($id, 'manifest.json'), $manifest);

        return $manifest;
    }

    public function currentSourceFingerprints(string $id): array
    {
        $manifest = $this->find($id);
        $files = [];
        foreach ($manifest['files'] as $key => $file) {
            $path = $this->sessionPath($id, 'source/'.$file['stored_name']);
            if (! is_file($path)) {
                throw new RuntimeException('Migracijos šaltinio failas neberastas.');
            }
            $files[$key] = ['sha256' => hash_file('sha256', $path), 'size' => filesize($path)];
        }
        $media = array_intersect_key($manifest['media_source'] ?? [], array_flip(['source_type', 'size', 'file_count', 'latest_modified_timestamp']));
        if (($manifest['media_source']['source_type'] ?? null) === 'directory') {
            $current = $this->workspaceScanner->validateDirectory($manifest['media_source']['working_path']);
            $media = array_intersect_key($current, array_flip(['source_type', 'size', 'file_count', 'latest_modified_timestamp']));
        }

        return ['files' => $files, 'media' => $media];
    }

    public function sourceMetadataIsCurrent(string $id): bool
    {
        $manifest = $this->find($id);
        foreach ($manifest['files'] as $file) {
            $path = $this->sessionPath($id, 'source/'.$file['stored_name']);
            if (! is_file($path) || filesize($path) !== (int) $file['size'] || filemtime($path) !== (int) ($file['modified_timestamp'] ?? 0)) {
                return false;
            }
        }
        if (($manifest['media_source']['source_type'] ?? null) === 'directory') {
            $path = $manifest['media_source']['working_path'];
            if (! is_dir($path) || is_link($path)) {
                return false;
            }
        }

        return true;
    }

    public function delete(string $id): void
    {
        $this->assertId($id);
        $path = $this->path($id);
        if (! is_dir($path)) {
            abort(404);
        } File::deleteDirectory($path);
    }

    private function base(): string
    {
        return rtrim(config('wordpress-migration.storage_path'), '/\\');
    }

    private function path(string $id): string
    {
        return $this->base().DIRECTORY_SEPARATOR.$id;
    }

    private function assertId(string $id): void
    {
        if (! Str::isUuid($id)) {
            abort(404);
        }
    }

    private function readJson(string $path): array
    {
        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function writeJson(string $path, array $value): void
    {
        File::ensureDirectoryExists(dirname($path), 0750, true);
        file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX);
    }

    private function synchronizeWorkspaceDirectory(array $manifest): array
    {
        if (($manifest['source_mode'] ?? null) !== 'workspace') {
            return $manifest;
        }
        $workspace = $this->workspaceScanner->scan();
        if (($workspace['uploads']['source_type'] ?? null) !== 'directory') {
            return $manifest;
        }
        $wasDirectory = ($manifest['media_source']['source_type'] ?? null) === 'directory';
        $manifest['media_source'] = $this->mediaManifest($workspace['uploads']);
        unset($manifest['files']['uploads_zip']);
        if (! $wasDirectory) {
            $manifest['analysis_status'] = 'pending';
            $manifest['errors'] = [];
            $manifest['warnings'] = [];
            unset($manifest['analysis_report_path'], $manifest['analyzed_at'], $manifest['import_settings'], $manifest['import_settings_saved_at'], $manifest['seo_settings'], $manifest['seo_settings_saved_at']);
        }
        $this->writeJson($this->path($manifest['id']).'/manifest.json', $manifest);

        return $manifest;
    }

    private function mediaManifest(array $media): array
    {
        return ['source_type' => $media['source_type'], 'original_name' => $media['name'], 'source_path' => $media['source_type'] === 'directory' ? $media['path'] : 'source/uploads.zip', 'working_path' => $media['source_type'] === 'directory' ? $media['path'] : 'extracted', 'size' => $media['size'], 'file_count' => $media['file_count'] ?? null, 'latest_modified_timestamp' => $media['latest_modified_timestamp'] ?? null, 'modified_at' => $media['modified_at'], 'checksum_strategy' => $media['checksum_strategy'], 'requires_extraction' => $media['requires_extraction']];
    }

    private function validateWorkspaceSources(array $workspace): void
    {
        if ($workspace['xml']['size'] > config('wordpress-migration.max_xml_kb') * 1024 || $workspace['sql']['size'] > config('wordpress-migration.max_sql_kb') * 1024 || ($workspace['uploads']['source_type'] === 'zip' && $workspace['uploads']['size'] > config('wordpress-migration.max_zip_kb') * 1024)) {
            throw new RuntimeException('Workspace šaltinio failas viršija leistiną dydį.');
        }
        $xmlHead = file_get_contents($workspace['xml']['path'], false, null, 0, 512);
        if (! preg_match('/^\s*(?:\xEF\xBB\xBF)?<\?xml\b|^\s*<rss\b/i', $xmlHead ?: '')) {
            throw new RuntimeException('Workspace XML failo turinys negaliojantis.');
        }
        $sqlHead = file_get_contents($workspace['sql']['path'], false, null, 0, 2);
        if (str_ends_with(strtolower($workspace['sql']['name']), '.gz') && $sqlHead !== "\x1f\x8b") {
            throw new RuntimeException('Workspace GZIP failo parašas negaliojantis.');
        }
        if (! str_ends_with(strtolower($workspace['sql']['name']), '.gz') && ($sqlHead === false || str_contains($sqlHead, "\0"))) {
            throw new RuntimeException('Workspace SQL failas turi būti tekstinis.');
        }
        if ($workspace['uploads']['source_type'] === 'zip') {
            $zipHead = file_get_contents($workspace['uploads']['path'], false, null, 0, 4);
            if (! in_array($zipHead, ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true)) {
                throw new RuntimeException('Workspace ZIP failo parašas negaliojantis.');
            }
        } else {
            $this->workspaceScanner->validateDirectory($workspace['uploads']['path']);
        }
    }
}
