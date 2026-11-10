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
        body {
            margin: 0;
            min-height: 100vh;
            background-color: #FAF8F5;
            background-image: radial-gradient(#D1D5DB 1.5px, transparent 1.5px);
            background-size: 20px 20px;
            background-attachment: fixed;
            font-family: system-ui, -apple-system, sans-serif;
            color: #1a1a1a;
            overflow-x: hidden;
        }
        .btn-bounce {
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .btn-bounce:active {
            transform: translateY(3px) !important;
            box-shadow: 0 1px 0 #1A1A1A !important;
        }
        @keyframes idle-sway {
            0%, 100% {
                transform: rotate(-10deg);
            }
            50% {
                transform: rotate(10deg);
            }
        }
        .idle-sway {
            animation: idle-sway 2.6s ease-in-out infinite;
            transform-origin: center;
            transform-box: fill-box;
        }
    </style>
    @livewireStyles
</head>
<body style="margin: 0; min-height: 100vh; background-color: #FAF8F5; background-image: radial-gradient(#D1D5DB 1.5px, transparent 1.5px); background-size: 20px 20px; background-attachment: fixed; overflow-x: hidden;">
    {{ $slot }}

    @livewireScripts
</body>
</html>
