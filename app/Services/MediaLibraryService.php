<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryService
{
    private const DIRECTORY = 'media-library';
    private const PROTECTED_FILES = [
        'logo.png',
        'logo_white.png',
    ];

    /**
     * @return Collection<int, array{
     *     filename: string,
     *     path: string,
     *     url: string,
     *     size: int,
     *     lastModified: int,
     *     sourceLabel: string,
     *     canDelete: bool,
     *     deleteDisabledReason: string|null
     * }>
     */
    public function all(): Collection
    {
        $disk = Storage::disk('public');
        $usedMediaPaths = $this->usedMediaPaths();

        return collect($this->mediaPaths($disk))
            ->filter(fn (string $path): bool => $this->isSupportedImagePath($path) && $disk->fileExists($path))
            ->map(fn (string $path): array => $this->mediaItem($disk, $path, $usedMediaPaths))
            ->sortByDesc('lastModified')
            ->values();
    }

    public function store(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid()->toString() . '.' . $extension;

        return $file->storeAs(self::DIRECTORY, $filename, 'public');
    }

    public function delete(string $filename): bool
    {
        if (! $this->isSafeFilename($filename)) {
            return false;
        }

        $path = self::DIRECTORY . '/' . $filename;
        $disk = Storage::disk('public');

        if (! $disk->fileExists($path) || $this->usedMediaPaths()->contains($path)) {
            return false;
        }

        return $disk->delete($path);
    }

    /**
     * @return array<int, string>
     */
    private function mediaPaths(mixed $disk): array
    {
        $paths = $disk->directoryExists(self::DIRECTORY)
            ? $disk->files(self::DIRECTORY)
            : [];

        foreach (self::PROTECTED_FILES as $path) {
            if ($disk->fileExists($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function mediaItem(mixed $disk, string $path, Collection $usedMediaPaths): array
    {
        $isProtected = in_array($path, self::PROTECTED_FILES, true);
        $isUsed = $usedMediaPaths->contains($path);

        return [
            'filename' => basename($path),
            'path' => $path,
            'url' => $disk->url($path),
            'size' => $disk->size($path),
            'lastModified' => $disk->lastModified($path),
            'sourceLabel' => $isProtected ? 'Sistemos failai' : 'Failų saugykla',
            'canDelete' => ! $isProtected && ! $isUsed,
            'deleteDisabledReason' => $this->deleteDisabledReason($isProtected, $isUsed),
        ];
    }

    private function deleteDisabledReason(bool $isProtected, bool $isUsed): ?string
    {
        if ($isProtected) {
            return 'Sistemos failas, jo trinti negalima.';
        }

        if ($isUsed) {
            return 'Naudojama Blog straipsnyje.';
        }

        return null;
    }

    private function usedMediaPaths(): Collection
    {
        $posts = BlogPost::query()
            ->whereNotNull('thumbnail')
            ->orWhereNotNull('description')
            ->get(['thumbnail', 'description']);

        $thumbnailPaths = $posts->pluck('thumbnail')
            ->map(fn (mixed $thumbnail): string => $this->normalizeMediaPath((string) $thumbnail))
            ->filter();

        $embeddedImagePaths = $posts->pluck('description')
            ->flatMap(fn (mixed $description): array => $this->embeddedMediaPaths((string) $description));

        return $thumbnailPaths->merge($embeddedImagePaths)->unique()->values();
    }

    /** @return array<int, string> */
    private function embeddedMediaPaths(string $html): array
    {
        preg_match_all('~<img\b[^>]*\bsrc=["\x27]([^"\x27]+)["\x27]~i', $html, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $path): string => $this->normalizeMediaPath($path))
            ->filter(fn (string $path): bool => str_starts_with($path, self::DIRECTORY.'/'))
            ->values()
            ->all();
    }

    private function normalizeMediaPath(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $storagePosition = strpos($path, 'storage/');

        if ($storagePosition !== false) {
            $path = substr($path, $storagePosition + strlen('storage/'));
        }

        if (str_starts_with($path, 'portfolio-thumbnails/')) {
            return self::DIRECTORY . '/' . basename($path);
        }

        return $path;
    }

    private function isSupportedImagePath(string $path): bool
    {
        return preg_match('/\.(jpe?g|png|webp|gif)$/i', $path) === 1;
    }

    private function isSafeFilename(string $filename): bool
    {
        return basename($filename) === $filename
            && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.(jpe?g|png|webp|gif)$/i', $filename) === 1;
    }
}
