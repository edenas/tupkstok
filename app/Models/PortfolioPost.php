<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'title_en',
    'category',
    'category_en',
    'short_description',
    'short_description_en',
    'content_heading',
    'content_heading_en',
    'description',
    'description_en',
    'project_details',
    'project_details_en',
    'thumbnail',
    'post_image',
    'youtube_url',
    'position',
])]
class PortfolioPost extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
        'project_details' => 'array',
        'project_details_en' => 'array',
    ];

    public function localized(string $field): mixed
    {
        if (app()->getLocale() !== 'en') {
            return $this->{$field};
        }

        $englishField = $field.'_en';
        $englishValue = $this->{$englishField} ?? null;

        if (is_array($englishValue)) {
            return $englishValue !== [] ? $englishValue : $this->{$field};
        }

        return filled($englishValue) ? $englishValue : $this->{$field};
    }

    public function localizedTitle(): string
    {
        return (string) $this->localized('title');
    }

    public function localizedCategory(): string
    {
        return (string) $this->localized('category');
    }

    public function localizedShortDescription(): ?string
    {
        return $this->localized('short_description');
    }

    public function localizedContentHeading(): ?string
    {
        return $this->localized('content_heading');
    }

    public function localizedDescription(): ?string
    {
        return $this->localized('description');
    }

    /**
     * @return array<int, string>
     */
    public function localizedProjectDetails(): array
    {
        $details = $this->localized('project_details');

        return is_array($details) ? $details : [];
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

        return ltrim($thumbnail, '/');
    }

    /**
     * Get the public URL for the stored post image.
     */
    public function postImageUrl(): string
    {
        if (! $this->post_image) {
            return '';
        }

        return '/storage/'.$this->postImagePath();
    }

    /**
     * Get the normalized storage-relative post image path.
     */
    public function postImagePath(): string
    {
        $postImage = ltrim((string) $this->post_image, '/');
        $storagePosition = strpos($postImage, 'storage/');

        if ($storagePosition !== false) {
            $postImage = substr($postImage, $storagePosition + strlen('storage/'));
        }

        return ltrim($postImage, '/');
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

        if (str_contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? '';
        } elseif (str_contains($host, 'youtube.com')) {
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
