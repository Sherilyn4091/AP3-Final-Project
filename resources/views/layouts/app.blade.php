<!DOCTYPE html>
<html lang="{{ strreplace('', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Music Lab') }}</title>

    <!-- Vite will inject both CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Optional: Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    {{ $slot }}

</body>
</html>