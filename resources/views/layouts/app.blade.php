<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $pageTitle ?? config('app.name', 'Laravel') }}</title>

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

            <main class="flex-1">
                @yield('content')
            </main>

            @include('components.site-footer')
        </div>

        <button type="button" class="back-to-top" data-back-to-top aria-label="Back to top">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="m6 15 6-6 6 6"/>
            </svg>
        </button>

        @stack('scripts')
    </body>
</html>
