<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Kontaktines formos zinute</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="font-size: 20px;">Nauja zinute is kontaktu formos</h1>

    <p><strong>Vardas:</strong> {{ $messageData['name'] }}</p>
    <p><strong>El. pastas:</strong> {{ $messageData['email'] }}</p>
    <p><strong>Tema:</strong> {{ $messageData['subject'] }}</p>
    <p><strong>Issiusta:</strong> {{ now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s T') }}</p>

    <h2 style="font-size: 16px;">Zinute</h2>
    <p style="white-space: pre-line;">{{ $messageData['message'] }}</p>
</body>
</html>
