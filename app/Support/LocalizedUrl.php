<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class LocalizedUrl
{
    private const PUBLIC_ROUTES = [
        'home',
        'about-me',
        'contact',
        'web-solutions',
        'mobile-apps',
        'graphics',
        'graphics.show',
    ];

    public static function route(string $name, mixed $parameters = [], bool $absolute = true, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $routeName = self::localizedRouteName($name, $locale);

        return route($routeName, $parameters, $absolute);
    }

    public static function current(string $locale): string
    {
        $route = request()->route();
        $name = $route?->getName();

        if (! $name) {
            return self::route('home', [], true, $locale);
        }

        $baseName = str_starts_with($name, 'en.') ? substr($name, 3) : $name;

        if (! in_array($baseName, self::PUBLIC_ROUTES, true)) {
            return url('/');
        }

        return self::route($baseName, $route->parameters() + request()->query(), true, $locale);
    }

    public static function active(string $name): bool
    {
        $current = Route::currentRouteName();

        return $current === $name || $current === 'en.'.$name || str_starts_with((string) $current, $name.'.') || str_starts_with((string) $current, 'en.'.$name.'.');
    }

    private static function localizedRouteName(string $name, string $locale): string
    {
        if ($locale === 'en' && in_array($name, self::PUBLIC_ROUTES, true)) {
            return 'en.'.$name;
        }

        return $name;
    }
}
