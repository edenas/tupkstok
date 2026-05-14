<?php

namespace App\Http\Controllers;

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

        return redirect()->back();
    }
}
