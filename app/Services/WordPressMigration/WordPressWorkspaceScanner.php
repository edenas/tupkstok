<?php

namespace App\Services\WordPressMigration;

use FilesystemIterator;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class WordPressWorkspaceScanner
{
    public function scan(): array
    {
        $configuredPath = rtrim(config('wordpress-migration.workspace_path'), '/\\');
        $workspacePath = realpath($configuredPath);
        $result = ['path' => $configuredPath, 'exists' => $workspacePath !== false, 'xml' => null, 'sql' => null, 'uploads' => null, 'uploads_zip_ignored' => false, 'media_error' => null];
        if ($workspacePath === false || ! is_dir($workspacePath)) return $result;

        $result['path'] = $workspacePath;
        $result['xml'] = $this->newest($workspacePath, ['*.xml']);
        $result['sql'] = $this->newest($workspacePath, ['*.sql', '*.sql.gz']);
        $directory = $workspacePath.DIRECTORY_SEPARATOR.config('wordpress-migration.workspace_uploads_directory_name');

        if (is_dir($directory)) {
            try {
                $result['uploads'] = $this->validateDirectory($directory, $workspacePath);
            } catch (RuntimeException $exception) {
                $result['media_error'] = $exception->getMessage();
            }
        } else {
            $archive = $this->newest($workspacePath, [config('wordpress-migration.workspace_uploads_archive_pattern')]);
            if ($archive) $result['uploads'] = $archive + ['source_type' => 'zip', 'display_name' => $archive['name'], 'requires_extraction' => true, 'checksum_strategy' => 'sha256'];
        }

        return $result;
    }

    public function complete(array $workspace): bool
    {
        return $workspace['xml'] !== null && $workspace['sql'] !== null && $workspace['uploads'] !== null;
    }

    public function validateDirectory(string $directory, ?string $workspace = null): array
    {
        $workspacePath = realpath($workspace ?? config('wordpress-migration.workspace_path'));
        $directoryPath = realpath($directory);
        if ($workspacePath === false || $directoryPath === false || ! is_dir($directoryPath)) throw new RuntimeException('„uploads“ katalogas neegzistuoja arba yra nepasiekiamas.');
        $this->assertInside($directoryPath, $workspacePath);
        if (is_link($directory)) throw new RuntimeException('„uploads“ katalogas negali būti simbolinis saitas.');

        $count = 0; $size = 0; $latest = filemtime($directoryPath) ?: 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if ($file->isLink()) throw new RuntimeException('„uploads“ kataloge rasta nesaugi simbolinė nuoroda.');
            $resolved = realpath($path);
            if ($resolved === false) throw new RuntimeException('„uploads“ kataloge rastas neperskaitomas kelias.');
            $this->assertInside($resolved, $directoryPath);
            $latest = max($latest, $file->getMTime());
            if (! $file->isFile()) continue;
            if (! $file->isReadable()) throw new RuntimeException('„uploads“ kataloge rastas neperskaitomas failas.');
            $count++; $size += $file->getSize();
            if ($count > config('wordpress-migration.maximum_directory_file_count')) throw new RuntimeException('„uploads“ kataloge yra per daug failų.');
            if ($size > config('wordpress-migration.maximum_directory_total_size')) throw new RuntimeException('„uploads“ katalogo dydis viršija leistiną ribą.');
        }

        return ['path' => $directoryPath, 'name' => basename($directoryPath), 'display_name' => 'uploads/', 'size' => $size, 'file_count' => $count, 'modified_at' => date(DATE_ATOM, $latest), 'latest_modified_timestamp' => $latest, 'source_type' => 'directory', 'requires_extraction' => false, 'checksum_strategy' => 'staged'];
    }

    private function newest(string $path, array $patterns): ?array
    {
        $matches = [];
        foreach ($patterns as $pattern) foreach (File::glob($path.DIRECTORY_SEPARATOR.$pattern) ?: [] as $file) if (is_file($file) && ! is_link($file)) $matches[] = $file;
        if (! $matches) return null;
        usort($matches, fn (string $a, string $b) => filemtime($b) <=> filemtime($a));
        $file = $matches[0];
        return ['path' => realpath($file), 'name' => basename($file), 'size' => filesize($file), 'modified_at' => date(DATE_ATOM, filemtime($file))];
    }

    private function assertInside(string $path, string $root): void
    {
        $root = rtrim(str_replace('\\', '/', $root), '/').'/';
        $path = str_replace('\\', '/', $path);
        if (! str_starts_with(strtolower($path.'/'), strtolower($root))) throw new RuntimeException('„uploads“ katalogo kelias išeina už Workspace ribų.');
    }
}
