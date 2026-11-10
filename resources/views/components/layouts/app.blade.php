<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Expédition Lens' }}</title>

    <!-- PWA Web App Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#d97706">

    <!-- iOS / Safari PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Expédition Lens">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/icon.svg">

    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        .btn-bounce {
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .btn-bounce:active {
            transform: translateY(3px) !important;
            box-shadow: 0 1px 0 #1A1A1A !important;
        }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot }}

    @livewireScripts
</body>
</html>
