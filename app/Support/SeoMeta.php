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
     * @return array{title: string, description: string, keywords: string, url: string, type: string}
     */
    public static function current(): array
    {
        $locale = in_array(app()->getLocale(), ['en', 'ru'], true) ? app()->getLocale() : 'lt';
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

        $title = self::value($pageSetting, 'meta_title_'.$locale)
            ?? self::value($globalSetting, 'meta_title_'.$locale)
            ?? 'EPgalerija';

        $description = self::value($pageSetting, 'meta_description_'.$locale)
            ?? self::value($globalSetting, 'meta_description_'.$locale)
            ?? '';

        $keywords = self::value($pageSetting, 'keywords_'.$locale)
            ?? self::value($globalSetting, 'keywords_'.$locale)
            ?? '';

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'url' => request()->fullUrl(),
            'type' => 'website',
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string, url: string, type: string}
     */
    private static function fallback(): array
    {
        return [
            'title' => 'EPgalerija',
            'description' => '',
            'keywords' => '',
            'url' => request()->fullUrl(),
            'type' => 'website',
        ];
    }

    private static function pageKeyForCurrentRoute(): string
    {
        $routeName = (string) Route::currentRouteName();
        $routeName = str_starts_with($routeName, 'en.') ? substr($routeName, 3) : $routeName;

        return match (true) {
            $routeName === 'home' => 'home',
            $routeName === 'about-me' => 'about',
            $routeName === 'web-solutions' => 'web_solutions',
            $routeName === 'mobile-apps' => 'mobile_apps',
            str_starts_with($routeName, 'graphics') => 'graphics',
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
