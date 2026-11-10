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
    style="width: 100%; max-width: 420px; height: 100vh; height: 100dvh; max-height: 100dvh; margin: 0 auto; padding: 16px; box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif; color: #1a1a1a; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden;">

    <!-- 1. En-tête haut de page -->
    <header style="text-align: center; flex-shrink: 0; padding-top: 2px;">
        <h1
            style="font-size: 26px; font-weight: 900; color: #841818; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px;">
            L'Expédition Secrète
        </h1>
        <p style="margin: 0; font-size: 14px; font-weight: 700; color: #b45309;">
            Lens Tourisme & Asmodee
        </p>
    </header>

    @if($mode === 'landing' || $mode === 'tuto')
        <!-- MODE ACCUEIL (WELCOME) -->
        <div x-show="onboardingStep === 'welcome'" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; min-height: 0; margin: 12px 0;">
            <!-- Carte centrale -->
            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0;">
                <main
                    style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 22px 20px; box-sizing: border-box; text-align: center; width: 100%;">
                    <span class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;">Connexion / Inscription Équipe</span>
                    <h2 style="font-size: 20px; font-weight: 900; margin: 0 0 16px 0; color: #1a1a1a;">
                        Bienvenue, Explorateurs !
                    </h2>

                    <div
                        style="background: #fdfaf2; border: 2.5px dashed #d97706; border-radius: 16px; padding: 18px 14px;">
                        <p style="margin: 0 0 8px 0; font-weight: 900; font-size: 16px; color: #841818;">
                            Prêts à explorer Lens ?
                        </p>
                        <p style="margin: 0; font-size: 13px; color: #4b5563; line-height: 1.45;">
                            Rejoignez la quête historique et découvrez les 6 fragments secrets disséminés dans la ville.
                        </p>
                    </div>
                </main>
            </div>

            <!-- Bloc d'actions ancré en bas -->
            <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 10px;">
                <button type="button" x-on:click="onboardingStep = 'slider'; activeSlide = 0;"
                    class="btn-bounce"
                    style="width: 100%; box-sizing: border-box; padding: 15px 20px; background: #F4B316; color: #1a1a1a; font-weight: 900; font-size: 18px; text-transform: uppercase; border: 3px solid #1a1a1a; border-radius: 14px; box-shadow: 0 5px 0 #1a1a1a; cursor: pointer; letter-spacing: 0.5px;">
                    Démarrer l'aventure
                </button>

                <div style="display: flex; gap: 10px;">
                    <button type="button" wire:click="setMode('register')"
                        class="btn-bounce"
                        style="flex: 1; box-sizing: border-box; padding: 11px 10px; background: #F9FAFB; color: #1a1a1a; font-weight: 800; font-size: 13px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer; text-align: center;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        class="btn-bounce"
                        style="flex: 1; box-sizing: border-box; padding: 11px 10px; background: #F9FAFB; color: #1a1a1a; font-weight: 800; font-size: 13px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer; text-align: center;">
                        Connexion équipe
                    </button>
                </div>

                <p style="margin: 4px 0 0 0; text-align: center; font-size: 11px;">
                    <a href="{{ route('test-qr') }}" target="_blank" style="color: #6b7280; text-decoration: underline;">
                        Tester les QR Codes sur écran PC (/test-qr)
                    </a>
                </p>
            </div>
        </div>

        <!-- MODE TUTORIEL (SLIDER) -->
        <div x-show="onboardingStep === 'slider'" x-cloak style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; min-height: 0; margin: 12px 0;">
            <!-- Carte centrale du slide -->
            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0;">
                <main
                    style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 22px 20px; box-sizing: border-box; width: 100%;">
                    <h2 style="font-size: 20px; font-weight: 900; margin: 0 0 16px 0; text-align: center; color: #1a1a1a;">
                        Règles de l'Expédition
                    </h2>

                    <!-- Slide 1 : L'Explorateur -->
                    <div x-show="activeSlide === 0" style="text-align: center;">
                        <span
                            style="display: inline-block; padding: 4px 12px; background: #fef3c7; border: 2px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 900; color: #841818; text-transform: uppercase; margin-bottom: 12px;">
                            Étape 1 sur 3
                        </span>
                        <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 900; color: #841818;">
                            L'Explorateur
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.5;">
                            L'enfant résout les énigmes sur la carte papier physique avec son filtre optique.
                        </p>
                    </div>

                    <!-- Slide 2 : Le Maître du Jeu -->
                    <div x-show="activeSlide === 1" style="text-align: center;">
                        <span
                            style="display: inline-block; padding: 4px 12px; background: #fef3c7; border: 2px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 900; color: #841818; text-transform: uppercase; margin-bottom: 12px;">
                            Étape 2 sur 3
                        </span>
                        <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 900; color: #841818;">
                            Le Maître du Jeu
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.5;">
                            Le parent s'oriente en ville grâce à la boussole et valide les étapes.
                        </p>
                    </div>

                    <!-- Slide 3 : Le Trésor -->
                    <div x-show="activeSlide === 2" style="text-align: center;">
                        <span
                            style="display: inline-block; padding: 4px 12px; background: #fef3c7; border: 2px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 900; color: #841818; text-transform: uppercase; margin-bottom: 12px;">
                            Étape 3 sur 3
                        </span>
                        <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 900; color: #841818;">
                            Le Trésor
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.5;">
                            Trouvez les 6 fragments et remportez le jeu Dobble exclusif à l'Office de Tourisme !
                        </p>
                    </div>
                </main>
            </div>

            <!-- Bloc de navigation du slider ancré en bas -->
            <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 12px;">
                <!-- Barre de pagination et flèches -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <!-- Bouton Précédent 3D -->
                    <button type="button" x-on:click="prevSlide()" :disabled="activeSlide === 0"
                        class="btn-bounce"
                        style="width: 44px; height: 44px; border-radius: 12px; border: 2.5px solid #1A1A1A; background: #FFFFFF; font-weight: 900; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                        :style="activeSlide === 0 ? 'opacity: 0.25; cursor: not-allowed; box-shadow: none;' : 'box-shadow: 0 3px 0 #1A1A1A;'">
                        ←
                    </button>

                    <!-- Indicateurs pastilles Sang et Or -->
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" x-on:click="goToSlide(0)"
                            style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; transition: transform 0.15s ease;"
                            :style="activeSlide === 0 ? 'background: #F4B316; transform: scale(1.3);' : 'background: #E5E7EB;'"></button>
                        <button type="button" x-on:click="goToSlide(1)"
                            style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; transition: transform 0.15s ease;"
                            :style="activeSlide === 1 ? 'background: #F4B316; transform: scale(1.3);' : 'background: #E5E7EB;'"></button>
                        <button type="button" x-on:click="goToSlide(2)"
                            style="width: 14px; height: 14px; border-radius: 50%; border: 2.5px solid #1A1A1A; cursor: pointer; padding: 0; transition: transform 0.15s ease;"
                            :style="activeSlide === 2 ? 'background: #F4B316; transform: scale(1.3);' : 'background: #E5E7EB;'"></button>
                    </div>

                    <!-- Bouton Suivant pour slides 0 et 1 (sans doublon !) -->
                    <div x-show="activeSlide < totalSlides - 1">
                        <button type="button" x-on:click="nextSlide()"
                            class="btn-bounce"
                            style="padding: 10px 18px; height: 44px; background: #F4B316; color: #1a1a1a; font-weight: 900; font-size: 14px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer; display: flex; align-items: center; justify-content: center; text-transform: uppercase;">
                            Suivant →
                        </button>
                    </div>

                    <!-- Espace réservé sur le dernier slide pour l'équilibre -->
                    <div x-show="activeSlide === totalSlides - 1" style="width: 44px;"></div>
                </div>

                <!-- Sur slides 0 et 1 : lien passer le tuto -->
                <div x-show="activeSlide < totalSlides - 1" style="text-align: center;">
                    <button type="button" x-on:click="goToSlide(totalSlides - 1)"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        Passer le tutoriel
                    </button>
                </div>

                <!-- Sur slide 2 (dernière slide) : Boutons d'action créer / connecter équipe -->
                <div x-show="activeSlide === totalSlides - 1" style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="button" wire:click="setMode('register')"
                        class="btn-bounce"
                        style="width: 100%; box-sizing: border-box; padding: 13px 18px; background: #F4B316; color: #1a1a1a; font-weight: 900; font-size: 15px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        class="btn-bounce"
                        style="width: 100%; box-sizing: border-box; padding: 13px 18px; background: #841818; color: #ffffff; font-weight: 900; font-size: 15px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; cursor: pointer;">
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
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0; margin: 12px 0; overflow-y: auto; -webkit-overflow-scrolling: touch;">
            <main
                style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 22px 20px; box-sizing: border-box; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 900; color: #841818;">Créer une équipe</h2>
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
                        class="btn-bounce"
                        style="margin-top: 6px; padding: 12px; background: #F4B316; color: #1a1a1a; font-weight: 800; font-size: 15px; border: 2.5px solid #1a1a1a; border-radius: 10px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
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
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0; margin: 12px 0; overflow-y: auto; -webkit-overflow-scrolling: touch;">
            <main
                style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 22px 20px; box-sizing: border-box; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 900; color: #841818;">Connexion équipe</h2>
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
                        class="btn-bounce"
                        style="margin-top: 6px; padding: 12px; background: #841818; color: #ffffff; font-weight: 800; font-size: 15px; border: 2.5px solid #1a1a1a; border-radius: 10px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
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
