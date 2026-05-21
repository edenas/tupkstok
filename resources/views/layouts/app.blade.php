<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php($seo = \App\Support\SeoMeta::current())
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $pageTitle ?? $seo['title'] }}</title>
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="keywords" content="{{ $seo['keywords'] }}">
        <meta property="og:title" content="{{ $pageTitle ?? $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:type" content="{{ $seo['type'] }}">
        <meta property="og:url" content="{{ $seo['url'] }}">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">

        @fonts
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')
    </head>
    <body class="public-body">
        <div id="app" class="public-shell">
            @include('components.site-header')

            <main class="flex-1 public-page-transition" data-public-page-transition>
                @yield('content')
            </main>

            @include('components.site-footer')
        </div>

        <button type="button" class="back-to-top" data-back-to-top aria-label="Back to top">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="m6 15 6-6 6 6"/>
            </svg>
        </button>

        <div class="public-loading-overlay" data-public-loading-overlay aria-hidden="true">
            <div class="public-loading-overlay__spinner" role="status" aria-label="Loading"></div>
        </div>

        @stack('scripts')
    </body>
</html>
