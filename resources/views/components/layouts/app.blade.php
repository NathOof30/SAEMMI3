<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
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
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            height: 100vh;
            height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
            overscroll-behavior: none;
            background-color: #FAF8F5;
            background-image: radial-gradient(#D1D5DB 1.5px, transparent 1.5px);
            background-size: 20px 20px;
            background-attachment: fixed;
            font-family: system-ui, -apple-system, sans-serif;
            color: #1a1a1a;
            -webkit-text-size-adjust: 100%;
            touch-action: manipulation;
        }
        .btn-bounce {
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }
        .btn-bounce:active {
            transform: translateY(3px) !important;
            box-shadow: 0 1px 0 #1A1A1A !important;
        }
        .tab-pane-wrapper {
            flex: 1;
            min-height: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
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
    @if(request()->is('test-qr*'))
        <style>
            html, body {
                overflow-y: auto !important;
                height: auto !important;
                max-height: none !important;
                overscroll-behavior: auto !important;
                background-color: #0b1120 !important;
                background-image: none !important;
            }
        </style>
    @endif
    @livewireStyles
</head>
<body style="margin: 0; padding: 0; height: 100%; height: 100vh; height: 100dvh; max-height: 100dvh; overflow: hidden; background-color: #FAF8F5; background-image: radial-gradient(#D1D5DB 1.5px, transparent 1.5px); background-size: 20px 20px; background-attachment: fixed;">
    {{ $slot }}

    @livewireScripts
</body>
</html>
