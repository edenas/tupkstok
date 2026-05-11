<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $pageTitle ?? config('app.name', 'Laravel') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')
    </head>
    <body class="min-h-screen bg-white text-slate-900 dark:bg-slate-950 dark:text-white">
        <div id="app" class="min-h-screen flex flex-col">
            @include('components.site-header')

            <main class="flex-1">
                @yield('content')
            </main>

            @include('components.site-footer')
        </div>

        @stack('scripts')
    </body>
</html>
