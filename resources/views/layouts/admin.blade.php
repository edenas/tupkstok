<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageTitle ?? config('app.name', 'Laravel') }} - Admin</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')
    </head>
    <body class="admin-body">
        <div id="app" class="admin-shell">
            @include('components.admin-sidebar')

            <div class="admin-main-area">
                @include('components.admin-topbar')

                <main class="admin-content">
                    @yield('content')
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
