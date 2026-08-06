<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $pageTitle ?? 'Administratoriaus prisijungimas' }} - Tupk Stok</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')
    </head>
    <body class="auth-body">
        <main class="auth-shell">
            @yield('content')
        </main>

        @stack('scripts')
    </body>
</html>
