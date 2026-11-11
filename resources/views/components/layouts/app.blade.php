<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'Expédition Lens' }}</title>

    <!-- PWA Web App Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#000000">

    <!-- iOS / Safari PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Expédition Lens">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/icon.svg">

    <link rel="preconnect" href="https://use.typekit.net" crossorigin>
    <link rel="preconnect" href="https://p.typekit.net" crossorigin>
    <link rel="stylesheet" href="https://use.typekit.net/msd2zjt.css">

    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --font-display: "hwt-artz", "Impact", sans-serif;
            --font-body: "nunito", system-ui, -apple-system, sans-serif;
        }
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
        input, textarea, select, button {
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

                return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${total} ${total}" width="${sizePx}" height="${sizePx}" shape-rendering="crispEdges" role="img" aria-label="QR Code Retrait du Lot Dobble"><rect width="${total}" height="${total}" fill="#FFFFFF"/><path d="${paths}" fill="#1A1A1A"/></svg>`;
            } catch (e) {
                console.warn('Erreur génération QR local:', e);
                return '';
            }
        };
    </script>

    @livewireScripts
</body>
</html>
