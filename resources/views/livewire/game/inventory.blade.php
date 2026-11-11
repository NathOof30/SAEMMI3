<div x-data="{
        selectedMemory: null,
        justUnlockedStep: null,
        localQrSvg: '',
        openMemory(stepId) {
            this.selectedMemory = stepId;
        },
        closeMemory() {
            this.selectedMemory = null;
        },
        checkUnlockAnimation() {
            const stepId = window.__expeditionPendingUnlockedStep || null;
            if (stepId) {
                window.__expeditionPendingUnlockedStep = null;
                this.justUnlockedStep = stepId;
                setTimeout(() => {
                    if (this.justUnlockedStep === stepId) {
                        this.justUnlockedStep = null;
                    }
                }, 2200);
            }
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
    x-init="$nextTick(() => { initInventoryQr(); if (typeof tab === 'undefined' || tab === 'inventory') { checkUnlockAnimation(); } })"
    x-on:tab-switched.window="if ($event.detail && $event.detail.tab === 'inventory') { $nextTick(() => { initInventoryQr(); checkUnlockAnimation(); }); }"
    x-on:set-tab.window="if ($event.detail && $event.detail.tab === 'inventory') { $nextTick(() => { initInventoryQr(); checkUnlockAnimation(); }); }"
    x-on:fragment-unlocked.window="if ($event.detail && $event.detail.stepId) { window.__expeditionPendingUnlockedStep = $event.detail.stepId; if (typeof tab === 'undefined' || tab === 'inventory') { checkUnlockAnimation(); } }"
    x-on:keydown.escape.window="closeMemory()"
    style="max-width: 480px; margin: 0 auto; padding: 10px 16px 24px 16px; font-family: var(--font-body); color: #1a1a1a;">

    <style>
        @keyframes keyShardSnap {
            0% {
                transform: scale(0.86) translateY(-6px);
                filter: drop-shadow(0 0 0 rgba(244, 179, 22, 0));
            }
            45% {
                transform: scale(1.06) translateY(0);
                filter: drop-shadow(0 0 12px rgba(244, 179, 22, 0.9));
            }
            100% {
                transform: scale(1) translateY(0);
                filter: drop-shadow(0 0 0 rgba(244, 179, 22, 0));
            }
        }

        .key-shard-just-unlocked {
            animation: keyShardSnap 0.9s cubic-bezier(0.22, 1, 0.36, 1) 1 forwards;
            transform-box: fill-box;
            transform-origin: center;
        }
    </style>

    <!-- Bouton Retour à la boussole en haut à gauche -->
    <div style="margin-bottom: 12px;">
        <button type="button" id="inventory-back-to-hub-btn" x-on:click="tab = 'hub'"
            wire:click="$dispatch('set-tab', { tab: 'hub' })" class="btn-bounce"
            style="background: #FFFFFF; border: 2.5px solid #1A1A1A; border-radius: 12px; padding: 8px 14px; font-size: 13px; font-weight: 800; color: #1A1A1A; box-shadow: 0 3px 0 #1A1A1A; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            ← Retour à la boussole
        </button>
    </div>

    @php
        $keyShards = [
            1 => [
                'defaultName' => "Bord de l'anneau",
                'path' => 'M 60 23 C 80 26 94 46 94 74 L 85 78 L 80 68 L 72 72 C 73 69 74 66 74 63 C 74 49 60 44 52 54 L 51 41 L 57 34 L 60 23 Z',
                'highlight' => 'M 63 29 C 77 33 87 48 88 67',
                'cx' => 76,
                'cy' => 52,
            ],
            2 => [
                'defaultName' => "Base de l'anneau",
                'path' => 'M 10 66 L 17 71 L 21 63 L 30 68 C 32 80 45 89 52 96 C 59 89 69 81 72 72 L 80 68 L 85 78 L 94 74 C 94 94 82 108 69 114 L 56 122 L 49 115 L 35 114 C 22 108 10 92 10 66 Z',
                'highlight' => 'M 20 92 C 29 104 41 110 52 111',
                'cx' => 52,
                'cy' => 107,
            ],
            3 => [
                'defaultName' => 'Col sculpté',
                'path' => 'M 35 114 L 49 115 L 56 122 L 69 114 L 66 121 L 73 127 L 65 135 L 71 143 L 63 152 L 63 178 L 55 171 L 49 184 L 41 166 L 41 152 L 33 143 L 39 135 L 31 127 L 38 121 L 35 114 Z',
                'highlight' => 'M 38 128 H 66 M 37 143 H 67',
                'cx' => 52,
                'cy' => 145,
            ],
            4 => [
                'defaultName' => 'Fût gravé',
                'path' => 'M 41 166 L 49 184 L 55 171 L 63 178 L 63 224 L 54 238 L 47 229 L 41 242 L 41 166 Z',
                'highlight' => 'M 48 189 V 222 M 56 184 V 219',
                'cx' => 52,
                'cy' => 205,
            ],
            5 => [
                'defaultName' => 'Garde du fût',
                'path' => 'M 41 242 L 47 229 L 54 238 L 63 224 L 63 262 L 74 262 L 71 276 L 78 290 L 69 306 L 75 320 L 68 330 L 63 330 L 63 335 Q 63 343 52 343 Q 41 343 41 335 L 41 242 Z',
                'highlight' => 'M 48 248 V 330',
                'cx' => 53,
                'cy' => 287,
            ],
            6 => [
                'defaultName' => 'Dents de la clé',
                'path' => 'M 74 262 L 114 262 L 114 277 L 95 277 L 95 290 L 116 290 L 116 307 L 89 307 L 89 318 L 107 318 L 107 330 L 68 330 L 75 320 L 69 306 L 78 290 L 71 276 L 74 262 Z',
                'highlight' => 'M 83 270 H 106 M 83 298 H 107',
                'cx' => 96,
                'cy' => 296,
            ],
        ];

        $starterShardPath = 'M 52 12 C 58 12 62 17 60 23 L 57 34 L 51 41 L 52 54 C 43 44 29 52 30 68 L 21 63 L 17 71 L 10 66 C 10 42 24 26 44 23 C 42 17 46 12 52 12 Z';
        $fullKeyOuterPath = 'M 52 12 C 58 12 62 17 60 23 C 80 26 94 46 94 74 C 94 94 82 108 69 114 L 66 121 L 73 127 L 65 135 L 71 143 L 63 152 L 63 262 L 114 262 L 114 277 L 95 277 L 95 290 L 116 290 L 116 307 L 89 307 L 89 318 L 107 318 L 107 330 L 63 330 L 63 335 Q 63 343 52 343 Q 41 343 41 335 L 41 152 L 33 143 L 39 135 L 31 127 L 38 121 L 35 114 C 22 108 10 92 10 66 C 10 42 24 26 44 23 C 42 17 46 12 52 12 Z M 52 54 C 43 44 29 52 30 68 C 32 80 45 89 52 96 C 59 89 69 81 72 72 C 73 69 74 66 74 63 C 74 49 60 44 52 54 Z';
        $completeKeyPng = file_exists(public_path('images/key/complete-key.png'));
        $completeKeySvg = file_exists(public_path('images/key/complete-key.svg'));
    @endphp

    @if($hasWon)
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 20px 16px; text-align: center;">
            <div
                style="display: inline-flex; align-items: center; gap: 6px; background: #fef3c7; border: 2px solid #1a1a1a; border-radius: 9999px; padding: 5px 14px; font-size: 11px; font-weight: 800; color: #841818; margin-bottom: 10px; text-transform: uppercase;">
                <span>Clé Complète assemblée (6/6)</span>
                <span aria-hidden="true">|</span>
                <span>Lampe Cœur rallumée</span>
            </div>

            <h2 class="font-display" style="font-family: var(--font-display); font-size: 30px; font-weight: 700; color: #841818; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.03em;">
                Victoire !
            </h2>

            <p style="font-size: 14px; margin: 0 0 6px 0; color: #374151;">
                Félicitations à l'équipe <strong>{{ $user?->team_name ?? $user?->name ?? 'Équipe' }}</strong> !
            </p>
            <p style="font-size: 13px; margin: 0 0 16px 0; color: #4b5563; line-height: 1.4;">
                Vous avez triomphé des 6 étapes, reforgé la clé antique de Gali et fait briller à nouveau la Lampe Cœur !
            </p>

            <!-- Assemblage Clé Verticale Complète (6/6) -->
            <div id="gali-key-assembly"
                style="background: #F4EBD9; border: 2px solid #1A1A1A; border-radius: 16px; padding: 12px; margin-bottom: 18px; text-align: left;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; gap: 6px; flex-wrap: wrap;">
                    <span class="font-display" style="font-family: var(--font-display); font-size: 17px; font-weight: 700; color: #841818;">
                        Clé Antique de Gali (6/6)
                    </span>
                    <span style="font-size: 11px; font-weight: 700; color: #78350F;">
                        Touchez un fragment
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: clamp(104px, 34%, 128px) minmax(0, 1fr); gap: 10px; align-items: center; background: #FFFBEB; border: 1.5px solid #B45309; border-radius: 12px; padding: 10px 8px;">
                    <div style="display: flex; align-items: center; justify-content: center;">
                        {{-- PLACEHOLDER CLE ANTIQUE COMPLETE : déposez public/images/key/complete-key.svg (ou .png) pour remplacer ce SVG --}}
                        @if($completeKeySvg || $completeKeyPng)
                            <img src="/images/key/complete-key.{{ $completeKeySvg ? 'svg' : 'png' }}"
                                alt="Clé antique complète assemblée"
                                style="width: 116px; height: auto; max-height: 330px; object-fit: contain; display: block;">
                        @else
                            <svg id="gali-vertical-key-svg" viewBox="0 0 130 350" width="118" height="318"
                                role="img" aria-label="Clé antique verticale complète en or"
                                style="width: 100%; max-width: 122px; height: auto; display: block;">
                                <path d="{{ $fullKeyOuterPath }}" fill="#F4B316" fill-rule="evenodd" stroke="#1A1A1A" stroke-width="2.4" stroke-linejoin="round" />

                                <!-- Pièce du grenier (couronne supérieure gauche) -->
                                <g id="key-svg-shard-starter">
                                    <path d="{{ $starterShardPath }}" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.8" stroke-linejoin="round" />
                                    <path d="M 21 53 C 22 39 33 30 46 28" fill="none" stroke="#FEF3C7" stroke-width="2" stroke-linecap="round" />
                                </g>

                                @for($i = 1; $i <= 6; $i++)
                                    @php
                                        $shard = $keyShards[$i];
                                        $shardAssetSvg = file_exists(public_path("images/key/fragment-{$i}.svg"));
                                        $shardAssetPng = file_exists(public_path("images/key/fragment-{$i}.png"));
                                    @endphp
                                    {{-- PLACEHOLDER FRAGMENT CLE {{ $i }} : public/images/key/fragment-{{ $i }}.svg (.png) --}}
                                    <g id="key-svg-shard-{{ $i }}"
                                        x-on:click="openMemory({{ $i }})"
                                        :class="{ 'key-shard-just-unlocked': justUnlockedStep === {{ $i }} }"
                                        :data-just-unlocked="justUnlockedStep === {{ $i }} ? '1' : '0'"
                                        style="cursor: pointer;">
                                        <path d="{{ $shard['path'] }}" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.8" stroke-linejoin="round" />
                                        <path d="{{ $shard['highlight'] }}" fill="none" stroke="#FEF3C7" stroke-width="1.8" stroke-linecap="round" />
                                    </g>
                                @endfor
                            </svg>
                        @endif
                    </div>

                    <!-- Emplacements alignés verticalement de haut en bas -->
                    <div style="display: flex; flex-direction: column; gap: 6px; min-width: 0;">
                        <div id="key-fragment-starter"
                            style="background: #FEF3C7; border: 1.5px solid #B45309; border-radius: 10px; padding: 6px 8px; display: flex; flex-direction: column; gap: 2px;">
                            <span style="font-size: 10px; font-weight: 800; color: #841818; text-transform: uppercase;">Base de la clé</span>
                            <span style="font-size: 11px; font-weight: 700; color: #4B5563;">Déjà acquis  |  Grenier</span>
                        </div>

                        @for($i = 1; $i <= 6; $i++)
                            @php
                                $unlocked = $fragments->firstWhere('step_id', $i);
                                $partLabel = $unlocked?->step?->code_part ?: $keyShards[$i]['defaultName'];
                            @endphp
                            <button type="button"
                                id="key-fragment-{{ $i }}"
                                x-on:click="openMemory({{ $i }})"
                                :class="{ 'key-shard-just-unlocked': justUnlockedStep === {{ $i }} }"
                                :data-just-unlocked="justUnlockedStep === {{ $i }} ? '1' : '0'"
                                style="width: 100%; text-align: left; background: #FFFFFF; border: 1.5px solid #B45309; border-radius: 10px; padding: 6px 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                <span style="min-width: 0;">
                                    <span style="display: block; font-size: 10px; font-weight: 800; color: #78350F; text-transform: uppercase;">
                                        Fragment {{ $i }}/6
                                    </span>
                                    <span style="display: block; font-size: 12px; font-weight: 800; color: #841818; line-height: 1.2;">
                                        {{ $partLabel }}
                                    </span>
                                </span>
                                <span style="font-size: 11px; font-weight: 800; color: #841818; white-space: nowrap;">Lire →</span>
                            </button>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- QR Code Retrait Trésor (généré localement en SVG hors-ligne + repli API) -->
            <div id="victory-treasure-qr-card"
                style="background: #fdfaf2; border: 2px solid #1a1a1a; border-radius: 16px; padding: 18px 16px; margin-bottom: 18px;">
                <h3 class="font-display" style="font-family: var(--font-display); margin: 0 0 6px 0; font-size: 21px; font-weight: 700; color: #841818;">
                    QR Code Retrait du Trésor
                </h3>
                <p style="font-size: 13px; color: #4b5563; margin: 0 0 12px 0; line-height: 1.4;">
                    Présentez ce QR Code à l'accueil de l'Office de Tourisme pour ouvrir le coffre et récupérer votre véritable trésor surprise !
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
                    <img x-show="!localQrSvg" src="{{ $qrUrl }}" alt="QR Code Retrait du Trésor" width="180" height="180"
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
                            Statut : Trésor déjà remis  |  Merci d'avoir joué !
                        </div>
                    @else
                        <div
                            style="background: #fef3c7; border: 1.5px solid #d97706; border-radius: 10px; padding: 10px; color: #92400e; font-weight: 800; font-size: 13px;">
                            Statut : Trésor en attente de retrait
                        </div>
                    @endif
                </div>
            </div>

            <!-- Encart dédié : Inscription Automatique au Grand Tirage au Sort -->
            <div id="victory-raffle-card"
                style="background: #FFFBEB; border: 2px solid #1A1A1A; border-radius: 16px; padding: 16px; margin-bottom: 18px; text-align: left;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: #F4B316; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 9V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z" fill="#FEF3C7"/>
                            <path d="M12 8.5l1 2.1 2.3.3-1.7 1.6.4 2.3-2-1.1-2 1.1.4-2.3-1.7-1.6 2.3-.3z" fill="#841818"/>
                        </svg>
                    </div>
                    <div style="min-width: 0;">
                        <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #841818; line-height: 1.15;">
                            Inscription automatique au grand tirage au sort !
                        </h4>
                        <p style="margin: 0; font-size: 12.5px; color: #374151; line-height: 1.45;">
                            En terminant l'expédition, l'équipe <strong>{{ $user?->team_name ?? $user?->name ?? 'Équipe' }}</strong> ({{ $user?->email }}) est <strong>automatiquement inscrite</strong> au grand tirage au sort pour tenter de remporter de nombreux autres lots. Aucune démarche supplémentaire requise !
                        </p>
                    </div>
                </div>
            </div>

            <!-- Récapitulatif cliquable des Fiches Souvenirs -->
            <div
                style="text-align: left; background: #f9fafb; border: 2px solid #1a1a1a; border-radius: 14px; padding: 14px;">
                <h3 class="font-display" style="font-family: var(--font-display); margin: 0 0 10px 0; font-size: 18px; font-weight: 700; color: #1a1a1a;">
                    Fragments et souvenirs récoltés (6/6) :
                </h3>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    @foreach($fragments as $fragment)
                        <button type="button" x-on:click="openMemory({{ $fragment->step->id }})"
                            style="width: 100%; text-align: left; background: #FFFFFF; border: 1.5px solid rgba(26, 26, 26, 0.45); border-radius: 10px; padding: 8px 10px; font-size: 12px; color: #374151; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span>
                                Fragment {{ $fragment->step->id }}/6  |  {{ $fragment->step->cleanName() }}  |
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
                    Carnet &amp; Clé de Gali
                </h2>
                <span class="font-display"
                    style="font-family: var(--font-display); background: #841818; color: #F4B316; padding: 4px 12px; border-radius: 9999px; font-size: 16px; font-weight: 700; border: 2px solid #1a1a1a; white-space: nowrap;">
                    {{ $fragments->count() }} / 6
                </span>
            </div>

            <p style="font-size: 13px; color: #4b5563; margin: 0 0 14px 0;">
                Progression : {{ $fragments->count() }} / 6 fragments de clé débloqués
            </p>

            @if($fragments->isEmpty())
                <div
                    style="background: #fdfaf2; border: 2px dashed #d97706; border-radius: 12px; padding: 12px 14px; text-align: center; color: #78350f; font-size: 13px; line-height: 1.45; margin-bottom: 14px;">
                    <p style="margin: 0;">Aucun fragment de clé débloqué pour le moment. Résolvez la première énigme de Gali avec la loupe et retrouvez l'animateur de l'étape sur place !</p>
                </div>
            @endif

            <!-- Assemblage Clé Verticale (Grise avec morceaux brisés + pièce de départ du grenier en or) -->
            <div id="gali-key-assembly"
                style="background: #F4EBD9; border: 2px solid #1A1A1A; border-radius: 16px; padding: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; gap: 8px; flex-wrap: wrap;">
                    <span class="font-display" style="font-family: var(--font-display); font-size: 17px; font-weight: 700; color: #1A1A1A;">
                        Clé Antique à Réparer
                    </span>
                    <span style="font-size: 11px; font-weight: 700; color: #78350F;">
                        {{ $fragments->isEmpty() ? '6 fragments brisés à retrouver' : 'Touchez un fragment débloqué' }}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: clamp(104px, 34%, 128px) minmax(0, 1fr); gap: 10px; align-items: center; background: #E7DEC8; border: 1.5px dashed rgba(180, 83, 9, 0.55); border-radius: 12px; padding: 10px 8px;">
                    <!-- Colonne gauche : Clé verticale grise + fragments dorés assemblés -->
                    <div style="display: flex; align-items: center; justify-content: center;">
                        <svg id="gali-vertical-key-svg" viewBox="0 0 130 350" width="118" height="318"
                            role="img" aria-label="Silhouette verticale de la clé antique grise et fragments assemblés"
                            style="width: 100%; max-width: 122px; height: auto; display: block;">
                            <!-- Base silhouette grise métallique de la clé complète -->
                            <path d="{{ $fullKeyOuterPath }}" fill="#D1D5DB" fill-rule="evenodd" stroke="#6B7280" stroke-width="2.2" stroke-linejoin="round" />

                            <!-- Pièce de départ du grenier (toujours fixée en or dès 0/6) -->
                            <g id="key-svg-shard-starter">
                                <path d="{{ $starterShardPath }}" fill="#F4B316" stroke="#1A1A1A" stroke-width="2" stroke-linejoin="round" />
                                <path d="M 21 53 C 22 39 33 30 46 28" fill="none" stroke="#FEF3C7" stroke-width="2" stroke-linecap="round" />
                            </g>

                            @for($i = 1; $i <= 6; $i++)
                                @php
                                    $unlocked = $fragments->firstWhere('step_id', $i);
                                    $shard = $keyShards[$i];
                                @endphp
                                @if($unlocked)
                                    <g id="key-svg-shard-{{ $i }}"
                                        x-on:click="openMemory({{ $i }})"
                                        :class="{ 'key-shard-just-unlocked': justUnlockedStep === {{ $i }} }"
                                        :data-just-unlocked="justUnlockedStep === {{ $i }} ? '1' : '0'"
                                        style="cursor: pointer;">
                                        <path d="{{ $shard['path'] }}" fill="#F4B316" stroke="#1A1A1A" stroke-width="2" stroke-linejoin="round" />
                                        <path d="{{ $shard['highlight'] }}" fill="none" stroke="#FEF3C7" stroke-width="1.8" stroke-linecap="round" />
                                    </g>
                                @else
                                    <g id="key-svg-shard-{{ $i }}">
                                        <path d="{{ $shard['path'] }}" fill="#D1D5DB" stroke="#6B7280" stroke-width="1.6" stroke-dasharray="3 2" stroke-linejoin="round" />
                                        <circle cx="{{ $shard['cx'] }}" cy="{{ $shard['cy'] }}" r="8.5" fill="#E5E7EB" stroke="#6B7280" stroke-width="1.3" />
                                        <text x="{{ $shard['cx'] }}" y="{{ $shard['cy'] + 3.5 }}" text-anchor="middle" font-size="10" font-weight="800" fill="#4B5563">{{ $i }}</text>
                                    </g>
                                @endif
                            @endfor
                        </svg>
                    </div>

                    <!-- Colonne droite : Emplacements ordonnés verticalement -->
                    <div style="display: flex; flex-direction: column; gap: 6px; min-width: 0;">
                        <div id="key-fragment-starter"
                            style="background: #FEF3C7; border: 1.5px solid #B45309; border-radius: 10px; padding: 6px 8px; display: flex; flex-direction: column; gap: 2px;">
                            <span style="font-size: 10px; font-weight: 800; color: #841818; text-transform: uppercase;">Base de la clé</span>
                            <span style="font-size: 11px; font-weight: 700; color: #4B5563;">Déjà acquis  |  Grenier</span>
                        </div>

                        @for($i = 1; $i <= 6; $i++)
                            @php
                                $unlocked = $fragments->firstWhere('step_id', $i);
                                $partLabel = $unlocked?->step?->code_part ?: $keyShards[$i]['defaultName'];
                            @endphp
                            @if($unlocked)
                                <button type="button"
                                    id="key-fragment-{{ $i }}"
                                    x-on:click="openMemory({{ $i }})"
                                    :class="{ 'key-shard-just-unlocked': justUnlockedStep === {{ $i }} }"
                                    :data-just-unlocked="justUnlockedStep === {{ $i }} ? '1' : '0'"
                                    title="Ouvrir la fiche souvenir : {{ $unlocked->step->cleanName() }}"
                                    style="width: 100%; text-align: left; background: #FFFBEB; border: 1.5px solid #B45309; border-radius: 10px; padding: 6px 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                    <span style="min-width: 0;">
                                        <span style="display: block; font-size: 10px; font-weight: 800; color: #78350F; text-transform: uppercase;">
                                            Fragment {{ $i }}/6
                                        </span>
                                        <span style="display: block; font-size: 12px; font-weight: 800; color: #841818; line-height: 1.2;">
                                            {{ $partLabel }}
                                        </span>
                                    </span>
                                    <span style="font-size: 11px; font-weight: 800; color: #841818; white-space: nowrap;">Lire →</span>
                                </button>
                            @else
                                <div id="key-fragment-{{ $i }}"
                                    style="background: #EFEAE0; border: 1.5px dashed #9CA3AF; border-radius: 10px; padding: 6px 8px; display: flex; align-items: center; justify-content: space-between; gap: 6px; opacity: 0.92;">
                                    <span style="min-width: 0;">
                                        <span style="display: block; font-size: 10px; font-weight: 800; color: #6B7280; text-transform: uppercase;">
                                            Fragment {{ $i }}/6
                                        </span>
                                        <span style="display: block; font-size: 11px; font-weight: 700; color: #6B7280; line-height: 1.2;">
                                            ?  |  À découvrir
                                        </span>
                                    </span>
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>

            @if($fragments->isNotEmpty())
                <div style="margin-top: 14px;">
                    <button type="button" id="inventory-continue-next-btn" x-on:click="tab = 'hub'"
                        wire:click="$dispatch('set-tab', { tab: 'hub' })" class="btn-bounce font-display"
                        style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 13px 16px; background: #F4B316; color: #1A1A1A; font-size: 19px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 4px 0 #1A1A1A; cursor: pointer; text-align: center; letter-spacing: 0.03em;">
                        Poursuivre vers l'étape suivante →
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Modale Fiche Souvenir du Carnet de Gali (uniquement pour les fragments débloqués) -->
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
                                    Carnet de Gali  |  Étape {{ $stepModel->id }}/6
                                </span>
                                <h3 class="font-display" style="font-family: var(--font-display); margin: 0; font-size: 24px; font-weight: 700; color: #1A1A1A; line-height: 1.1;">
                                    {{ $stepModel->cleanName() }}
                                </h3>
                            </div>

                            <div style="background: #841818; color: #FFFFFF; border: 2px solid #1A1A1A; border-radius: 12px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px;">
                                Fragment {{ $stepModel->id }}/6  |  {{ $stepModel->cleanName() }}  |
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
