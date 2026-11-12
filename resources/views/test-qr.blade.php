<x-layouts.app :title="'Mire de Test QR Codes - Expédition Lens'">
    <style>
        :root {
            --bg-page: #0b1120;
            --bg-card: #1e293b;
            --bg-card-sub: #0f172a;
            --border-color: #334155;
            --gold-primary: #f59e0b;
            --gold-secondary: #d97706;
            --gold-light: #fef3c7;
            --emerald-primary: #10b981;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 16px 80px 16px;
        }

        header.test-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid var(--border-color);
            border-top: 4px solid var(--gold-primary);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }

        .badge-dev {
            display: inline-block;
            background-color: rgba(245, 158, 11, 0.15);
            color: var(--gold-primary);
            border: 1px solid rgba(245, 158, 11, 0.35);
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        h1 {
            margin: 0 0 10px 0;
            font-size: 26px;
            color: #ffffff;
            font-weight: 800;
        }

        .instructions {
            color: var(--text-muted);
            font-size: 15px;
            margin: 0 0 16px 0;
            line-height: 1.6;
        }

        .instructions strong {
            color: #f1f5f9;
        }

        .top-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border-color);
        }

        .btn-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #334155;
            color: #ffffff;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-link:hover {
            background-color: #475569;
            color: var(--gold-primary);
        }

        .btn-link-primary {
            background-color: var(--gold-primary);
            color: #0f172a;
            font-weight: 700;
        }

        .btn-link-primary:hover {
            background-color: #fbbf24;
            color: #020617;
        }

        .step-jump-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-left: auto;
        }

        .step-pill {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.15s ease;
        }

        .step-pill:hover {
            background: var(--gold-secondary);
            border-color: var(--gold-secondary);
            color: #ffffff;
        }

        .steps-grid {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .step-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
            scroll-margin-top: 20px;
        }

        .step-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
            margin-bottom: 16px;
        }

        .step-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .step-number-badge {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            color: #0f172a;
            font-weight: 800;
            font-size: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .step-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
        }

        .step-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .badge-info {
            background-color: #0f172a;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .badge-fragment {
            background-color: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #34d399;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .riddle-box {
            background-color: var(--bg-card-sub);
            border-left: 4px solid var(--gold-primary);
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .riddle-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--gold-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .riddle-text {
            font-size: 14px;
            color: #e2e8f0;
            font-style: italic;
            margin: 0;
        }

        .qr-pair-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .qr-card {
            background-color: var(--bg-card-sub);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }

        .qr-card:hover {
            border-color: var(--gold-secondary);
            transform: translateY(-2px);
        }

        .qr-tag {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 3px 10px;
            border-radius: 9999px;
            margin-bottom: 12px;
        }

        .tag-map {
            background-color: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #60a5fa;
        }

        .tag-mj {
            background-color: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.35);
            color: #fbbf24;
        }

        .qr-frame {
            background-color: #ffffff;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            margin-bottom: 12px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .qr-frame img {
            display: block;
            width: 180px;
            height: 180px;
            image-rendering: pixelated;
        }

        .qr-code-value {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 15px;
            font-weight: 800;
            background: #1e293b;
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            margin-bottom: 8px;
            letter-spacing: 0.04em;
        }

        .qr-desc {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.4;
        }

        .qr-expected {
            margin-top: 8px;
            font-size: 12px;
            color: #34d399;
            font-weight: 600;
        }

        .summary-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-top: 32px;
        }

        .summary-card h2 {
            margin: 0 0 16px 0;
            font-size: 18px;
            color: #ffffff;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        table.summary-table th,
        table.summary-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        table.summary-table th {
            background-color: #0f172a;
            color: var(--gold-primary);
            font-weight: 700;
        }

        table.summary-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .code-pill {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            background: #0f172a;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            border: 1px solid #334155;
        }

        .final-secret-box {
            margin-top: 20px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(16, 185, 129, 0.1));
            border: 1px solid rgba(245, 158, 11, 0.4);
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }

        .final-secret-box strong {
            color: var(--gold-primary);
            font-size: 18px;
        }

        @media (max-width: 640px) {
            .step-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .step-jump-nav {
                margin-left: 0;
            }
        }
    </style>

    <div class="container">
        <header class="test-header">
            <span class="badge-dev">Outil Développeur &amp; Banc de Test Mobile</span>
            <h1>Mire de Test des QR Codes — Expédition Lens</h1>
            <p class="instructions">
                Affichez cette page en plein écran sur votre ordinateur. Ouvrez ensuite l'application
                <strong>Expédition Lens</strong> sur votre smartphone (connecté au même réseau local ou via le tunnel HTTPS).
                Depuis l'onglet <strong>[Scanner]</strong> du smartphone, pointez l'appareil photo vers les QR codes ci-dessous
                pour tester et valider en conditions réelles chacune des 6 étapes sans impression papier !
                <br>
                <strong>Parcours rapide :</strong> scannez <code>MAP_LOC_X</code>, ouvrez <code>[•] debug</code> pour cliquer sur
                <strong>TP sur le spot (0m)</strong>, confirmez <strong>Vous êtes arrivé ?</strong>, puis touchez
                <strong>Épreuve terminée ?</strong> pour ouvrir le scanner et flasher <code>MJ_STEP_X</code>.
            </p>

            <nav class="top-nav" aria-label="Navigation mire de test">
                <a href="{{ route('home') }}" class="btn-link btn-link-primary">
                    ← Retour à l'application
                </a>

                <div class="step-jump-nav">
                    @foreach($steps as $step)
                        <a href="#step-{{ $step->id }}" class="step-pill">Étape {{ $step->id }}</a>
                    @endforeach
                    <a href="#summary-table" class="step-pill">Récapitulatif</a>
                </div>
            </nav>
        </header>

        <main class="steps-grid">
            @forelse($steps as $step)
                <article class="step-card" id="step-{{ $step->id }}">
                    <header class="step-header">
                        <div class="step-title-wrap">
                            <span class="step-number-badge">{{ $step->id }}</span>
                            <h2 class="step-title">{{ $step->name }}</h2>
                        </div>
                        <div class="step-badges">
                            <span class="badge-info">GPS: {{ $step->lat }}, {{ $step->lng }} (rayon {{ $step->target_radius ?? 30 }} m)</span>
                            <span class="badge-fragment">Fragment attendu : {{ $step->code_part }}</span>
                        </div>
                    </header>

                    <section class="riddle-box" aria-label="Énigme">
                        <div class="riddle-label">Énigme Cartographique</div>
                        <p class="riddle-text">« {{ $step->riddle_text }} »</p>
                    </section>

                    <div class="qr-pair-grid">
                        <!-- QR Code Phase A : Carte -->
                        <div class="qr-card">
                            <span class="qr-tag tag-map">Phase A : QR Code Carte</span>
                            <div class="qr-frame">
                                <img
                                    src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&amp;margin=10&amp;data={{ urlencode($step->qr_map_code) }}"
                                    alt="QR Code Carte {{ $step->qr_map_code }}"
                                    width="180"
                                    height="180"
                                    loading="lazy"
                                >
                            </div>
                            <div class="qr-code-value">{{ $step->qr_map_code }}</div>
                            <p class="qr-desc">
                                À scanner quand l'équipe est en phase <strong>Recherche de l'emplacement</strong>. Valide l'analyse de la carte et active la marche GPS vers le lieu réel.
                            </p>
                            <div class="qr-expected">→ Passe l'équipe en phase WALKING_TO_TARGET</div>
                        </div>

                        <!-- QR Code Phase B : Maître du Jeu / Défi -->
                        <div class="qr-card">
                            <span class="qr-tag tag-mj">Phase B : QR Code Maître du Jeu</span>
                            <div class="qr-frame">
                                <img
                                    src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&amp;margin=10&amp;data={{ urlencode($step->qr_mj_code) }}"
                                    alt="QR Code MJ {{ $step->qr_mj_code }}"
                                    width="180"
                                    height="180"
                                    loading="lazy"
                                >
                            </div>
                            <div class="qr-code-value">{{ $step->qr_mj_code }}</div>
                            <p class="qr-desc">
                                À scanner auprès de l'animateur sur place après réalisation du défi physique. Délivre le fragment de l'étape et fait progresser l'aventure.
                            </p>
                            <div class="qr-expected">→ Débloque : <strong>{{ $step->code_part }}</strong></div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="step-card">
                    <p>Aucune étape trouvée dans la base de données. Veuillez exécuter <code>php artisan db:seed</code>.</p>
                </div>
            @endforelse

            <section class="summary-card" id="summary-table">
                <h2>Récapitulatif des codes &amp; Fragments du parcours</h2>
                <div class="table-responsive">
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom de l'étape</th>
                                <th>Énigme</th>
                                <th>QR Map (Phase A)</th>
                                <th>QR MJ (Phase B)</th>
                                <th>Fragment / Code Part</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($steps as $step)
                                <tr>
                                    <td><strong>{{ $step->id }}</strong></td>
                                    <td>{{ $step->name }}</td>
                                    <td><small>{{ $step->riddle_text }}</small></td>
                                    <td><span class="code-pill">{{ $step->qr_map_code }}</span></td>
                                    <td><span class="code-pill">{{ $step->qr_mj_code }}</span></td>
                                    <td><strong>{{ $step->code_part }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="final-secret-box">
                    Clé Complète (6 fragments de clé : Étapes 1 à 6) + Ouverture du Coffre du Trésor Secret à l'<strong>Office de Tourisme</strong> + Participation automatique au Grand Tirage au Sort !
                </div>
            </section>
        </main>
    </div>
</x-layouts.app>
