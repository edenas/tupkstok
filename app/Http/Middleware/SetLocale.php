<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const DEFAULT_LOCALE = 'lt';
    private const SUPPORTED_LOCALES = ['lt', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $sessionKey = $request->is('admin*') ? 'admin_locale' : 'public_locale';
        $locale = $request->session()->get($sessionKey, self::DEFAULT_LOCALE);

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = self::DEFAULT_LOCALE;
            $request->session()->put($sessionKey, $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
