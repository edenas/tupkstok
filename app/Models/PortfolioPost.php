<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'category',
    'short_description',
    'content_heading',
    'description',
    'project_details',
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
    ];

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
