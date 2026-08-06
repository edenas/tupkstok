<?php

namespace App\Services\WordPressMigration;

use RuntimeException;
use ZipArchive;

class SafeArchiveExtractor
{
    public function extract(string $archive, string $destination): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZIP plėtinys serveryje neįjungtas.');
        }

        $zip = new ZipArchive;
        if ($zip->open($archive) !== true) throw new RuntimeException('Nepavyko atidaryti ZIP archyvo.');
        $count = 0; $total = 0; $entries = [];
        try {
            if ($zip->numFiles > config('wordpress-migration.max_extracted_files')) throw new RuntimeException('Archyve yra per daug failų.');
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = str_replace('\\', '/', $stat['name'] ?? '');
                $this->assertSafeEntry($zip, $i, $name);
                if (str_ends_with($name, '/')) continue;
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($extension, config('wordpress-migration.rejected_extensions'), true)) throw new RuntimeException("Archyve rastas vykdomasis failas: {$name}");
                $size = (int) ($stat['size'] ?? 0); $compressed = (int) ($stat['comp_size'] ?? 0);
                $total += $size; $count++;
                if ($total > config('wordpress-migration.max_extracted_bytes')) throw new RuntimeException('Išskleisto archyvo dydis viršija limitą.');
                if ($size > 0 && $compressed === 0 || ($compressed > 0 && $size / $compressed > config('wordpress-migration.max_compression_ratio'))) throw new RuntimeException('Archyvo suspaudimo santykis yra nesaugus.');
                $target = $destination.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $name);
                if (! is_dir(dirname($target))) mkdir(dirname($target), 0750, true);
                $input = $zip->getStream($stat['name']); $output = fopen($target, 'xb');
                if (! $input || ! $output) throw new RuntimeException("Nepavyko saugiai išskleisti failo: {$name}");
                stream_copy_to_stream($input, $output); fclose($input); fclose($output);
                $entries[] = $name;
            }
        } finally { $zip->close(); }
        return ['file_count' => $count, 'total_size' => $total, 'entries' => $entries];
    }

    private function assertSafeEntry(ZipArchive $zip, int $index, string $name): void
    {
        if ($name === '' || str_contains($name, "\0") || str_starts_with($name, '/') || preg_match('/^[A-Za-z]:\//', $name) || in_array('..', explode('/', $name), true)) throw new RuntimeException('Archyve rastas nesaugus failo kelias.');
        if ($zip->getExternalAttributesIndex($index, $opsys, $attributes) && $opsys === ZipArchive::OPSYS_UNIX && (($attributes >> 16) & 0170000) === 0120000) throw new RuntimeException('Archyve rastas simbolinis saitas.');
    }
}
