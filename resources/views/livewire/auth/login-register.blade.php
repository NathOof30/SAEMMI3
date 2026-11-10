<div x-data="{
    onboardingStep: 'welcome',
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
    style="max-width: 480px; margin: 0 auto; padding: 16px; font-family: system-ui, -apple-system, sans-serif; color: #1a1a1a;">
    <header style="text-align: center; margin-bottom: 24px;">
        <h1
            style="font-size: 28px; font-weight: 900; color: #841818; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 0.5px;">
            L'Expédition Secrète
        </h1>
        <p style="margin: 0; font-size: 14px; font-weight: 700; color: #b45309;">
            Lens Tourisme & Asmodee
        </p>
    </header>

    <main
        style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 22px;">
        <h2
            style="font-size: 20px; font-weight: 800; margin-top: 0; margin-bottom: 18px; text-align: center; color: #1a1a1a;">
            <span x-show="onboardingStep === 'welcome'">Bienvenue, Explorateurs !</span>
            <span x-show="onboardingStep === 'slider'" x-cloak>Règles de l'Expédition</span>
        </h2>
        <span class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;">Connexion / Inscription Équipe</span>

        @if($mode === 'landing')
            <!-- Étape 1 : Accueil pur -->
            <div x-show="onboardingStep === 'welcome'">
                <div
                    style="background: #fdfaf2; border: 2.5px dashed #d97706; border-radius: 14px; padding: 18px; margin-bottom: 20px; text-align: center;">
                    <p style="margin: 0 0 8px 0; font-weight: 800; font-size: 16px; color: #841818;">
                        Prêts à explorer Lens ?
                    </p>
                    <p style="margin: 0; font-size: 13px; color: #4b5563; line-height: 1.45;">
                        Rejoignez la quête historique et découvrez les 6 fragments secrets disséminés dans la ville.
                    </p>
                </div>

                <div style="text-align: center; margin-bottom: 16px;">
                    <button type="button" x-on:click="onboardingStep = 'slider'; activeSlide = 0;"
                        class="btn-bounce"
                        style="width: 100%; padding: 16px 20px; background: #F4B316; color: #1a1a1a; font-weight: 900; font-size: 18px; text-transform: uppercase; border: 3px solid #1a1a1a; border-radius: 14px; box-shadow: 0 5px 0 #1a1a1a; cursor: pointer; letter-spacing: 0.5px;">
                        Démarrer l'aventure
                    </button>
                </div>

                <!-- Boutons d'accès direct pour les équipes régulières -->
                <div style="display: flex; gap: 10px;">
                    <button type="button" wire:click="setMode('register')"
                        style="flex: 1; padding: 10px; background: #F9FAFB; color: #1a1a1a; font-weight: 800; font-size: 13px; border: 2px solid #1a1a1a; border-radius: 10px; box-shadow: 0 2px 0 #1a1a1a; cursor: pointer;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        style="flex: 1; padding: 10px; background: #F9FAFB; color: #1a1a1a; font-weight: 800; font-size: 13px; border: 2px solid #1a1a1a; border-radius: 10px; box-shadow: 0 2px 0 #1a1a1a; cursor: pointer;">
                        Connexion équipe
                    </button>
                </div>
            </div>

            <!-- Étape 2 : Slider Tutoriel 3 étapes (sans emojis) -->
            <div x-show="onboardingStep === 'slider'" x-cloak>
                <div
                    style="position: relative; min-height: 175px; background: #FDFDFC; border: 2.5px solid #1A1A1A; border-radius: 16px; padding: 18px; margin-bottom: 18px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);">
                    <!-- Slide 1 : L'Explorateur -->
                    <div x-show="activeSlide === 0" style="text-align: center;">
                        <span
                            style="display: inline-block; padding: 3px 10px; background: #fef3c7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; margin-bottom: 10px;">
                            Étape 1 sur 3
                        </span>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 900; color: #841818;">
                            L'Explorateur
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.5;">
                            L'enfant résout les énigmes sur la carte papier physique avec son filtre optique.
                        </p>
                    </div>

                    <!-- Slide 2 : Le Maître du Jeu -->
                    <div x-show="activeSlide === 1" style="text-align: center;">
                        <span
                            style="display: inline-block; padding: 3px 10px; background: #fef3c7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; margin-bottom: 10px;">
                            Étape 2 sur 3
                        </span>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 900; color: #841818;">
                            Le Maître du Jeu
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.5;">
                            Le parent s'oriente en ville grâce à la boussole et valide les étapes.
                        </p>
                    </div>

                    <!-- Slide 3 : Le Trésor -->
                    <div x-show="activeSlide === 2" style="text-align: center;">
                        <span
                            style="display: inline-block; padding: 3px 10px; background: #fef3c7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; margin-bottom: 10px;">
                            Étape 3 sur 3
                        </span>
                        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 900; color: #841818;">
                            Le Trésor
                        </h3>
                        <p style="margin: 0; font-size: 14px; color: #374151; line-height: 1.5;">
                            Trouvez les 6 fragments et remportez le jeu Dobble exclusif à l'Office de Tourisme !
                        </p>
                    </div>
                </div>

                <!-- Contrôles : Flèches Gauche / Droite + Indicateurs ronds de pagination -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <button type="button" x-on:click="prevSlide()" :disabled="activeSlide === 0"
                        style="width: 40px; height: 40px; border-radius: 10px; border: 2.5px solid #1A1A1A; background: #FFFFFF; font-weight: 900; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                        :style="activeSlide === 0 ? 'opacity: 0.3; cursor: not-allowed;' : 'box-shadow: 0 3px 0 #1A1A1A;'">
                        ←
                    </button>

                    <!-- Indicateurs ronds de pagination -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" x-on:click="goToSlide(0)"
                            style="width: 12px; height: 12px; border-radius: 50%; border: 2px solid #1A1A1A; cursor: pointer; padding: 0;"
                            :style="activeSlide === 0 ? 'background: #F4B316; transform: scale(1.2);' : 'background: #D1D5DB;'"></button>
                        <button type="button" x-on:click="goToSlide(1)"
                            style="width: 12px; height: 12px; border-radius: 50%; border: 2px solid #1A1A1A; cursor: pointer; padding: 0;"
                            :style="activeSlide === 1 ? 'background: #F4B316; transform: scale(1.2);' : 'background: #D1D5DB;'"></button>
                        <button type="button" x-on:click="goToSlide(2)"
                            style="width: 12px; height: 12px; border-radius: 50%; border: 2px solid #1A1A1A; cursor: pointer; padding: 0;"
                            :style="activeSlide === 2 ? 'background: #F4B316; transform: scale(1.2);' : 'background: #D1D5DB;'"></button>
                    </div>

                    <button type="button" x-on:click="nextSlide()" :disabled="activeSlide === totalSlides - 1"
                        style="width: 40px; height: 40px; border-radius: 10px; border: 2.5px solid #1A1A1A; background: #FFFFFF; font-weight: 900; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                        :style="activeSlide === totalSlides - 1 ? 'opacity: 0.3; cursor: not-allowed;' : 'box-shadow: 0 3px 0 #1A1A1A;'">
                        →
                    </button>
                </div>

                <!-- Navigation intermédiaire (slides 1 et 2) -->
                <div x-show="activeSlide < totalSlides - 1" style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="button" x-on:click="nextSlide()"
                        class="btn-bounce"
                        style="width: 100%; padding: 14px 18px; background: #F4B316; color: #1a1a1a; font-weight: 900; font-size: 16px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Suivant →
                    </button>
                    <div style="text-align: center; margin-top: 4px;">
                        <button type="button" x-on:click="goToSlide(totalSlides - 1)"
                            style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                            Passer le tutoriel
                        </button>
                    </div>
                </div>

                <!-- Étape 3 : Boutons d'action finale (En fin de slider, slide 3) -->
                <div x-show="activeSlide === totalSlides - 1" style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="button" wire:click="setMode('register')"
                        class="btn-bounce"
                        style="width: 100%; padding: 14px 18px; background: #F4B316; color: #1a1a1a; font-weight: 900; font-size: 16px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        class="btn-bounce"
                        style="width: 100%; padding: 14px 18px; background: #841818; color: #ffffff; font-weight: 900; font-size: 16px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Connexion équipe
                    </button>

                    <div style="text-align: center; margin-top: 4px;">
                        <button type="button" x-on:click="onboardingStep = 'welcome'"
                            style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                            ← Revenir à l'accueil
                        </button>
                    </div>
                </div>
            </div>
        @elseif($mode === 'register')
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #841818;">Créer une équipe</h3>
                    <button type="button" wire:click="setMode('landing')"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        ← Retour
                    </button>
                </div>

                <form wire:submit="register" style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label for="team_name" style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px;">Nom de l'équipe :</label>
                        <input type="text" id="team_name" wire:model="team_name" placeholder="Ex: Les Explorateurs du Louvre" style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('team_name')
                            <p role="alert" style="color: #dc2626; font-size: 12px; margin: 4px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px;">Email :</label>
                        <input type="email" id="email" wire:model="email" placeholder="Ex: famille@lens.fr" style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('email')
                            <p role="alert" style="color: #dc2626; font-size: 12px; margin: 4px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px;">Mot de passe :</label>
                        <input type="password" id="password" wire:model="password" placeholder="Au moins 6 caractères" style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('password')
                            <p role="alert" style="color: #dc2626; font-size: 12px; margin: 4px 0 0 0; font-weight: 600;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px;">Confirmer le mot de passe :</label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation" placeholder="Retapez le mot de passe" style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                    </div>

                    <button type="submit"
                        class="btn-bounce"
                        style="margin-top: 6px; padding: 13px; background: #F4B316; color: #1a1a1a; font-weight: 800; font-size: 15px; border: 2.5px solid #1a1a1a; border-radius: 10px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Créer mon équipe et commencer
                    </button>
                </form>

                <p style="text-align: center; margin-top: 14px; font-size: 13px;">
                    Déjà une équipe ?
                    <button type="button" wire:click="setMode('login')"
                        style="background: none; border: none; color: #841818; font-weight: 700; cursor: pointer; text-decoration: underline;">
                        Connexion
                    </button>
                </p>
            </div>
        @elseif($mode === 'login')
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #841818;">Connexion équipe</h3>
                    <button type="button" wire:click="setMode('landing')"
                        style="background: transparent; border: none; color: #6b7280; font-weight: 700; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        ← Retour
                    </button>
                </div>

                <form wire:submit="login" style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label for="email"
                            style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px;">Email :</label>
                        <input type="email" id="email" wire:model="email" placeholder="Ex: famille@lens.fr"
                            style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('email')
                            <p role="alert" style="color: #dc2626; font-size: 12px; margin: 4px 0 0 0; font-weight: 600;">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" style="display: block; font-weight: 700; font-size: 13px; margin-bottom: 4px;">Mot de passe :</label>
                        <input type="password" id="password" wire:model="password" placeholder="Votre mot de passe"
                            style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 2px solid #1a1a1a; border-radius: 8px; font-size: 14px;">
                        @error('password')
                            <p role="alert" style="color: #dc2626; font-size: 12px; margin: 4px 0 0 0; font-weight: 600;">
                                {{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="btn-bounce"
                        style="margin-top: 6px; padding: 13px; background: #841818; color: #ffffff; font-weight: 800; font-size: 15px; border: 2.5px solid #1a1a1a; border-radius: 10px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Se connecter
                    </button>
                </form>

                <p style="text-align: center; margin-top: 14px; font-size: 13px;">
                    Pas encore d'équipe ?
                    <button type="button" wire:click="setMode('register')"
                        style="background: none; border: none; color: #841818; font-weight: 700; cursor: pointer; text-decoration: underline;">
                        Créer une équipe
                    </button>
                </p>
            </div>
        @endif

        <p style="margin-top: 24px; text-align: center; font-size: 12px;">
            <a href="{{ route('test-qr') }}" target="_blank" style="color: #6b7280; text-decoration: underline;">
                Tester les QR Codes sur écran PC (/test-qr)
            </a>
        </p>
    </main>
</div>
