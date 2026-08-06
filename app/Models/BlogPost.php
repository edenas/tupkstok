<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'category',
    'description',
    'project_details',
    'thumbnail',
    'youtube_url',
    'position',
])]
class BlogPost extends Model
{
    private const LEGACY_THUMBNAIL_DIRECTORY = 'portfolio-thumbnails';
    private const MEDIA_LIBRARY_DIRECTORY = 'media-library';
    private const DEFAULT_ARTICLE_AUTHOR = 'Tūpk Stok redakcija';
    private const DEFAULT_ARTICLE_SOURCE = 'www.tupkstok.lt';

    protected $table = 'portfolio_posts';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
        'project_details' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $blogPost): void {
            if (! $blogPost->exists || $blogPost->isDirty('title')) {
                $blogPost->slug = static::uniqueSlug($blogPost->title, $blogPost->id);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'blog-post';
        $baseSlug = Str::substr($baseSlug, 0, 255);
        $slug = $baseSlug;
        $suffix = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $suffixText = '-'.$suffix;
            $slug = Str::substr($baseSlug, 0, 255 - strlen($suffixText)).$suffixText;
            $suffix++;
        }

        return $slug;
    }

    public function localized(string $field): mixed
    {
        return $this->{$field};
    }

    public function localizedTitle(): string
    {
        return (string) $this->localized('title');
    }

    public function localizedCategory(): string
    {
        return (string) $this->localized('category');
    }

    public function localizedDescription(): ?string
    {
        return $this->localized('description');
    }

    /**
     * @return array{author: string, source: string, show_disclaimer: bool, disclaimer_text: string}
     */
    public function articleInformation(): array
    {
        $information = $this->localized('project_details');
        $information = is_array($information) ? $information : [];

        return [
            'author' => $this->articleInformationValue($information['author'] ?? null, self::DEFAULT_ARTICLE_AUTHOR),
            'source' => $this->articleInformationValue($information['source'] ?? null, self::DEFAULT_ARTICLE_SOURCE),
            'show_disclaimer' => filter_var($information['show_disclaimer'] ?? true, FILTER_VALIDATE_BOOL),
            'disclaimer_text' => trim((string) ($information['disclaimer_text'] ?? '')),
        ];
    }

    private function articleInformationValue(mixed $value, string $fallback): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }

    /**
     * Get the public URL for the stored thumbnail.
     */
    public function thumbnailUrl(): string
    {
        if (! $this->thumbnail) {
            return '';
        }

        return '/storage/'.$this->thumbnailPath();
    }

    /**
     * Get the normalized storage-relative thumbnail path.
     */
    public function thumbnailPath(): string
    {
        $thumbnail = ltrim((string) $this->thumbnail, '/');
        $storagePosition = strpos($thumbnail, 'storage/');

        if ($storagePosition !== false) {
            $thumbnail = substr($thumbnail, $storagePosition + strlen('storage/'));
        }

        $thumbnail = ltrim($thumbnail, '/');

        if (str_starts_with($thumbnail, self::LEGACY_THUMBNAIL_DIRECTORY . '/')) {
            $mediaLibraryPath = self::MEDIA_LIBRARY_DIRECTORY . '/' . basename($thumbnail);

            if (Storage::disk('public')->fileExists($mediaLibraryPath)) {
                return $mediaLibraryPath;
            }
        }

        return $thumbnail;
    }

    /**
     * Get an embeddable YouTube URL from a regular YouTube URL.
     */
    public function youtubeEmbedUrl(): string
    {
        if (! $this->youtube_url) {
            return '';
        }

        $parts = parse_url($this->youtube_url);

        if (! $parts || empty($parts['host'])) {
            return '';
        }

        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');
        $videoId = '';

        $host = preg_replace('/^www\./', '', $host);

        if ($host === 'youtu.be') {
            $videoId = explode('/', $path)[0] ?? '';
        } elseif (in_array($host, ['youtube.com', 'm.youtube.com'], true)) {
            if (str_starts_with($path, 'embed/')) {
                $videoId = explode('/', substr($path, strlen('embed/')))[0] ?? '';
            } elseif ($path === 'watch') {
                parse_str($parts['query'] ?? '', $query);
                $videoId = $query['v'] ?? '';
            }
        }

        if (! preg_match('/^[A-Za-z0-9_-]+$/', $videoId)) {
            return '';
        }

        return 'https://www.youtube.com/embed/'.$videoId;
    }
}
