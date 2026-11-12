<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? "GALI L'Expédition Secrète" }}</title>

    <!-- PWA Web App Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#000000">
    <meta name="msapplication-navbutton-color" content="#000000">
    <meta name="msapplication-TileColor" content="#000000">
    <meta name="color-scheme" content="light">

    <!-- iOS / Safari PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GALI">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" href="/logo_collab_noir.png" type="image/png">
    <link rel="apple-touch-icon" href="/logo_collab_noir.png">

    <link rel="preconnect" href="https://use.typekit.net" crossorigin>
    <link rel="preconnect" href="https://p.typekit.net" crossorigin>
    <link rel="stylesheet" href="https://use.typekit.net/msd2zjt.css">

    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --font-display: "hwt-artz", "Impact", sans-serif;
            --font-body: "nunito", system-ui, -apple-system, sans-serif;
        }

        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            min-height: 100%;
            overflow: hidden;
            overscroll-behavior: none;
            background-color: #FAF8F5;
            background-image: radial-gradient(#D1D5DB 1.5px, transparent 1.5px);
            background-size: 20px 20px;
            background-attachment: fixed;
            -webkit-text-size-adjust: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            height: 100vh;
            height: 100dvh;
            min-height: 100%;
            max-height: 100dvh;
            overflow: hidden;
            overscroll-behavior: none;
            background-color: #FAF8F5;
            background-image: radial-gradient(#D1D5DB 1.5px, transparent 1.5px);
            background-size: 20px 20px;
            background-attachment: fixed;
            font-family: var(--font-body);
            color: #1a1a1a;
            -webkit-text-size-adjust: 100%;
            touch-action: manipulation;
        }

        .font-display {
            font-family: var(--font-display);
            font-weight: 700;
            font-style: normal;
        }

        .font-body {
            font-family: var(--font-body);
        }

        input,
        textarea,
        select,
        button {
            font-family: var(--font-body);
        }

        .btn-bounce {
            transition: transform 0.1s ease, box-shadow 0.1s ease;
        }

        .btn-bounce:active {
            transform: translateY(3px) !important;
            box-shadow: 0 1px 0 #1A1A1A !important;
        }

        [x-cloak] {
            display: none !important;
        }

        .alpine-flex {
            display: flex;
        }

        .alpine-inline-flex {
            display: inline-flex;
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

            0%,
            100% {
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
            html,
            body {
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

<body
    style="margin: 0; padding: 0; width: 100%; height: 100%; height: 100vh; height: 100dvh; min-height: 100%; max-height: 100dvh; overflow: hidden; background-color: #FAF8F5; background-image: radial-gradient(#D1D5DB 1.5px, transparent 1.5px); background-size: 20px 20px; background-attachment: fixed;">
    @if(!request()->is('test-qr*'))
        <!-- Bandeau non bloquant de perte réseau / reconnexion -->
        <div id="offline-network-banner" role="alert" aria-live="polite"
            style="display: none; position: fixed; top: calc(env(safe-area-inset-top, 0px) + 8px); left: 12px; right: 12px; max-width: 456px; margin: 0 auto; z-index: 9500; background: #FEF3C7; border: 2.5px solid #1A1A1A; border-radius: 14px; padding: 10px 12px; box-shadow: 0 4px 0 #1A1A1A; font-family: var(--font-body); box-sizing: border-box;">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <div
                    style="width: 28px; height: 28px; border-radius: 8px; background: #FEE2E2; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#B91C1C" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path
                            d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <p id="offline-network-banner-title" class="font-display"
                        style="font-family: var(--font-display); margin: 0 0 2px 0; font-size: 15px; font-weight: 700; color: #841818; line-height: 1.1;">
                        Connexion réseau perdue
                    </p>
                    <p id="offline-network-banner-text"
                        style="margin: 0; font-size: 12px; font-weight: 700; color: #374151; line-height: 1.35;">
                        La boussole et le GPS continuent hors ligne. Le scan et la validation nécessitent Internet.
                    </p>
                    <button type="button" id="offline-network-reload-btn" onclick="window.location.reload()"
                        style="display: none; margin-top: 6px; padding: 4px 10px; background: #F4B316; color: #1A1A1A; border: 2px solid #1A1A1A; border-radius: 8px; font-size: 11px; font-weight: 800; cursor: pointer;">
                        Recharger la page
                    </button>
                </div>
                <button type="button" id="offline-network-dismiss-btn" aria-label="Masquer l'alerte réseau"
                    onclick="window.__expeditionDismissNetworkBanner && window.__expeditionDismissNetworkBanner()"
                    style="background: transparent; border: none; font-size: 16px; font-weight: 800; color: #1A1A1A; cursor: pointer; padding: 0 2px; line-height: 1; flex-shrink: 0;">
                    ✕
                </button>
            </div>
        </div>

        <div id="online-restored-banner" role="status" aria-live="polite"
            style="display: none; position: fixed; top: calc(env(safe-area-inset-top, 0px) + 8px); left: 12px; right: 12px; max-width: 456px; margin: 0 auto; z-index: 9500; background: #DCFCE7; border: 2.5px solid #1A1A1A; border-radius: 14px; padding: 8px 12px; box-shadow: 0 4px 0 #1A1A1A; font-family: var(--font-body); box-sizing: border-box;">
            <p class="font-display"
                style="font-family: var(--font-display); margin: 0; font-size: 15px; font-weight: 700; color: #15803D; text-align: center;">
                Connexion retrouvée !
            </p>
        </div>

        <style>
            @keyframes expeditionLoadingSpin {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }

            .expedition-loading-spinner {
                animation: expeditionLoadingSpin 0.9s linear infinite;
                transform-origin: center;
            }
        </style>

        <!-- Niveau 1 : Pastille compacte non bloquante en haut (onglets / synchros légères >= 350 ms) -->
        <div id="global-loading-pill" role="status" aria-live="polite" aria-hidden="true"
            style="display: none; position: fixed; top: calc(env(safe-area-inset-top, 0px) + 10px); left: 50%; transform: translateX(-50%); z-index: 9400; pointer-events: none; background: #FAF8F5; border: 2px solid #1A1A1A; border-radius: 9999px; padding: 6px 13px; box-shadow: 0 3px 0 #1A1A1A; font-family: var(--font-body); align-items: center; gap: 8px; max-width: calc(100vw - 24px); box-sizing: border-box;">
            <svg class="expedition-loading-spinner" viewBox="0 0 24 24" width="16" height="16" fill="none"
                aria-hidden="true" style="flex-shrink: 0;">
                <circle cx="12" cy="12" r="9" stroke="#E5E7EB" stroke-width="3" />
                <path d="M12 3a9 9 0 0 1 9 9" stroke="#F4B316" stroke-width="3" stroke-linecap="round" />
            </svg>
            <span id="global-loading-pill-text"
                style="font-size: 12px; font-weight: 800; color: #1A1A1A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                Synchronisation...
            </span>
        </div>

        <!-- Niveau 2 : Voile discret centré pour actions critiques lentes (>= 350 ms) -->
        <div id="global-loading-overlay" role="status" aria-live="polite" aria-busy="true" aria-hidden="true"
            style="display: none; position: fixed; inset: 0; z-index: 9600; background: rgba(26, 26, 26, 0.36); backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px); align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
            <div
                style="background: #FAF8F5; border: 3px solid #1A1A1A; border-radius: 18px; box-shadow: 0 5px 0 #1A1A1A; padding: 18px 20px; width: 100%; max-width: 300px; text-align: center; font-family: var(--font-body); box-sizing: border-box;">
                <div
                    style="width: 48px; height: 48px; margin: 0 auto 10px auto; border-radius: 50%; background: #FFFFFF; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center;">
                    <svg class="expedition-loading-spinner" viewBox="0 0 36 36" width="32" height="32" fill="none"
                        aria-hidden="true">
                        <circle cx="18" cy="18" r="13" stroke="#E5E7EB" stroke-width="4" />
                        <path d="M18 5a13 13 0 0 1 13 13" stroke="#F4B316" stroke-width="4" stroke-linecap="round" />
                    </svg>
                </div>
                <p id="global-loading-overlay-title" class="font-display"
                    style="font-family: var(--font-display); margin: 0; font-size: 19px; font-weight: 700; color: #841818; letter-spacing: 0.02em; line-height: 1.15;">
                    Connexion à l'expédition...
                </p>
                <p id="global-loading-overlay-subtitle"
                    style="display: none; margin: 6px 0 0 0; font-size: 12px; font-weight: 700; color: #374151; line-height: 1.35;">
                    (20 à 40 s après une pause)
                </p>
            </div>
        </div>
    @endif

    {{ $slot }}

    <script>
        window.generateExpeditionQrSvg = function (text, sizePx = 180) {
            try {
                const bytes = new TextEncoder().encode(text);
                const specs = [
                    { ver: 1, size: 21, dataCw: 19, ecCw: 7, align: [] },
                    { ver: 2, size: 25, dataCw: 34, ecCw: 10, align: [6, 18] },
                    { ver: 3, size: 29, dataCw: 55, ecCw: 15, align: [6, 22] },
                    { ver: 4, size: 33, dataCw: 80, ecCw: 20, align: [6, 26] },
                    { ver: 5, size: 37, dataCw: 108, ecCw: 26, align: [6, 30] },
                ];

                let spec = null;
                for (const s of specs) {
                    if (bytes.length + 2 <= s.dataCw) {
                        spec = s;
                        break;
                    }
                }
                if (!spec) return '';

                const gfExp = new Uint8Array(512);
                const gfLog = new Uint8Array(256);
                let x = 1;
                for (let i = 0; i < 255; i++) {
                    gfExp[i] = x;
                    gfLog[x] = i;
                    x <<= 1;
                    if (x & 0x100) x ^= 0x11d;
                }
                for (let i = 255; i < 512; i++) {
                    gfExp[i] = gfExp[i - 255];
                }
                const gfMul = (a, b) => (a === 0 || b === 0) ? 0 : gfExp[gfLog[a] + gfLog[b]];

                const bits = [];
                const pushBits = (val, len) => {
                    for (let i = len - 1; i >= 0; i--) {
                        bits.push((val >> i) & 1);
                    }
                };
                pushBits(0b0100, 4);
                pushBits(bytes.length, 8);
                for (const b of bytes) pushBits(b, 8);
                const maxBits = spec.dataCw * 8;
                const termLen = Math.min(4, maxBits - bits.length);
                pushBits(0, termLen);
                while (bits.length % 8 !== 0) bits.push(0);

                const dataBytes = [];
                for (let i = 0; i < bits.length; i += 8) {
                    let byte = 0;
                    for (let j = 0; j < 8; j++) byte = (byte << 1) | bits[i + j];
                    dataBytes.push(byte);
                }
                const pads = [0xec, 0x11];
                let padIdx = 0;
                while (dataBytes.length < spec.dataCw) {
                    dataBytes.push(pads[padIdx % 2]);
                    padIdx++;
                }

                let gen = [1];
                for (let i = 0; i < spec.ecCw; i++) {
                    const next = new Array(gen.length + 1).fill(0);
                    const factor = gfExp[i];
                    for (let j = 0; j < gen.length; j++) {
                        next[j] ^= gen[j];
                        next[j + 1] ^= gfMul(gen[j], factor);
                    }
                    gen = next;
                }

                const ecBytes = new Array(spec.ecCw).fill(0);
                for (const d of dataBytes) {
                    const lead = d ^ ecBytes[0];
                    for (let i = 0; i < spec.ecCw - 1; i++) {
                        ecBytes[i] = ecBytes[i + 1] ^ gfMul(gen[i + 1], lead);
                    }
                    ecBytes[spec.ecCw - 1] = gfMul(gen[spec.ecCw], lead);
                }

                const codewords = dataBytes.concat(ecBytes);
                const n = spec.size;

                const makeMatrix = () => {
                    const grid = Array.from({ length: n }, () => new Int8Array(n).fill(-1));
                    const reserved = Array.from({ length: n }, () => new Uint8Array(n));

                    const setMod = (r, c, dark, isFunc = true) => {
                        if (r < 0 || r >= n || c < 0 || c >= n) return;
                        grid[r][c] = dark ? 1 : 0;
                        if (isFunc) reserved[r][c] = 1;
                    };

                    const drawFinder = (top, left) => {
                        for (let r = -1; r <= 7; r++) {
                            for (let c = -1; c <= 7; c++) {
                                const rr = top + r;
                                const cc = left + c;
                                if (rr < 0 || rr >= n || cc < 0 || cc >= n) continue;
                                const inBorder = r >= 0 && r <= 6 && c >= 0 && c <= 6;
                                const dark = inBorder && (
                                    r === 0 || r === 6 || c === 0 || c === 6 ||
                                    (r >= 2 && r <= 4 && c >= 2 && c <= 4)
                                );
                                setMod(rr, cc, dark, true);
                            }
                        }
                    };

                    drawFinder(0, 0);
                    drawFinder(0, n - 7);
                    drawFinder(n - 7, 0);

                    for (let i = 8; i < n - 8; i++) {
                        setMod(6, i, i % 2 === 0, true);
                        setMod(i, 6, i % 2 === 0, true);
                    }

                    for (const ar of spec.align) {
                        for (const ac of spec.align) {
                            if (reserved[ar][ac]) continue;
                            for (let dr = -2; dr <= 2; dr++) {
                                for (let dc = -2; dc <= 2; dc++) {
                                    const dark = Math.abs(dr) === 2 || Math.abs(dc) === 2 || (dr === 0 && dc === 0);
                                    setMod(ar + dr, ac + dc, dark, true);
                                }
                            }
                        }
                    }

                    setMod(n - 8, 8, true, true);

                    for (let i = 0; i < 9; i++) {
                        if (i !== 6) {
                            reserved[8][i] = 1;
                            reserved[i][8] = 1;
                        }
                    }
                    for (let i = n - 8; i < n; i++) {
                        reserved[8][i] = 1;
                        reserved[i][8] = 1;
                    }

                    const allBits = [];
                    for (const cw of codewords) {
                        for (let b = 7; b >= 0; b--) allBits.push((cw >> b) & 1);
                    }
                    let bitIdx = 0;
                    let upward = true;
                    for (let right = n - 1; right >= 1; right -= 2) {
                        if (right === 6) right = 5;
                        for (let step = 0; step < n; step++) {
                            const r = upward ? (n - 1 - step) : step;
                            for (let dx = 0; dx < 2; dx++) {
                                const c = right - dx;
                                if (!reserved[r][c]) {
                                    grid[r][c] = bitIdx < allBits.length ? allBits[bitIdx++] : 0;
                                }
                            }
                        }
                        upward = !upward;
                    }

                    return { grid, reserved };
                };

                const maskFns = [
                    (r, c) => (r + c) % 2 === 0,
                    (r, c) => r % 2 === 0,
                    (r, c) => c % 3 === 0,
                    (r, c) => (r + c) % 3 === 0,
                    (r, c) => (Math.floor(r / 2) + Math.floor(c / 3)) % 2 === 0,
                    (r, c) => ((r * c) % 2 + (r * c) % 3) === 0,
                    (r, c) => (((r * c) % 2 + (r * c) % 3) % 2) === 0,
                    (r, c) => (((r + c) % 2 + (r * c) % 3) % 2) === 0,
                ];

                const computeFormatBits = (maskId) => {
                    const data = (0b01 << 3) | maskId;
                    let rem = data << 10;
                    const poly = 0b10100110111;
                    for (let i = 14; i >= 10; i--) {
                        if ((rem >> i) & 1) rem ^= poly << (i - 10);
                    }
                    return ((data << 10) | rem) ^ 0b101010000010010;
                };

                const applyFormat = (grid, maskId) => {
                    const fmt = computeFormatBits(maskId);
                    const getBit = (idx) => (fmt >> idx) & 1;
                    for (let i = 0; i <= 5; i++) grid[8][i] = getBit(14 - i);
                    grid[8][7] = getBit(8);
                    grid[8][8] = getBit(7);
                    grid[7][8] = getBit(6);
                    for (let i = 0; i <= 5; i++) grid[5 - i][8] = getBit(5 - i);

                    for (let i = 0; i < 8; i++) grid[8][n - 1 - i] = getBit(i);
                    for (let i = 0; i < 7; i++) grid[n - 7 + i][8] = getBit(8 + i);
                };

                const scoreGrid = (grid) => {
                    let penalty = 0;
                    for (let r = 0; r < n; r++) {
                        let runColor = grid[r][0], runLen = 1;
                        for (let c = 1; c < n; c++) {
                            if (grid[r][c] === runColor) {
                                runLen++;
                            } else {
                                if (runLen >= 5) penalty += 3 + (runLen - 5);
                                runColor = grid[r][c];
                                runLen = 1;
                            }
                        }
                        if (runLen >= 5) penalty += 3 + (runLen - 5);
                    }
                    for (let c = 0; c < n; c++) {
                        let runColor = grid[0][c], runLen = 1;
                        for (let r = 1; r < n; r++) {
                            if (grid[r][c] === runColor) {
                                runLen++;
                            } else {
                                if (runLen >= 5) penalty += 3 + (runLen - 5);
                                runColor = grid[r][c];
                                runLen = 1;
                            }
                        }
                        if (runLen >= 5) penalty += 3 + (runLen - 5);
                    }
                    for (let r = 0; r < n - 1; r++) {
                        for (let c = 0; c < n - 1; c++) {
                            const v = grid[r][c];
                            if (v === grid[r][c + 1] && v === grid[r + 1][c] && v === grid[r + 1][c + 1]) {
                                penalty += 3;
                            }
                        }
                    }
                    const p1 = [1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0];
                    const p2 = [0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1];
                    for (let r = 0; r < n; r++) {
                        for (let c = 0; c <= n - 11; c++) {
                            let m1 = true, m2 = true;
                            for (let k = 0; k < 11; k++) {
                                if (grid[r][c + k] !== p1[k]) m1 = false;
                                if (grid[r][c + k] !== p2[k]) m2 = false;
                            }
                            if (m1 || m2) penalty += 40;
                        }
                    }
                    for (let c = 0; c < n; c++) {
                        for (let r = 0; r <= n - 11; r++) {
                            let m1 = true, m2 = true;
                            for (let k = 0; k < 11; k++) {
                                if (grid[r + k][c] !== p1[k]) m1 = false;
                                if (grid[r + k][c] !== p2[k]) m2 = false;
                            }
                            if (m1 || m2) penalty += 40;
                        }
                    }
                    let darkCount = 0;
                    for (let r = 0; r < n; r++) {
                        for (let c = 0; c < n; c++) {
                            if (grid[r][c] === 1) darkCount++;
                        }
                    }
                    const darkPct = (darkCount * 100) / (n * n);
                    const prev5 = Math.floor(darkPct / 5) * 5;
                    const next5 = prev5 + 5;
                    penalty += Math.min(Math.abs(prev5 - 50), Math.abs(next5 - 50)) * 2;
                    return penalty;
                };

                let bestGrid = null;
                let bestScore = Infinity;
                const base = makeMatrix();

                for (let m = 0; m < 8; m++) {
                    const g = base.grid.map((row) => Int8Array.from(row));
                    for (let r = 0; r < n; r++) {
                        for (let c = 0; c < n; c++) {
                            if (!base.reserved[r][c] && maskFns[m](r, c)) {
                                g[r][c] ^= 1;
                            }
                        }
                    }
                    applyFormat(g, m);
                    const s = scoreGrid(g);
                    if (s < bestScore) {
                        bestScore = s;
                        bestGrid = g;
                    }
                }

                const quiet = 4;
                const total = n + quiet * 2;
                let paths = '';
                for (let r = 0; r < n; r++) {
                    for (let c = 0; c < n; c++) {
                        if (bestGrid[r][c] === 1) {
                            paths += `M${c + quiet},${r + quiet}h1v1h-1z`;
                        }
                    }
                }

                return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${total} ${total}" width="${sizePx}" height="${sizePx}" shape-rendering="crispEdges" role="img" aria-label="QR Code Retrait du Trésor"><rect width="${total}" height="${total}" fill="#FFFFFF"/><path d="${paths}" fill="#1A1A1A"/></svg>`;
            } catch (e) {
                console.warn('Erreur génération QR local:', e);
                return '';
            }
        };

        window.__expeditionPendingUnlockedStep = null;
        window.addEventListener('fragment-unlocked', function (event) {
            if (event && event.detail && event.detail.stepId) {
                window.__expeditionPendingUnlockedStep = event.detail.stepId;
            }
        });

        window.expeditionVibrate = function (pattern) {
            try {
                if (typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function') {
                    return navigator.vibrate(pattern);
                }
            } catch (e) { }
            return false;
        };
    </script>

    @if(!request()->is('test-qr*'))
        <script>
            (function initExpeditionMonitors() {
                if (window.__expeditionMonitorsInitialized) {
                    return;
                }
                window.__expeditionMonitorsInitialized = true;

                const getBannerEls = () => ({
                    offline: document.getElementById('offline-network-banner'),
                    title: document.getElementById('offline-network-banner-title'),
                    text: document.getElementById('offline-network-banner-text'),
                    reloadBtn: document.getElementById('offline-network-reload-btn'),
                    restored: document.getElementById('online-restored-banner'),
                });

                let restoredTimer = null;
                let wasOfflineOrErrored = false;

                const showNetworkBanner = (titleText, bodyText, showReload = false) => {
                    const els = getBannerEls();
                    if (!els.offline) return;
                    if (els.title && titleText) els.title.textContent = titleText;
                    if (els.text && bodyText) els.text.textContent = bodyText;
                    if (els.reloadBtn) els.reloadBtn.style.display = showReload ? 'inline-block' : 'none';
                    if (els.restored) els.restored.style.display = 'none';
                    els.offline.style.display = 'block';
                    wasOfflineOrErrored = true;
                };

                const hideNetworkBanner = (flashRestored = false) => {
                    const els = getBannerEls();
                    if (els.offline) els.offline.style.display = 'none';
                    if (flashRestored && wasOfflineOrErrored && els.restored) {
                        els.restored.style.display = 'block';
                        if (restoredTimer) clearTimeout(restoredTimer);
                        restoredTimer = setTimeout(() => {
                            if (els.restored) els.restored.style.display = 'none';
                        }, 2600);
                    }
                    wasOfflineOrErrored = false;
                };

                window.__expeditionDismissNetworkBanner = () => hideNetworkBanner(false);
                window.__expeditionShowNetworkBanner = showNetworkBanner;

                if (typeof navigator !== 'undefined' && navigator.onLine === false) {
                    showNetworkBanner(
                        'Connexion réseau perdue',
                        'La boussole et le GPS continuent hors ligne. Le scan et la validation nécessitent Internet.',
                        false
                    );
                }

                window.addEventListener('offline', () => {
                    showNetworkBanner(
                        'Connexion réseau perdue',
                        'La boussole et le GPS continuent hors ligne. Le scan et la validation nécessitent Internet.',
                        false
                    );
                });

                window.addEventListener('online', () => {
                    hideNetworkBanner(true);
                    window.dispatchEvent(new CustomEvent('expedition-network-restored'));
                    if (typeof window.__expeditionTriggerKeepAlive === 'function') {
                        window.__expeditionTriggerKeepAlive();
                    }
                });

                // --- Indicateur de chargement à 2 niveaux (seuil 350 ms, palier réveil 2.5 s) ---
                const SHOW_THRESHOLD_MS = 350;
                const SLOW_THRESHOLD_MS = 2500;
                const SAFETY_TIMEOUT_MS = 45000;
                const CRITICAL_ACTIONS = new Set([
                    'login',
                    'register',
                    'logout',
                    'verifyCode',
                    'verifyManualCode',
                    'debugSetProgress',
                    'debugResetProgression',
                ]);

                let activeLightCount = 0;
                let activeCriticalCount = 0;
                let showTimer = null;
                let slowTimer = null;
                let thresholdReached = false;
                let isSlowMessage = false;
                let navSettle = null;

                const getLoadingEls = () => ({
                    pill: document.getElementById('global-loading-pill'),
                    pillText: document.getElementById('global-loading-pill-text'),
                    overlay: document.getElementById('global-loading-overlay'),
                    overlayTitle: document.getElementById('global-loading-overlay-title'),
                    overlaySubtitle: document.getElementById('global-loading-overlay-subtitle'),
                });

                const renderLoadingUi = () => {
                    const els = getLoadingEls();
                    const totalActive = activeLightCount + activeCriticalCount;

                    if (totalActive <= 0 || !thresholdReached) {
                        if (els.pill) {
                            els.pill.style.display = 'none';
                            els.pill.setAttribute('aria-hidden', 'true');
                        }
                        if (els.overlay) {
                            els.overlay.style.display = 'none';
                            els.overlay.setAttribute('aria-hidden', 'true');
                        }
                        return;
                    }

                    if (activeCriticalCount > 0) {
                        if (els.pill) {
                            els.pill.style.display = 'none';
                            els.pill.setAttribute('aria-hidden', 'true');
                        }
                        if (els.overlay) {
                            if (els.overlayTitle) {
                                els.overlayTitle.textContent = isSlowMessage
                                    ? 'Réveil du serveur en cours...'
                                    : "Connexion à l'expédition...";
                            }
                            if (els.overlaySubtitle) {
                                els.overlaySubtitle.style.display = isSlowMessage ? 'block' : 'none';
                            }
                            els.overlay.style.display = 'flex';
                            els.overlay.setAttribute('aria-hidden', 'false');
                        }
                    } else {
                        if (els.overlay) {
                            els.overlay.style.display = 'none';
                            els.overlay.setAttribute('aria-hidden', 'true');
                        }
                        if (els.pill) {
                            if (els.pillText) {
                                els.pillText.textContent = isSlowMessage
                                    ? 'Réveil du serveur en cours...'
                                    : 'Synchronisation...';
                            }
                            els.pill.style.display = 'flex';
                            els.pill.setAttribute('aria-hidden', 'false');
                        }
                    }
                };

                const resetLoadingTimers = () => {
                    if (showTimer) {
                        clearTimeout(showTimer);
                        showTimer = null;
                    }
                    if (slowTimer) {
                        clearTimeout(slowTimer);
                        slowTimer = null;
                    }
                };

                const beginLoading = (tier) => {
                    const isCritical = tier === 'critical';
                    const wasIdle = (activeLightCount + activeCriticalCount) === 0;

                    if (isCritical) {
                        activeCriticalCount++;
                    } else {
                        activeLightCount++;
                    }

                    if (wasIdle) {
                        thresholdReached = false;
                        isSlowMessage = false;
                        resetLoadingTimers();

                        showTimer = setTimeout(() => {
                            showTimer = null;
                            if ((activeLightCount + activeCriticalCount) > 0) {
                                thresholdReached = true;
                                renderLoadingUi();
                            }
                        }, SHOW_THRESHOLD_MS);

                        slowTimer = setTimeout(() => {
                            slowTimer = null;
                            if ((activeLightCount + activeCriticalCount) > 0) {
                                isSlowMessage = true;
                                thresholdReached = true;
                                renderLoadingUi();
                            }
                        }, SLOW_THRESHOLD_MS);
                    } else if (thresholdReached) {
                        renderLoadingUi();
                    }

                    let settled = false;
                    let safetyTimer = setTimeout(() => {
                        settle();
                    }, SAFETY_TIMEOUT_MS);

                    const settle = () => {
                        if (settled) {
                            return;
                        }
                        settled = true;

                        if (safetyTimer) {
                            clearTimeout(safetyTimer);
                            safetyTimer = null;
                        }

                        if (isCritical) {
                            activeCriticalCount = Math.max(0, activeCriticalCount - 1);
                        } else {
                            activeLightCount = Math.max(0, activeLightCount - 1);
                        }

                        if ((activeLightCount + activeCriticalCount) === 0) {
                            resetLoadingTimers();
                            thresholdReached = false;
                            isSlowMessage = false;
                        }

                        renderLoadingUi();
                    };

                    return settle;
                };

                const isCriticalLivewireRequest = (request) => {
                    if (!request || !request.messages) {
                        return false;
                    }

                    for (const message of request.messages) {
                        if (message && message.actions) {
                            for (const action of message.actions) {
                                if (action && CRITICAL_ACTIONS.has(action.name)) {
                                    return true;
                                }
                            }
                        }
                        if (message && Array.isArray(message.calls)) {
                            for (const call of message.calls) {
                                if (call && CRITICAL_ACTIONS.has(call.method)) {
                                    return true;
                                }
                            }
                        }
                    }

                    return false;
                };

                const startNavigationLoading = () => {
                    if (!navSettle) {
                        navSettle = beginLoading('critical');
                    }
                };

                const stopNavigationLoading = () => {
                    if (navSettle) {
                        const settleFn = navSettle;
                        navSettle = null;
                        settleFn();
                    }
                };

                document.addEventListener('livewire:navigate', (event) => {
                    if (!event.defaultPrevented) {
                        startNavigationLoading();
                    }
                    queueMicrotask(() => {
                        if (event.defaultPrevented) {
                            stopNavigationLoading();
                        }
                    });
                });

                document.addEventListener('livewire:navigating', () => {
                    startNavigationLoading();
                });

                document.addEventListener('livewire:navigated', () => {
                    stopNavigationLoading();
                    renderLoadingUi();
                });

                if (typeof window.fetch === 'function' && !window.__expeditionFetchWrapped) {
                    window.__expeditionFetchWrapped = true;
                    const originalFetch = window.fetch.bind(window);
                    window.fetch = function (input, init) {
                        const headers = init && init.headers;
                        const isLivewireNavigate = Boolean(
                            headers && (
                                headers['X-Livewire-Navigate'] === '1' ||
                                (typeof Headers !== 'undefined' && headers instanceof Headers && headers.get('X-Livewire-Navigate') === '1')
                            )
                        );
                        const fetchPromise = originalFetch(input, init);
                        if (isLivewireNavigate) {
                            fetchPromise.catch((err) => {
                                stopNavigationLoading();
                                if ((!err || err.name !== 'AbortError') && (typeof navigator === 'undefined' || navigator.onLine !== false)) {
                                    showNetworkBanner(
                                        'Connexion interrompue',
                                        'Impossible de charger la page. Vérifiez votre réseau puis réessayez.',
                                        true
                                    );
                                }
                            });
                        }
                        return fetchPromise;
                    };
                }

                let interceptorRegistered = false;
                const registerLivewireRequestInterceptor = () => {
                    if (interceptorRegistered || !window.Livewire || typeof window.Livewire.interceptRequest !== 'function') {
                        return;
                    }
                    interceptorRegistered = true;

                    window.Livewire.interceptRequest(({ request, onSend, onCancel, onFailure, onError, onSuccess, onFinish, onRedirect }) => {
                        let activeSettle = null;

                        const ensureSettled = () => {
                            if (activeSettle) {
                                activeSettle();
                            }
                        };

                        if (typeof onSend === 'function') {
                            onSend(({ responsePromise } = {}) => {
                                const tier = isCriticalLivewireRequest(request) ? 'critical' : 'light';
                                activeSettle = beginLoading(tier);

                                if (responsePromise && typeof responsePromise.finally === 'function') {
                                    responsePromise.finally(ensureSettled);
                                }
                            });
                        }

                        if (typeof onCancel === 'function') {
                            onCancel(() => {
                                ensureSettled();
                            });
                        }

                        onFailure(({ error }) => {
                            ensureSettled();
                            const isOffline = typeof navigator !== 'undefined' && navigator.onLine === false;
                            const msg = isOffline
                                ? 'Vous êtes hors ligne : la boussole reste active, mais cette action nécessite Internet.'
                                : 'Réseau instable ou serveur injoignable. Rapprochez-vous d\'une zone couverte et réessayez.';
                            showNetworkBanner('Connexion interrompue', msg, false);
                            window.dispatchEvent(new CustomEvent('expedition-network-error', {
                                detail: { type: isOffline ? 'offline' : 'network', message: msg, error }
                            }));
                        });

                        onError(({ response, preventDefault }) => {
                            ensureSettled();
                            const status = response && typeof response.status === 'number' ? response.status : 0;
                            if (status === 419 || status >= 500 || status === 0 || (typeof navigator !== 'undefined' && navigator.onLine === false)) {
                                if (typeof preventDefault === 'function') {
                                    preventDefault();
                                }
                                const isExpired = status === 419;
                                const msg = isExpired
                                    ? 'Votre session a expiré. Rechargez la page pour retrouver votre expédition en toute sécurité.'
                                    : 'Impossible de contacter le serveur pour le moment. Vérifiez votre réseau puis réessayez.';
                                showNetworkBanner(
                                    isExpired ? 'Session à rafraîchir' : 'Problème de connexion',
                                    msg,
                                    isExpired
                                );
                                window.dispatchEvent(new CustomEvent('expedition-network-error', {
                                    detail: { type: isExpired ? 'expired' : 'server', status, message: msg }
                                }));
                            }
                        });

                        onSuccess(() => {
                            ensureSettled();
                            if (typeof navigator === 'undefined' || navigator.onLine !== false) {
                                hideNetworkBanner(false);
                            }
                        });

                        if (typeof onRedirect === 'function') {
                            onRedirect(() => {
                                ensureSettled();
                            });
                        }

                        if (typeof onFinish === 'function') {
                            onFinish(() => {
                                ensureSettled();
                            });
                        }
                    });
                };

                registerLivewireRequestInterceptor();
                document.addEventListener('livewire:init', registerLivewireRequestInterceptor);

                // --- Heartbeat Keep-Alive silencieux (Render + Neon + Session/CSRF) ---
                const KEEP_ALIVE_INTERVAL_MS = 210000;
                const KEEP_ALIVE_VISIBILITY_DEBOUNCE_MS = 60000;
                let lastKeepAliveAt = Date.now();
                let isKeepAliveInFlight = false;

                const refreshCsrfToken = (nextToken) => {
                    if (!nextToken || typeof nextToken !== 'string') {
                        return;
                    }

                    document.querySelectorAll('[data-csrf]').forEach((el) => {
                        el.setAttribute('data-csrf', nextToken);
                    });

                    if (window.livewireScriptConfig && typeof window.livewireScriptConfig === 'object') {
                        window.livewireScriptConfig.csrf = nextToken;
                    }

                    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
                    if (metaCsrf) {
                        metaCsrf.setAttribute('content', nextToken);
                    }
                };

                const sendKeepAlive = () => {
                    if (isKeepAliveInFlight) {
                        return;
                    }
                    if (typeof document !== 'undefined' && document.visibilityState !== 'visible') {
                        return;
                    }
                    if (typeof navigator !== 'undefined' && navigator.onLine === false) {
                        return;
                    }

                    isKeepAliveInFlight = true;

                    fetch('/keep-alive', {
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then((response) => {
                            if (response && response.ok) {
                                lastKeepAliveAt = Date.now();
                                return response.json();
                            }
                            return null;
                        })
                        .then((data) => {
                            if (data && data.csrf) {
                                refreshCsrfToken(data.csrf);
                            }
                        })
                        .catch(() => { })
                        .finally(() => {
                            isKeepAliveInFlight = false;
                        });
                };

                window.__expeditionTriggerKeepAlive = () => sendKeepAlive();

                setInterval(() => {
                    sendKeepAlive();
                }, KEEP_ALIVE_INTERVAL_MS);

                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible' && (Date.now() - lastKeepAliveAt >= KEEP_ALIVE_VISIBILITY_DEBOUNCE_MS)) {
                        sendKeepAlive();
                    }
                });
            })();
        </script>
    @endif

    @livewireScripts
</body>

</html>