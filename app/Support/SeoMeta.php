<?php

namespace App\Support;

use App\Models\SeoSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;

class SeoMeta
{
    /**
     * Resolve SEO metadata for the current public route.
     *
     * @return array{title: string, description: string, keywords: string, url: string, image: string, type: string}
     */
    public static function current(): array
    {
        $pageKey = self::pageKeyForCurrentRoute();

        try {
            $settings = SeoSetting::query()
                ->whereIn('page_key', ['global', $pageKey])
                ->get()
                ->keyBy('page_key');
        } catch (QueryException) {
            return self::fallback();
        }

        $pageSetting = $settings->get($pageKey);
        $globalSetting = $settings->get('global');

        $title = self::value($pageSetting, 'meta_title_lt')
            ?? self::value($globalSetting, 'meta_title_lt')
            ?? 'Tupk Stok';

        $description = self::value($pageSetting, 'meta_description_lt')
            ?? self::value($globalSetting, 'meta_description_lt')
            ?? '';

        $keywords = self::value($pageSetting, 'keywords_lt')
            ?? self::value($globalSetting, 'keywords_lt')
            ?? '';

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'url' => request()->url(),
            'image' => asset('storage/logo.png'),
            'type' => 'website',
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string, url: string, image: string, type: string}
     */
    private static function fallback(): array
    {
        return [
            'title' => 'Tupk Stok',
            'description' => 'Tupk Stok',
            'keywords' => 'Tupk Stok',
            'url' => request()->url(),
            'image' => asset('storage/logo.png'),
            'type' => 'website',
        ];
    }

    private static function pageKeyForCurrentRoute(): string
    {
        $routeName = (string) Route::currentRouteName();
        return match (true) {
            $routeName === 'home' => 'home',
            str_starts_with($routeName, 'blog') => 'blog',
            $routeName === 'contact' => 'contact',
            default => 'global',
        };
    }

    private static function value(?SeoSetting $setting, string $field): ?string
    {
        if (! $setting) {
            return null;
        }

        $value = trim((string) $setting->{$field});

        return $value !== '' ? $value : null;
    }
}
