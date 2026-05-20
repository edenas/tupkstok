<?php

namespace App\Http\Controllers;

use App\Support\LocalizedUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['lt', 'en'], true)) {
            $locale = 'lt';
        }

        $previousPath = ltrim(parse_url(url()->previous(), PHP_URL_PATH) ?? '', '/');
        $sessionKey = str_starts_with($previousPath, 'admin') ? 'admin_locale' : 'public_locale';

        $request->session()->put($sessionKey, $locale);

        if ($sessionKey === 'public_locale') {
            return redirect()->to($this->localizedPreviousUrl($locale, $previousPath));
        }

        return redirect()->back();
    }

    private function localizedPreviousUrl(string $locale, string $previousPath): string
    {
        $path = trim($previousPath, '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (($segments[0] ?? null) === 'en') {
            array_shift($segments);
        }

        $slug = $segments[0] ?? '';
        $portfolioPost = $segments[1] ?? null;
        $query = [];

        parse_str(parse_url(url()->previous(), PHP_URL_QUERY) ?? '', $query);

        $route = match ($slug) {
            '' => 'home',
            'apie-mane', 'about-me' => 'about-me',
            'web-sprendimai', 'web-solutions' => 'web-solutions',
            'mobiliosios-aplikacijos', 'mobile-apps' => 'mobile-apps',
            'grafika', 'graphics' => $portfolioPost ? 'graphics.show' : 'graphics',
            'kontaktai', 'contact' => 'contact',
            default => 'home',
        };

        $parameters = $route === 'graphics.show' ? ['portfolioPost' => $portfolioPost] : [];

        return LocalizedUrl::route($route, $parameters + $query, true, $locale);
    }
}
