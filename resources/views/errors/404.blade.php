<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Puslapis nerastas | Tupk Stok</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px;
            background: #f8fafc;
            color: #0f172a;
            font-family: Arial, sans-serif;
        }

        main {
            max-width: 560px;
            text-align: center;
        }

        img {
            width: 190px;
            height: auto;
            margin-bottom: 32px;
        }

        h1 {
            margin: 0 0 14px;
            font-size: clamp(34px, 7vw, 64px);
            line-height: 1;
        }

        p {
            margin: 0 0 28px;
            color: #475569;
            font-size: 18px;
            line-height: 1.6;
        }

        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 22px;
            border-radius: 999px;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main>
        <img src="{{ asset('storage/logo.png') }}" alt="Tupk Stok">
        <h1>404</h1>
        <p>Puslapis nerastas arba nuoroda nebegalioja.</p>
        <a href="{{ url('/') }}">Grįžti į pradžią</a>
    </main>
</body>
</html>
