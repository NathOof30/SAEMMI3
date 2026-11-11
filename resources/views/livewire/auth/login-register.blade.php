<div x-data="{
    onboardingStep: '{{ $mode === 'tuto' ? 'slider' : 'welcome' }}',
    activeSlide: 0,
    totalSlides: 3,
    nextSlide() {
        if (this.activeSlide < this.totalSlides - 1) {
            this.activeSlide++;
        }
    },
    prevSlide() {
        if (this.activeSlide > 0) {
            this.activeSlide--;
        }
    },
    goToSlide(index) {
        this.activeSlide = index;
    }
}"
    style="width: 100%; max-width: 420px; height: 100vh; height: 100dvh; max-height: 100dvh; margin: 0 auto; padding: 16px 18px; box-sizing: border-box; font-family: var(--font-body); color: #1a1a1a; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden;">

    <!-- 1. En-tête haut de page -->
    <header style="text-align: center; flex-shrink: 0; padding-top: 2px;">
        <h1 class="font-display"
            style="font-family: var(--font-display); font-size: 30px; font-weight: 700; color: #841818; margin: 0 0 2px 0; text-transform: uppercase; letter-spacing: 0.04em; line-height: 1.05;">
            L'Expédition Secrète
        </h1>
        <p style="margin: 0; font-size: 13px; font-weight: 700; color: #b45309;">
            Lens Tourisme & Asmodee
        </p>
    </header>

    @if($mode === 'landing' || $mode === 'tuto')
        <!-- MODE ACCUEIL (WELCOME) -->
        <div x-show="onboardingStep === 'welcome'" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; min-height: 0; margin: 10px 0;">
            <!-- Carte centrale -->
            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0; padding: 4px 0;">
                <main
                    style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 18px 16px; box-sizing: border-box; text-align: center; width: 100%;">
                    <span class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;">Connexion / Inscription Équipe</span>

                    <!-- Emblème de la Lampe Cœur (placeholder vectoriel remplaçable) -->
                    <div style="display: flex; justify-content: center; margin-bottom: 8px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #FEF3C7; border: 2.5px solid #1A1A1A; box-shadow: 0 3px 0 #1A1A1A; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 32 32" width="28" height="28" fill="none" aria-hidden="true">
                                <path d="M11 6 H21" stroke="#1A1A1A" stroke-width="2.4" stroke-linecap="round"/>
                                <path d="M13 6 V9 H19 V6" stroke="#1A1A1A" stroke-width="2" fill="#9CA3AF"/>
                                <rect x="9" y="9" width="14" height="14" rx="3" fill="#FFFBEB" stroke="#1A1A1A" stroke-width="2.2"/>
                                <path d="M16 20.5 C16 20.5 11.5 17.5 11.5 14.3 C11.5 12.8 12.7 11.8 14 11.8 C14.9 11.8 15.6 12.3 16 13 C16.4 12.3 17.1 11.8 18 11.8 C19.3 11.8 20.5 12.8 20.5 14.3 C20.5 17.5 16 20.5 16 20.5 Z" fill="#F4B316" stroke="#841818" stroke-width="1.5"/>
                                <rect x="8" y="23" width="16" height="4" rx="1.5" fill="#841818" stroke="#1A1A1A" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>

                    <h2 class="font-display" style="font-family: var(--font-display); font-size: 24px; font-weight: 700; margin: 0 0 10px 0; color: #1a1a1a; letter-spacing: 0.02em; line-height: 1.1;">
                        Bienvenue, Explorateurs !
                    </h2>

                    <div
                        style="background: #fdfaf2; border: 2.5px dashed #d97706; border-radius: 16px; padding: 14px 12px; text-align: left;">
                        <p class="font-display" style="font-family: var(--font-display); margin: 0 0 6px 0; font-weight: 700; font-size: 18px; color: #841818; text-align: center; letter-spacing: 0.02em;">
                            La légende de la Lampe Cœur
                        </p>
                        <p style="margin: 0 0 6px 0; font-size: 13px; color: #374151; line-height: 1.4;">
                            Dans le <strong>grenier</strong> de son grand-père, sous une veste de mineur, <strong>Gali</strong> a découvert un bout de <strong>papier jauni</strong> menant à la <strong>Lampe Cœur</strong>, la gaillette de charbon magique qui illumine Lens !
                        </p>
                        <p style="margin: 0; font-size: 12px; font-weight: 700; color: #92400e; text-align: center; line-height: 1.35;">
                            Au dos l'attend le premier indice : partez en famille réunir les 6 fragments et faites briller la ville à nouveau.
                        </p>
                    </div>
                </main>
            </div>

            <!-- Bloc d'actions ancré en bas -->
            <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 8px;">
                <button type="button" x-on:click="onboardingStep = 'slider'; activeSlide = 0;"
                    class="btn-bounce font-display"
                    style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 14px 18px; background: #F4B316; color: #1a1a1a; font-weight: 700; font-size: 21px; text-transform: uppercase; border: 3px solid #1a1a1a; border-radius: 14px; box-shadow: 0 5px 0 #1a1a1a; cursor: pointer; letter-spacing: 0.04em;">
                    Démarrer l'aventure
                </button>

                <div style="display: flex; gap: 10px;">
                    <button type="button" wire:click="setMode('register')"
                        class="btn-bounce"
                        style="flex: 1; box-sizing: border-box; padding: 10px 10px; background: #F9FAFB; color: #1a1a1a; font-weight: 700; font-size: 13px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer; text-align: center;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        class="btn-bounce"
                        style="flex: 1; box-sizing: border-box; padding: 10px 10px; background: #F9FAFB; color: #1a1a1a; font-weight: 700; font-size: 13px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer; text-align: center;">
                        Connexion équipe
                    </button>
                </div>

                <p style="margin: 2px 0 0 0; text-align: center; font-size: 11px;">
                    <a href="{{ route('test-qr') }}" target="_blank" style="color: #6b7280; text-decoration: underline;">
                        Tester les QR Codes sur écran PC (/test-qr)
                    </a>
                </p>
            </div>
        </div>

        <!-- MODE TUTORIEL (SLIDER) -->
        <div x-show="onboardingStep === 'slider'" x-cloak style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; min-height: 0; margin: 12px 0;">
            <!-- Carte centrale du slide -->
            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0; padding: 6px 0;">
                <main
                    style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 22px 18px; box-sizing: border-box; width: 100%; min-height: 210px; display: flex; flex-direction: column; justify-content: center;">
                    <h2 class="font-display" style="font-family: var(--font-display); font-size: 22px; font-weight: 700; margin: 0 0 14px 0; text-align: center; color: #1a1a1a; letter-spacing: 0.02em;">
                        Règles de l'Expédition
                    </h2>

                    <!-- Slide 1 : L'Explorateur -->
                    <div x-show="activeSlide === 0" style="text-align: center;">
                        <div style="display: flex; justify-content: center; margin-bottom: 10px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; box-shadow: 0 2px 0 #1A1A1A; display: flex; align-items: center; justify-content: center;">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#841818" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.8"/>
                                    <line x1="8" y1="2" x2="8" y2="18" stroke="#1A1A1A" stroke-width="1.5"/>
                                    <line x1="16" y1="6" x2="16" y2="22" stroke="#1A1A1A" stroke-width="1.5"/>
                                </svg>
                            </div>
                        </div>
                        <span
                            style="display: inline-block; padding: 4px 12px; background: #fef3c7; border: 2px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 700; color: #841818; text-transform: uppercase; margin-bottom: 8px;">
                            Étape 1 sur 3
                        </span>
                        <h3 class="font-display" style="font-family: var(--font-display); margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #841818;">
                            L'Explorateur
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.45;">
                            L'enfant résout les énigmes de Gali sur la carte papier physique avec sa loupe.
                        </p>
                    </div>

                    <!-- Slide 2 : Le Maître du Jeu -->
                    <div x-show="activeSlide === 1" style="text-align: center;">
                        <div style="display: flex; justify-content: center; margin-bottom: 10px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; box-shadow: 0 2px 0 #1A1A1A; display: flex; align-items: center; justify-content: center;">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#841818" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.8"/>
                                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76" fill="#841818" stroke="#1A1A1A" stroke-width="1.2"/>
                                </svg>
                            </div>
                        </div>
                        <span
                            style="display: inline-block; padding: 4px 12px; background: #fef3c7; border: 2px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 700; color: #841818; text-transform: uppercase; margin-bottom: 8px;">
                            Étape 2 sur 3
                        </span>
                        <h3 class="font-display" style="font-family: var(--font-display); margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #841818;">
                            Le Maître du Jeu
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.45;">
                            Le parent s'oriente en ville grâce à la boussole et valide les étapes.
                        </p>
                    </div>

                    <!-- Slide 3 : Le Trésor -->
                    <div x-show="activeSlide === 2" style="text-align: center;">
                        <div style="display: flex; justify-content: center; margin-bottom: 10px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; box-shadow: 0 2px 0 #1A1A1A; display: flex; align-items: center; justify-content: center;">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#841818" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h2" stroke="#1A1A1A" stroke-width="1.8"/>
                                    <path d="M18 9h2a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-2" stroke="#1A1A1A" stroke-width="1.8"/>
                                    <path d="M4 22h16" stroke="#1A1A1A" stroke-width="1.8"/>
                                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22" stroke="#1A1A1A" stroke-width="1.8"/>
                                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22" stroke="#1A1A1A" stroke-width="1.8"/>
                                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" fill="#F4B316" stroke="#1A1A1A" stroke-width="1.8"/>
                                </svg>
                            </div>
                        </div>
                        <span
                            style="display: inline-block; padding: 4px 12px; background: #fef3c7; border: 2px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 700; color: #841818; text-transform: uppercase; margin-bottom: 8px;">
                            Étape 3 sur 3
                        </span>
                        <h3 class="font-display" style="font-family: var(--font-display); margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #841818;">
                            Le Trésor
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.45;">
                            Trouvez les 6 fragments et remportez le jeu Dobble exclusif à l'Office de Tourisme !
                        </p>
                    </div>
                </main>
            </div>

            <!-- Bloc de navigation du slider ancré en bas -->
            <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 10px;">
                <!-- Barre de pagination et flèches (3 colonnes pour centrage absolu des pastilles) -->
                <div style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 8px;">
                    <!-- Bouton Précédent 3D (aligné à gauche) -->
                    <div style="display: flex; justify-content: flex-start;">
                        <button type="button" x-on:click="prevSlide()" :disabled="activeSlide === 0"
                            class="btn-bounce"
                            style="width: 44px; height: 44px; border-radius: 12px; border: 2.5px solid #1A1A1A; background: #FFFFFF; font-weight: 700; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"
                            :style="{ opacity: activeSlide === 0 ? 0.25 : 1, cursor: activeSlide === 0 ? 'not-allowed' : 'pointer', boxShadow: activeSlide === 0 ? 'none' : '0 3px 0 #1A1A1A' }">
                            ←
                        </button>
                    </div>

                    <!-- Indicateurs pastilles Sang et Or (parfaitement au centre) -->
                    <div style="display: flex; gap: 10px; align-items: center; justify-content: center; flex-shrink: 0;">
                        <button type="button" x-on:click="goToSlide(0)"
                            style="display: inline-block; width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; box-sizing: border-box; transition: transform 0.15s ease;"
                            :style="{ background: activeSlide === 0 ? '#F4B316' : '#E5E7EB', transform: activeSlide === 0 ? 'scale(1.25)' : 'scale(1)' }"></button>
                        <button type="button" x-on:click="goToSlide(1)"
                            style="display: inline-block; width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; box-sizing: border-box; transition: transform 0.15s ease;"
                            :style="{ background: activeSlide === 1 ? '#F4B316' : '#E5E7EB', transform: activeSlide === 1 ? 'scale(1.25)' : 'scale(1)' }"></button>
                        <button type="button" x-on:click="goToSlide(2)"
                            style="display: inline-block; width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; box-sizing: border-box; transition: transform 0.15s ease;"
                            :style="{ background: activeSlide === 2 ? '#F4B316' : '#E5E7EB', transform: activeSlide === 2 ? 'scale(1.25)' : 'scale(1)' }"></button>
                    </div>

                    <!-- Bouton Suivant pour slides 0 et 1 (aligné à droite) -->
                    <div style="display: flex; justify-content: flex-end;">
                        <div x-show="activeSlide < totalSlides - 1" style="flex-shrink: 0;">
                            <button type="button" x-on:click="nextSlide()"
                                class="btn-bounce font-display"
                                style="font-family: var(--font-display); padding: 8px 18px; height: 44px; background: #F4B316; color: #1a1a1a; font-weight: 700; font-size: 16px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer; display: flex; align-items: center; justify-content: center; text-transform: uppercase; letter-spacing: 0.03em;">
                                Suivant →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sur slides 0 et 1 : lien passer le tuto -->
                <div x-show="activeSlide < totalSlides - 1" style="text-align: center;">
                    <button type="button" x-on:click="goToSlide(totalSlides - 1)"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        Passer le tutoriel
                    </button>
                </div>

                <!-- Sur slide 2 (dernière slide) : Boutons d'action créer / connecter équipe -->
                <div x-show="activeSlide === totalSlides - 1" style="display: flex; flex-direction: column; gap: 8px;">
                    <button type="button" wire:click="setMode('register')"
                        class="btn-bounce font-display"
                        style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 12px 18px; background: #F4B316; color: #1a1a1a; font-weight: 700; font-size: 18px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        class="btn-bounce font-display"
                        style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 12px 18px; background: #841818; color: #ffffff; font-weight: 700; font-size: 18px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer;">
                        Connexion équipe
                    </button>

                    <div style="text-align: center; margin-top: 2px;">
                        <button type="button" x-on:click="onboardingStep = 'welcome'"
                            style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                            ← Revenir à l'accueil
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @elseif($mode === 'register')
        <!-- CONTENU DU MODE REGISTER (mêmes marges et largeur uniformisées) -->
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0; margin: 12px 0; overflow-y: auto; padding-bottom: 8px; -webkit-overflow-scrolling: touch;">
            <main
                style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 20px 18px; box-sizing: border-box; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <h2 class="font-display" style="font-family: var(--font-display); margin: 0; font-size: 22px; font-weight: 700; color: #841818;">Créer une équipe</h2>
                    <button type="button" wire:click="setMode('landing')"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        ← Retour
                    </button>
                </div>

                <form wire:submit="register" style="display: flex; flex-direction: column; gap: 10px;">
                    <div>
                        <label for="team_name" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 3px;">Nom de l'équipe :</label>
                        <input type="text" id="team_name" wire:model="team_name" placeholder="Ex: Les Explorateurs du Louvre" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 13px;">
                        @error('team_name')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 3px;">Email :</label>
                        <input type="email" id="email" wire:model="email" placeholder="Ex: famille@lens.fr" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 13px;">
                        @error('email')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 3px;">Mot de passe :</label>
                        <input type="password" id="password" wire:model="password" placeholder="Au moins 6 caractères" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 13px;">
                        @error('password')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 3px;">Confirmer le mot de passe :</label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation" placeholder="Retapez le mot de passe" style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 13px;">
                    </div>

                    <button type="submit"
                        class="btn-bounce font-display"
                        style="font-family: var(--font-display); margin-top: 6px; padding: 12px; background: #F4B316; color: #1a1a1a; font-weight: 700; font-size: 18px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 10px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Créer mon équipe et commencer
                    </button>
                </form>

                <p style="text-align: center; margin-top: 10px; margin-bottom: 0; font-size: 13px;">
                    Déjà une équipe ?
                    <button type="button" wire:click="setMode('login')"
                        style="background: none; border: none; color: #841818; font-weight: 700; cursor: pointer; text-decoration: underline;">
                        Connexion
                    </button>
                </p>
            </main>
        </div>

    @elseif($mode === 'login')
        <!-- CONTENU DU MODE LOGIN (mêmes marges et largeur uniformisées) -->
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0; margin: 12px 0; overflow-y: auto; padding-bottom: 8px; -webkit-overflow-scrolling: touch;">
            <main
                style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 20px 18px; box-sizing: border-box; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <h2 class="font-display" style="font-family: var(--font-display); margin: 0; font-size: 22px; font-weight: 700; color: #841818;">Connexion équipe</h2>
                    <button type="button" wire:click="setMode('landing')"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        ← Retour
                    </button>
                </div>

                <form wire:submit="login" style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label for="email"
                            style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 3px;">Email :</label>
                        <input type="email" id="email" wire:model="email" placeholder="Ex: famille@lens.fr"
                            style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('email')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 3px;">Mot de passe :</label>
                        <input type="password" id="password" wire:model="password" placeholder="Votre mot de passe"
                            style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('password')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="btn-bounce font-display"
                        style="font-family: var(--font-display); margin-top: 6px; padding: 12px; background: #841818; color: #ffffff; font-weight: 700; font-size: 18px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 10px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Se connecter
                    </button>
                </form>

                <p style="text-align: center; margin-top: 10px; margin-bottom: 0; font-size: 13px;">
                    Pas encore d'équipe ?
                    <button type="button" wire:click="setMode('register')"
                        style="background: none; border: none; color: #841818; font-weight: 700; cursor: pointer; text-decoration: underline;">
                        Créer une équipe
                    </button>
                </p>
            </main>
        </div>
    @endif
</div>
