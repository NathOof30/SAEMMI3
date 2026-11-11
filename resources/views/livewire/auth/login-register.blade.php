<div x-data="{
    onboardingStep: '{{ $mode === 'tuto' ? 'slider' : 'welcome' }}',
    activeSlide: 0,
    totalSlides: 4,
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
    style="width: 100%; max-width: 420px; height: 100vh; height: 100dvh; max-height: 100dvh; margin: 0 auto; padding: clamp(18px, 4.2dvh, 42px) 18px clamp(16px, 3.2dvh, 32px) 18px; box-sizing: border-box; font-family: var(--font-body); color: #1a1a1a; display: flex; flex-direction: column; justify-content: space-between; gap: clamp(10px, 2dvh, 22px); overflow: hidden;">

    <!-- 1. En-tête haut de page -->
    <header style="text-align: center; flex-shrink: 0; padding-top: 2px;">
        <h1 class="font-display"
            style="font-family: var(--font-display); font-size: clamp(27px, 6.8vw, 33px); font-weight: 700; color: #841818; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.04em; line-height: 1.05;">
            L'Expédition Secrète
        </h1>
        <p style="margin: 0; font-size: 13px; font-weight: 700; color: #b45309;">
            Lens Tourisme & Asmodee
        </p>
    </header>

    @if($mode === 'landing' || $mode === 'tuto')
        <!-- MODE ACCUEIL (WELCOME) -->
        <div x-show="onboardingStep === 'welcome'" class="alpine-flex" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: clamp(10px, 2dvh, 22px); min-height: 0;">
            <!-- Carte centrale -->
            <div style="flex: 1; display: flex; flex-direction: column; min-height: 0; overflow-y: auto; -webkit-overflow-scrolling: touch; padding: 2px 2px 8px 2px;">
                <main
                    style="margin: auto 0; background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: clamp(14px, 2.8dvh, 26px) 16px; box-sizing: border-box; text-align: center; width: 100%;">
                    <span class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;">Connexion / Inscription Équipe</span>

                    <!-- Emblème de la Lampe Cœur (Niveau 2 : contour seul sans ombre portée) -->
                    <div style="display: flex; justify-content: center; margin-bottom: clamp(8px, 1.4dvh, 12px);">
                        <div style="width: clamp(42px, 6dvh, 52px); height: clamp(42px, 6dvh, 52px); border-radius: 50%; background: #FEF3C7; border: 2px solid #1A1A1A; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 32 32" width="26" height="26" fill="none" aria-hidden="true">
                                <path d="M11 6 H21" stroke="#1A1A1A" stroke-width="2.4" stroke-linecap="round"/>
                                <path d="M13 6 V9 H19 V6" stroke="#1A1A1A" stroke-width="2" fill="#9CA3AF"/>
                                <rect x="9" y="9" width="14" height="14" rx="3" fill="#FFFBEB" stroke="#1A1A1A" stroke-width="2.2"/>
                                <path d="M16 20.5 C16 20.5 11.5 17.5 11.5 14.3 C11.5 12.8 12.7 11.8 14 11.8 C14.9 11.8 15.6 12.3 16 13 C16.4 12.3 17.1 11.8 18 11.8 C19.3 11.8 20.5 12.8 20.5 14.3 C20.5 17.5 16 20.5 16 20.5 Z" fill="#F4B316" stroke="#841818" stroke-width="1.5"/>
                                <rect x="8" y="23" width="16" height="4" rx="1.5" fill="#841818" stroke="#1A1A1A" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>

                    <h2 class="font-display" style="font-family: var(--font-display); font-size: clamp(21px, 3.5dvh, 24px); font-weight: 700; margin: 0 0 clamp(8px, 1.4dvh, 12px) 0; color: #1a1a1a; letter-spacing: 0.02em; line-height: 1.1;">
                        Bienvenue, Explorateurs !
                    </h2>

                    <div
                        style="background: #fdfaf2; border: 2px dashed #d97706; border-radius: 16px; padding: clamp(11px, 2dvh, 18px) 13px; text-align: left;">
                        <p class="font-display" style="font-family: var(--font-display); margin: 0 0 clamp(6px, 1dvh, 8px) 0; font-weight: 700; font-size: 17px; color: #841818; text-align: center; letter-spacing: 0.02em;">
                            La légende de la Lampe Cœur
                        </p>
                        <p style="margin: 0 0 clamp(6px, 1dvh, 8px) 0; font-size: 13px; color: #374151; line-height: 1.4;">
                            Dans le <strong>grenier</strong> de son <strong>grand-père</strong>, sous une veste de mineur, <strong>Gali</strong> a découvert un bout de <strong>papier jauni</strong> menant à la <strong>Lampe Cœur</strong>, la <strong>gaillette de charbon magique</strong> qui illumine Lens !
                        </p>
                        <p style="margin: 0 0 clamp(6px, 1dvh, 8px) 0; font-size: 13px; color: #374151; line-height: 1.4;">
                            Mais Gali n'a trouvé qu'un <strong>seul morceau</strong> de la carte : impossible de localiser la Lampe Cœur et de la rallumer avec un bout incomplet !
                        </p>
                        <p style="margin: 0; font-size: 12.5px; font-weight: 700; color: #92400e; text-align: center; line-height: 1.38;">
                            Pour que l'histoire de Lens brille à nouveau, partez en ville avec votre kit retrouver les <strong>6 morceaux éparpillés</strong> et reconstituer la carte jusqu'au coffre final. Au dos l'attend le premier indice !
                        </p>
                    </div>
                </main>
            </div>

            <!-- Bloc d'actions ancré en bas -->
            <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: clamp(8px, 1.4dvh, 10px);">
                <button type="button" x-on:click="onboardingStep = 'slider'; activeSlide = 0;"
                    class="btn-bounce font-display"
                    style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: clamp(12px, 2dvh, 15px) 18px; background: #F4B316; color: #1a1a1a; font-weight: 700; font-size: clamp(19px, 3dvh, 21px); text-transform: uppercase; border: 3px solid #1a1a1a; border-radius: 14px; box-shadow: 0 5px 0 #1a1a1a; cursor: pointer; letter-spacing: 0.04em;">
                    Démarrer l'aventure
                </button>

                <div style="display: flex; gap: 10px;">
                    <button type="button" wire:click="setMode('register')"
                        class="btn-bounce"
                        style="flex: 1; box-sizing: border-box; padding: clamp(9px, 1.5dvh, 11px) 10px; background: #F9FAFB; color: #1a1a1a; font-weight: 700; font-size: 13px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer; text-align: center;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        class="btn-bounce"
                        style="flex: 1; box-sizing: border-box; padding: clamp(9px, 1.5dvh, 11px) 10px; background: #F9FAFB; color: #1a1a1a; font-weight: 700; font-size: 13px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer; text-align: center;">
                        Connexion équipe
                    </button>
                </div>

                <p style="margin: 0; text-align: center; font-size: 11px;">
                    <a href="{{ route('test-qr') }}" target="_blank" style="color: #6b7280; text-decoration: underline;">
                        Tester les QR Codes sur écran PC (/test-qr)
                    </a>
                </p>
            </div>
        </div>

        <!-- MODE TUTORIEL (SLIDER) -->
        <div x-show="onboardingStep === 'slider'" x-cloak class="alpine-flex" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: clamp(10px, 2dvh, 20px); min-height: 0;">
            <!-- Carte centrale du slide -->
            <div style="flex: 1; display: flex; flex-direction: column; min-height: 0; overflow-y: auto; -webkit-overflow-scrolling: touch; padding: 2px 2px 8px 2px;">
                <main
                    style="margin: auto 0; background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: clamp(20px, 3dvh, 26px) 18px; box-sizing: border-box; width: 100%;">
                    <h2 class="font-display" style="font-family: var(--font-display); font-size: 22px; font-weight: 700; margin: 0 0 14px 0; text-align: center; color: #1a1a1a; letter-spacing: 0.02em;">
                        Règles de l'Expédition
                    </h2>

                    <x-tutorial-slides slide-var="activeSlide" />
                </main>
            </div>

            <!-- Bloc de navigation du slider ancré en bas -->
            <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 12px;">
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
                        <button type="button" x-on:click="goToSlide(0)" aria-label="Étape 1"
                            style="display: inline-block; width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; box-sizing: border-box; transition: transform 0.15s ease;"
                            :style="{ background: activeSlide === 0 ? '#F4B316' : '#E5E7EB', transform: activeSlide === 0 ? 'scale(1.25)' : 'scale(1)' }"></button>
                        <button type="button" x-on:click="goToSlide(1)" aria-label="Étape 2"
                            style="display: inline-block; width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; box-sizing: border-box; transition: transform 0.15s ease;"
                            :style="{ background: activeSlide === 1 ? '#F4B316' : '#E5E7EB', transform: activeSlide === 1 ? 'scale(1.25)' : 'scale(1)' }"></button>
                        <button type="button" x-on:click="goToSlide(2)" aria-label="Étape 3"
                            style="display: inline-block; width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; box-sizing: border-box; transition: transform 0.15s ease;"
                            :style="{ background: activeSlide === 2 ? '#F4B316' : '#E5E7EB', transform: activeSlide === 2 ? 'scale(1.25)' : 'scale(1)' }"></button>
                        <button type="button" x-on:click="goToSlide(3)" aria-label="Étape 4"
                            style="display: inline-block; width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; box-sizing: border-box; transition: transform 0.15s ease;"
                            :style="{ background: activeSlide === 3 ? '#F4B316' : '#E5E7EB', transform: activeSlide === 3 ? 'scale(1.25)' : 'scale(1)' }"></button>
                    </div>

                    <!-- Bouton Suivant pour slides 0 à 2 (aligné à droite) -->
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

                <!-- Sur slides 0 à 2 : lien passer le tuto -->
                <div x-show="activeSlide < totalSlides - 1" style="text-align: center;">
                    <button type="button" x-on:click="goToSlide(totalSlides - 1)"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        Passer le tutoriel
                    </button>
                </div>

                <!-- Sur slide 3 (dernière slide) : Boutons d'action créer / connecter équipe -->
                <div x-show="activeSlide === totalSlides - 1" class="alpine-flex" style="display: flex; flex-direction: column; gap: clamp(8px, 1.4dvh, 10px);">
                    <button type="button" wire:click="setMode('register')"
                        class="btn-bounce font-display"
                        style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 13px 18px; background: #F4B316; color: #1a1a1a; font-weight: 700; font-size: 18px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        class="btn-bounce font-display"
                        style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 13px 18px; background: #841818; color: #ffffff; font-weight: 700; font-size: 18px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer;">
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
        <div style="flex: 1; display: flex; flex-direction: column; min-height: 0; margin: 12px 0; overflow-y: auto; padding: 4px 2px 10px 2px; -webkit-overflow-scrolling: touch;">
            <main
                style="margin: auto 0; background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 24px 20px; box-sizing: border-box; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h2 class="font-display" style="font-family: var(--font-display); margin: 0; font-size: 22px; font-weight: 700; color: #841818;">Créer une équipe</h2>
                    <button type="button" wire:click="setMode('landing')"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        ← Retour
                    </button>
                </div>

                <form wire:submit="register" style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label for="team_name" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 4px;">Nom de l'équipe :</label>
                        <input type="text" id="team_name" wire:model="team_name" placeholder="Ex: Les Explorateurs du Louvre" style="width: 100%; box-sizing: border-box; padding: 9px 11px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 13px;">
                        @error('team_name')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 4px;">Email :</label>
                        <input type="email" id="email" wire:model="email" placeholder="Ex: famille@lens.fr" style="width: 100%; box-sizing: border-box; padding: 9px 11px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 13px;">
                        @error('email')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 4px;">Mot de passe :</label>
                        <input type="password" id="password" wire:model="password" placeholder="Au moins 6 caractères" style="width: 100%; box-sizing: border-box; padding: 9px 11px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 13px;">
                        @error('password')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 4px;">Confirmer le mot de passe :</label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation" placeholder="Retapez le mot de passe" style="width: 100%; box-sizing: border-box; padding: 9px 11px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 13px;">
                    </div>

                    <button type="submit"
                        class="btn-bounce font-display"
                        style="font-family: var(--font-display); margin-top: 6px; padding: 13px; background: #F4B316; color: #1a1a1a; font-weight: 700; font-size: 18px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 10px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Créer mon équipe et commencer
                    </button>
                </form>

                <p style="text-align: center; margin-top: 12px; margin-bottom: 0; font-size: 13px;">
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
        <div style="flex: 1; display: flex; flex-direction: column; min-height: 0; margin: 12px 0; overflow-y: auto; padding: 4px 2px 10px 2px; -webkit-overflow-scrolling: touch;">
            <main
                style="margin: auto 0; background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 24px 20px; box-sizing: border-box; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h2 class="font-display" style="font-family: var(--font-display); margin: 0; font-size: 22px; font-weight: 700; color: #841818;">Connexion équipe</h2>
                    <button type="button" wire:click="setMode('landing')"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        ← Retour
                    </button>
                </div>

                <form wire:submit="login" style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label for="email"
                            style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 4px;">Email :</label>
                        <input type="email" id="email" wire:model="email" placeholder="Ex: famille@lens.fr"
                            style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('email')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" style="display: block; font-weight: 700; font-size: 12px; margin-bottom: 4px;">Mot de passe :</label>
                        <input type="password" id="password" wire:model="password" placeholder="Votre mot de passe"
                            style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('password')
                            <p role="alert" style="color: #dc2626; font-size: 11px; margin: 3px 0 0 0; font-weight: 600;">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="btn-bounce font-display"
                        style="font-family: var(--font-display); margin-top: 6px; padding: 13px; background: #841818; color: #ffffff; font-weight: 700; font-size: 18px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 10px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Se connecter
                    </button>
                </form>

                <p style="text-align: center; margin-top: 12px; margin-bottom: 0; font-size: 13px;">
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
