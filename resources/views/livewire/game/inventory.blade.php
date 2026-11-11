<div x-data="{
        selectedMemory: null,
        localQrSvg: '',
        openMemory(stepId) {
            this.selectedMemory = stepId;
        },
        closeMemory() {
            this.selectedMemory = null;
        },
        initInventoryQr() {
            const payloadEl = document.getElementById('reward-qr-payload');
            if (!payloadEl) return;
            const text = payloadEl.getAttribute('data-qr-payload') || '';
            if (text && typeof window.generateExpeditionQrSvg === 'function') {
                this.localQrSvg = window.generateExpeditionQrSvg(text, 180);
            }
        }
    }"
    x-init="$nextTick(() => initInventoryQr())"
    @tab-switched.window="if ($event.detail && $event.detail.tab === 'inventory') { $nextTick(() => initInventoryQr()); }"
    @keydown.escape.window="closeMemory()"
    style="max-width: 480px; margin: 0 auto; padding: 10px 16px 24px 16px; font-family: var(--font-body); color: #1a1a1a;">

    <!-- Bouton Retour à la boussole en haut à gauche -->
    <div style="margin-bottom: 12px;">
        <button type="button" id="inventory-back-to-hub-btn" x-on:click="tab = 'hub'"
            wire:click="$dispatch('set-tab', { tab: 'hub' })" class="btn-bounce"
            style="background: #FFFFFF; border: 2.5px solid #1A1A1A; border-radius: 12px; padding: 8px 14px; font-size: 13px; font-weight: 800; color: #1A1A1A; box-shadow: 0 3px 0 #1A1A1A; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            ← Retour à la boussole
        </button>
    </div>

    @php
        $puzzlePieces = [
            1 => ['tabRight' => true,  'tabBottom' => true,  'notchLeft' => false, 'notchTop' => false, 'cipher' => '4', 'label' => 'Gare'],
            2 => ['tabRight' => true,  'tabBottom' => true,  'notchLeft' => true,  'notchTop' => false, 'cipher' => '8', 'label' => 'Corons'],
            3 => ['tabRight' => false, 'tabBottom' => true,  'notchLeft' => true,  'notchTop' => false, 'cipher' => '1', 'label' => 'Stade'],
            4 => ['tabRight' => true,  'tabBottom' => false, 'notchLeft' => false, 'notchTop' => true,  'cipher' => '9', 'label' => 'Musée'],
            5 => ['tabRight' => true,  'tabBottom' => false, 'notchLeft' => true,  'notchTop' => true,  'cipher' => 'Office de Tourisme', 'label' => 'Terrils'],
            6 => ['tabRight' => false, 'tabBottom' => false, 'notchLeft' => true,  'notchTop' => true,  'cipher' => 'Coffre', 'label' => 'Trésor'],
        ];

        $buildPuzzlePath = static function (array $meta): string {
            $top = $meta['notchTop'] ? 'H 37 C 37 15 51 15 51 8 H 80' : 'H 80';
            $right = $meta['tabRight'] ? 'V 25 C 87 25 87 39 80 39 V 56' : 'V 56';
            $bottom = $meta['tabBottom'] ? 'H 51 C 51 63 37 63 37 56 H 8' : 'H 8';
            $left = $meta['notchLeft'] ? 'V 39 C 15 39 15 25 8 25 V 8' : 'V 8';

            return "M 8 8 {$top} {$right} {$bottom} {$left} Z";
        };

        $resolvePieceLabel = static function (int $stepNumber, mixed $unlockedFragment) use ($puzzlePieces): string {
            $fallback = $puzzlePieces[$stepNumber]['cipher'];
            $raw = trim((string) ($unlockedFragment?->step?->code_part ?? ''));

            if ($raw === '') {
                return $fallback;
            }

            if ($stepNumber === 5) {
                return mb_strlen($raw) <= 24 ? $raw : $fallback;
            }

            if ($stepNumber === 6) {
                return mb_strlen($raw) <= 8 ? $raw : $fallback;
            }

            return mb_strlen($raw) <= 10 ? $raw : $fallback;
        };
    @endphp

    @if($hasWon)
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 20px 18px; text-align: center;">
            <div
                style="display: inline-block; background: #fef3c7; border: 2px solid #1a1a1a; border-radius: 9999px; padding: 5px 14px; font-size: 12px; font-weight: 800; color: #841818; margin-bottom: 10px; text-transform: uppercase;">
                Triomphe de l'Expédition
            </div>

            <h2 class="font-display" style="font-family: var(--font-display); font-size: 30px; font-weight: 700; color: #841818; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.03em;">
                Victoire !
            </h2>

            <p style="font-size: 14px; margin: 0 0 6px 0; color: #374151;">
                Félicitations à l'équipe <strong>{{ $user?->team_name ?? $user?->name ?? 'Équipe' }}</strong> !
            </p>
            <p style="font-size: 13px; margin: 0 0 16px 0; color: #4b5563; line-height: 1.4;">
                Vous avez triomphé de toutes les étapes, aidé Gali à reconstituer sa carte et fait briller à nouveau la Lampe Cœur !
            </p>

            <!-- Plateau Puzzle 6/6 complété (cliquable pour relire chaque souvenir) -->
            <div id="gali-treasure-map-puzzle"
                style="background: #F4EBD9; border: 2px solid #1A1A1A; border-radius: 16px; padding: 12px; margin-bottom: 18px; text-align: left;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; gap: 6px; flex-wrap: wrap;">
                    <span class="font-display" style="font-family: var(--font-display); font-size: 16px; font-weight: 700; color: #841818;">
                        Carte au Trésor de Gali (6/6)
                    </span>
                    <span style="font-size: 11px; font-weight: 700; color: #78350F;">
                        Touchez un morceau
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 5px; background: #E7DEC8; border: 1.5px dashed rgba(180, 83, 9, 0.55); border-radius: 12px; padding: 6px;">
                    @for($i = 1; $i <= 6; $i++)
                        @php
                            $unlocked = $fragments->firstWhere('step_id', $i);
                            $pieceMeta = $puzzlePieces[$i];
                            $piecePath = $buildPuzzlePath($pieceMeta);
                            $pieceLabel = $resolvePieceLabel($i, $unlocked);
                            $assetPng = file_exists(public_path("images/puzzle/piece-{$i}.png"));
                            $assetSvg = file_exists(public_path("images/puzzle/piece-{$i}.svg"));
                        @endphp
                        <button type="button"
                            id="puzzle-piece-{{ $i }}"
                            x-on:click="openMemory({{ $i }})"
                            style="position: relative; background: #FFFBEB; border: 1.5px solid #B45309; border-radius: 10px; padding: 6px 4px; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: space-between; min-height: 90px;">
                            {{-- PLACEHOLDER PUZZLE PIECE {{ $i }} : déposez votre asset dans public/images/puzzle/piece-{{ $i }}.png (ou .svg) pour remplacer ce SVG --}}
                            @if($assetSvg || $assetPng)
                                <img src="/images/puzzle/piece-{{ $i }}.{{ $assetSvg ? 'svg' : 'png' }}"
                                    alt="Morceau de carte {{ $i }}"
                                    style="width: 100%; height: 52px; object-fit: contain; display: block;">
                            @else
                                <svg viewBox="0 0 88 64" width="72" height="52" aria-hidden="true" style="display: block;">
                                    <path d="{{ $piecePath }}" fill="#FEF3C7" stroke="#1A1A1A" stroke-width="2" stroke-linejoin="round" />
                                    @if($i === 1)
                                        <path d="M 18 32 H 80" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                        <path d="M 44 42 V 56" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                        <path d="M18 38 H66 M24 38 V26 H50 L58 31 V38" fill="none" stroke="#841818" stroke-width="2.2" stroke-linecap="round"/>
                                        <circle cx="30" cy="40" r="3" fill="#1A1A1A"/>
                                        <circle cx="48" cy="40" r="3" fill="#1A1A1A"/>
                                    @elseif($i === 2)
                                        <path d="M 15 32 H 80 M 44 36 V 56" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                        <path d="M20 40 V24 L32 16 L44 24 V40 Z" fill="#FDE68A" stroke="#1A1A1A" stroke-width="1.8"/>
                                        <circle cx="57" cy="27" r="7" fill="#FFFFFF" stroke="#841818" stroke-width="2"/>
                                        <line x1="62" y1="32" x2="69" y2="39" stroke="#1A1A1A" stroke-width="2.4" stroke-linecap="round"/>
                                    @elseif($i === 3)
                                        <path d="M 15 32 H 44 V 56" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                        <rect x="20" y="21" width="48" height="20" rx="6" fill="#841818" stroke="#1A1A1A" stroke-width="1.8"/>
                                        <rect x="28" y="25" width="32" height="12" rx="3" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.4"/>
                                    @elseif($i === 4)
                                        <path d="M 44 15 V 32 H 80" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                        <polygon points="18,40 30,22 66,22 70,40" fill="#E0F2FE" stroke="#1A1A1A" stroke-width="1.8"/>
                                        <circle cx="28" cy="39" r="5" fill="#9FB8AD" stroke="#1A1A1A" stroke-width="1.5"/>
                                    @elseif($i === 5)
                                        <path d="M 15 32 H 80 M 44 15 V 32" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                        <polygon points="16,42 30,20 44,42" fill="#374151" stroke="#1A1A1A" stroke-width="1.7"/>
                                        <polygon points="34,42 48,18 62,42" fill="#1F2937" stroke="#1A1A1A" stroke-width="1.7"/>
                                        <path d="M 65 23 C 62 23 60 25 60 28 C 60 32 65 38 65 38 C 65 38 70 32 70 28 C 70 25 68 23 65 23 Z" fill="#841818" stroke="#1A1A1A" stroke-width="1.5"/>
                                        <circle cx="65" cy="28" r="1.8" fill="#F4B316"/>
                                    @else
                                        <path d="M 15 32 H 44 V 15" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                        <rect x="24" y="24" width="40" height="19" rx="3" fill="#841818" stroke="#1A1A1A" stroke-width="1.8"/>
                                        <path d="M22 24 H66" stroke="#F4B316" stroke-width="3" stroke-linecap="round"/>
                                        <circle cx="44" cy="33" r="3.5" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.2"/>
                                    @endif
                                </svg>
                            @endif
                            <span class="font-display" style="font-family: var(--font-display); font-size: {{ mb_strlen($pieceLabel) > 8 ? '11px' : '14px' }}; font-weight: 700; color: #841818; line-height: 1.05; text-align: center;">
                                {{ $pieceLabel }}
                            </span>
                        </button>
                    @endfor
                </div>
            </div>

            <!-- Cadenas du Code Secret Final -->
            <div
                style="background: #fffbeb; border: 2px solid #1a1a1a; border-radius: 16px; padding: 16px; margin-bottom: 18px;">
                <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 800; color: #92400e; text-transform: uppercase;">
                    Code Cadenas Coffre
                </p>
                <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 8px;" aria-hidden="true">
                    @foreach(str_split($finalCode) as $digit)
                        <span class="font-display" style="font-family: var(--font-display); display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 48px; background: #FFFFFF; border: 1.5px solid #1A1A1A; border-radius: 10px; font-size: 30px; font-weight: 700; color: #841818;">
                            {{ $digit }}
                        </span>
                    @endforeach
                </div>
                <h3 class="font-display" style="font-family: var(--font-display); font-size: 30px; font-weight: 700; color: #841818; margin: 0; letter-spacing: 0.06em;">
                    Code final : {{ $finalCode }}
                </h3>
                <p style="margin: 8px 0 0 0; font-size: 12px; color: #78350f;">
                    Rendez-vous à l'Office de Tourisme (Destination Lens) pour ouvrir le cadenas et récupérer votre
                    récompense Asmodee !
                </p>
            </div>

            <!-- QR Code Retrait Lot (généré localement en SVG hors-ligne + repli API) -->
            <div
                style="background: #fdfaf2; border: 2px solid #1a1a1a; border-radius: 16px; padding: 18px 16px; margin-bottom: 18px;">
                <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 6px 0; font-size: 19px; font-weight: 700; color: #841818;">
                    QR Code Retrait du Lot Dobble
                </h4>
                <p style="font-size: 13px; color: #4b5563; margin: 0 0 12px 0; line-height: 1.4;">
                    Présentez ce QR Code à l'accueil de l'Office de Tourisme pour débloquer votre boîte de jeu Dobble
                    collector.
                </p>

                @php
                    $token = $rewardToken ?: ($user?->reward_token ?? 'EXPEDITION_LENS_REWARD_PENDING');
                    $qrPayload = 'EXPEDITION_LENS_REWARD_' . $token;
                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=10&data=' . urlencode($qrPayload);
                @endphp

                <div id="reward-qr-payload" data-qr-payload="{{ $qrPayload }}"
                    style="display: inline-block; background: #ffffff; padding: 12px; border: 1.5px solid #1a1a1a; border-radius: 12px; margin-bottom: 12px;">
                    <div id="local-reward-qr-svg" x-show="localQrSvg" x-html="localQrSvg"
                        class="alpine-flex"
                        style="display: flex; align-items: center; justify-content: center;"></div>
                    <img x-show="!localQrSvg" src="{{ $qrUrl }}" alt="QR Code Retrait Lot" width="180" height="180"
                        style="display: block; max-width: 100%; height: auto;">
                    <div
                        style="font-family: monospace; font-size: 10px; font-weight: 700; color: #6b7280; margin-top: 6px; word-break: break-all;">
                        Token : {{ $token }}
                    </div>
                </div>

                <div style="margin-top: 6px;">
                    @if($rewardPicked)
                        <div
                            style="background: #dcfce7; border: 1.5px solid #16a34a; border-radius: 10px; padding: 10px; color: #166534; font-weight: 800; font-size: 13px;">
                            Statut : Lot déjà remis — Merci d'avoir joué !
                        </div>
                    @else
                        <div
                            style="background: #fef3c7; border: 1.5px solid #d97706; border-radius: 10px; padding: 10px; color: #92400e; font-weight: 800; font-size: 13px;">
                            Statut : Lot en attente de retrait
                        </div>
                    @endif
                </div>
            </div>

            <!-- Récapitulatif cliquable des Fiches Souvenirs -->
            <div
                style="text-align: left; background: #f9fafb; border: 2px solid #1a1a1a; border-radius: 14px; padding: 14px;">
                <h3 class="font-display" style="font-family: var(--font-display); margin: 0 0 10px 0; font-size: 18px; font-weight: 700; color: #1a1a1a;">
                    Fragments et indices récoltés (6/6) :
                </h3>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    @foreach($fragments as $fragment)
                        <button type="button" x-on:click="openMemory({{ $fragment->step->id }})"
                            style="width: 100%; text-align: left; background: #FFFFFF; border: 1.5px solid rgba(26, 26, 26, 0.45); border-radius: 10px; padding: 8px 10px; font-size: 12px; color: #374151; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span>
                                Étape {{ $fragment->step->id }} : {{ $fragment->step->cleanName() }} — Fragment :
                                <strong>{{ $fragment->step->code_part }}</strong>
                            </span>
                            <span style="font-weight: 800; color: #841818; white-space: nowrap;">Lire →</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 18px 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; gap: 8px;">
                <h2 class="font-display" style="font-family: var(--font-display); font-size: 24px; font-weight: 700; color: #841818; margin: 0; text-transform: uppercase; letter-spacing: 0.02em; line-height: 1.05;">
                    Carnet &amp; Carte de Gali
                </h2>
                <span class="font-display"
                    style="font-family: var(--font-display); background: #841818; color: #F4B316; padding: 4px 12px; border-radius: 9999px; font-size: 16px; font-weight: 700; border: 2px solid #1a1a1a; white-space: nowrap;">
                    {{ $fragments->count() }} / 6
                </span>
            </div>

            <p style="font-size: 13px; color: #4b5563; margin: 0 0 14px 0;">
                Progression : {{ $fragments->count() }} / 6 fragments débloqués
            </p>

            @if($fragments->isEmpty())
                <div
                    style="background: #fdfaf2; border: 2px dashed #d97706; border-radius: 12px; padding: 12px 14px; text-align: center; color: #78350f; font-size: 13px; line-height: 1.45; margin-bottom: 14px;">
                    <p style="margin: 0;">Aucun fragment débloqué pour le moment. Résolvez la première énigme de Gali avec la loupe et trouvez le Maître du Jeu !</p>
                </div>
            @endif

            <!-- Plateau Visuel RP : Puzzle 6 morceaux de la Carte au Trésor -->
            <div id="gali-treasure-map-puzzle"
                style="background: #F4EBD9; border: 2px solid #1A1A1A; border-radius: 16px; padding: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; gap: 8px; flex-wrap: wrap;">
                    <span class="font-display" style="font-family: var(--font-display); font-size: 17px; font-weight: 700; color: #1A1A1A;">
                        Carte au Trésor Puzzle
                    </span>
                    <span style="font-size: 11px; font-weight: 700; color: #78350F;">
                        {{ $fragments->isEmpty() ? '6 morceaux à assembler' : 'Touchez une pièce débloquée' }}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; background: #E7DEC8; border: 1.5px dashed rgba(180, 83, 9, 0.55); border-radius: 12px; padding: 8px;">
                    @for($i = 1; $i <= 6; $i++)
                        @php
                            $unlocked = $fragments->firstWhere('step_id', $i);
                            $pieceMeta = $puzzlePieces[$i];
                            $piecePath = $buildPuzzlePath($pieceMeta);
                            $pieceLabel = $unlocked ? $resolvePieceLabel($i, $unlocked) : '';
                            $assetPng = file_exists(public_path("images/puzzle/piece-{$i}.png"));
                            $assetSvg = file_exists(public_path("images/puzzle/piece-{$i}.svg"));
                        @endphp

                        @if($unlocked)
                            <button type="button"
                                id="puzzle-piece-{{ $i }}"
                                x-on:click="openMemory({{ $i }})"
                                title="Ouvrir la fiche souvenir : {{ $unlocked->step->cleanName() }}"
                                style="position: relative; background: #FFFBEB; border: 1.5px solid #B45309; border-radius: 12px; padding: 6px 4px; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: space-between; min-height: 98px;">
                                {{-- PLACEHOLDER PUZZLE PIECE {{ $i }} : déposez votre asset dans public/images/puzzle/piece-{{ $i }}.png (ou .svg) pour remplacer ce SVG --}}
                                @if($assetSvg || $assetPng)
                                    <img src="/images/puzzle/piece-{{ $i }}.{{ $assetSvg ? 'svg' : 'png' }}"
                                        alt="Morceau débloqué {{ $i }}"
                                        style="width: 100%; height: 54px; object-fit: contain; display: block;">
                                @else
                                    <svg viewBox="0 0 88 64" width="74" height="52" aria-hidden="true" style="display: block;">
                                        <path d="{{ $piecePath }}" fill="#FEF3C7" stroke="#1A1A1A" stroke-width="2" stroke-linejoin="round" />
                                        @if($i === 1)
                                            <path d="M 18 32 H 80 M 44 42 V 56" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                            <path d="M18 38 H66 M24 38 V26 H50 L58 31 V38" fill="none" stroke="#841818" stroke-width="2.2" stroke-linecap="round"/>
                                            <circle cx="30" cy="40" r="3" fill="#1A1A1A"/>
                                            <circle cx="48" cy="40" r="3" fill="#1A1A1A"/>
                                        @elseif($i === 2)
                                            <path d="M 15 32 H 80 M 44 36 V 56" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                            <path d="M20 40 V24 L32 16 L44 24 V40 Z" fill="#FDE68A" stroke="#1A1A1A" stroke-width="1.8"/>
                                            <circle cx="57" cy="27" r="7" fill="#FFFFFF" stroke="#841818" stroke-width="2"/>
                                            <line x1="62" y1="32" x2="69" y2="39" stroke="#1A1A1A" stroke-width="2.4" stroke-linecap="round"/>
                                        @elseif($i === 3)
                                            <path d="M 15 32 H 44 V 56" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                            <rect x="20" y="21" width="48" height="20" rx="6" fill="#841818" stroke="#1A1A1A" stroke-width="1.8"/>
                                            <rect x="28" y="25" width="32" height="12" rx="3" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.4"/>
                                        @elseif($i === 4)
                                            <path d="M 44 15 V 32 H 80" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                            <polygon points="18,40 30,22 66,22 70,40" fill="#E0F2FE" stroke="#1A1A1A" stroke-width="1.8"/>
                                            <circle cx="28" cy="39" r="5" fill="#9FB8AD" stroke="#1A1A1A" stroke-width="1.5"/>
                                        @elseif($i === 5)
                                            <path d="M 15 32 H 80 M 44 15 V 32" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                            <polygon points="16,42 30,20 44,42" fill="#374151" stroke="#1A1A1A" stroke-width="1.7"/>
                                            <polygon points="34,42 48,18 62,42" fill="#1F2937" stroke="#1A1A1A" stroke-width="1.7"/>
                                            <path d="M 65 23 C 62 23 60 25 60 28 C 60 32 65 38 65 38 C 65 38 70 32 70 28 C 70 25 68 23 65 23 Z" fill="#841818" stroke="#1A1A1A" stroke-width="1.5"/>
                                            <circle cx="65" cy="28" r="1.8" fill="#F4B316"/>
                                        @else
                                            <path d="M 15 32 H 44 V 15" fill="none" stroke="#D97706" stroke-width="1.6" stroke-dasharray="3 2"/>
                                            <rect x="24" y="24" width="40" height="19" rx="3" fill="#841818" stroke="#1A1A1A" stroke-width="1.8"/>
                                            <path d="M22 24 H66" stroke="#F4B316" stroke-width="3" stroke-linecap="round"/>
                                            <circle cx="44" cy="33" r="3.5" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.2"/>
                                        @endif
                                    </svg>
                                @endif
                                <div style="width: 100%; text-align: center;">
                                    <span class="font-display" style="font-family: var(--font-display); font-size: {{ mb_strlen($pieceLabel) > 8 ? '11px' : '16px' }}; font-weight: 700; color: #841818; display: block; line-height: 1.05;">
                                        {{ $pieceLabel }}
                                    </span>
                                    <span style="font-size: 10px; font-weight: 800; color: #78350F; display: block; margin-top: 2px;">
                                        Fiche souvenir
                                    </span>
                                </div>
                            </button>
                        @else
                            <div id="puzzle-piece-{{ $i }}"
                                style="background: #EFEAE0; border: 1.5px dashed #9CA3AF; border-radius: 12px; padding: 6px 4px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 98px; opacity: 0.9;">
                                {{-- PLACEHOLDER VERROUILLE {{ $i }} --}}
                                <svg viewBox="0 0 88 64" width="72" height="50" aria-hidden="true" style="display: block;">
                                    <path d="{{ $piecePath }}" fill="#E5E7EB" stroke="#9CA3AF" stroke-width="2" stroke-dasharray="4 3" stroke-linejoin="round" />
                                    <text x="44" y="38" text-anchor="middle" font-size="18" font-weight="700" fill="#9CA3AF">?</text>
                                </svg>
                                <span style="font-size: 10px; font-weight: 800; color: #6B7280; margin-top: 4px;">
                                    Pièce {{ $i }}
                                </span>
                            </div>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
    @endif

    <!-- Modale Fiche Souvenir du Carnet de Gali (uniquement pour les morceaux débloqués !) -->
    @if($fragments->isNotEmpty())
        <div id="inventory-memory-modal" x-show="selectedMemory !== null" x-cloak
            x-on:click.self="closeMemory()"
            role="dialog" aria-modal="true"
            class="alpine-flex"
            style="position: fixed; inset: 0; z-index: 9990; background: rgba(17, 24, 39, 0.78); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 18px; box-sizing: border-box;">
            <div style="width: 100%; max-width: 390px; max-height: 85vh; overflow-y: auto; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-radius: 22px; box-shadow: 0 8px 0 #1A1A1A; padding: 20px 18px; box-sizing: border-box;">
                @foreach($fragments as $fragment)
                    @php
                        $stepModel = $fragment->step;
                    @endphp
                    @if($stepModel)
                        <div x-show="selectedMemory === {{ $stepModel->id }}" x-cloak>
                            <div style="border-bottom: 2px solid #E5E7EB; padding-bottom: 10px; margin-bottom: 12px;">
                                <span style="display: inline-block; background: #FEF3C7; border: 1.5px solid #1A1A1A; border-radius: 9999px; padding: 2px 10px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; margin-bottom: 4px;">
                                    Carnet de Gali — Étape {{ $stepModel->id }}/6
                                </span>
                                <h3 class="font-display" style="font-family: var(--font-display); margin: 0; font-size: 24px; font-weight: 700; color: #1A1A1A; line-height: 1.1;">
                                    {{ $stepModel->cleanName() }}
                                </h3>
                            </div>

                            <div style="background: #841818; color: #FFFFFF; border: 2px solid #1A1A1A; border-radius: 12px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px;">
                                Étape {{ $stepModel->id }} : {{ $stepModel->cleanName() }} — Fragment :
                                <strong style="color: #F4B316;">{{ $stepModel->code_part }}</strong>
                            </div>

                            <div style="background: #FFFBEB; border: 2px solid #1A1A1A; border-radius: 14px; padding: 12px; margin-bottom: 12px;">
                                <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 4px 0; font-size: 17px; font-weight: 700; color: #841818;">
                                    Le mot de Gali
                                </h4>
                                <p style="margin: 0; font-size: 13px; color: #374151; line-height: 1.45;">
                                    {{ $stepModel->story_rp }}
                                </p>
                            </div>

                            <div style="background: #F4EBD9; border: 2px solid #1A1A1A; border-radius: 14px; padding: 12px; margin-bottom: 14px;">
                                <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 4px 0; font-size: 17px; font-weight: 700; color: #1A1A1A;">
                                    L'Histoire du lieu
                                </h4>
                                <p style="margin: 0; font-size: 13px; color: #374151; line-height: 1.45;">
                                    {{ $stepModel->heritage_info }}
                                </p>
                            </div>

                            <button type="button" id="close-memory-modal-btn" x-on:click="closeMemory()" class="btn-bounce font-display"
                                style="font-family: var(--font-display); width: 100%; padding: 10px 14px; background: #F4B316; color: #1A1A1A; border: 2.5px solid #1A1A1A; border-radius: 12px; box-shadow: 0 3px 0 #1A1A1A; font-size: 17px; font-weight: 700; cursor: pointer;">
                                Fermer la fiche
                            </button>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
