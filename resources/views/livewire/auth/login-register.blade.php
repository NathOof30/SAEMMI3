<div
    style="max-width: 480px; margin: 0 auto; padding: 16px; font-family: system-ui, -apple-system, sans-serif; color: #1a1a1a;">
    <header style="text-align: center; margin-bottom: 24px;">
        <h1
            style="font-size: 28px; font-weight: 900; color: #841818; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 0.5px;">
            L'Expédition Secrète
        </h1>
        <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #b45309;">
            Destination Lens Tourisme & Asmodee
        </p>
        <p
            style="background: #fef3c7; border: 1.5px solid #f59e0b; border-radius: 8px; padding: 8px 12px; font-size: 12px; line-height: 1.4; margin: 0;">
            <small>Application réservée au <strong>Parent (Maître du Jeu)</strong>. L'Enfant utilise le kit physique
                (carte papier, carnet, filtre optique).</small>
        </p>
    </header>

    <main
        style="background: #ffffff; border: 3px solid #1a1a1a; border-radius: 18px; box-shadow: 0 6px 0 #1a1a1a; padding: 20px;">
        <h2
            style="font-size: 20px; font-weight: 800; margin-top: 0; margin-bottom: 16px; text-align: center; color: #1a1a1a;">
            Connexion / Inscription Équipe
        </h2>

        @if($mode === 'landing')
            <div style="text-align: center;">
                <div
                    style="background: #fdfaf2; border: 2px dashed #d97706; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <p style="margin: 0 0 8px 0; font-weight: 700; font-size: 16px; color: #841818;">
                        Prêts à explorer Lens ?
                    </p>
                    <p style="margin: 0; font-size: 13px; color: #4b5563; line-height: 1.4;">
                        Rejoignez la quête historique et découvrez les 6 fragments secrets disséminés dans la ville.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <button type="button" wire:click="setMode('register')"
                        style="width: 100%; padding: 14px 18px; background: #F4B316; color: #1a1a1a; font-weight: 800; font-size: 16px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Créer une équipe
                    </button>

                    <button type="button" wire:click="setMode('login')"
                        style="width: 100%; padding: 14px 18px; background: #841818; color: #ffffff; font-weight: 800; font-size: 16px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                        Connexion équipe
                    </button>
                </div>

                <div style="margin-top: 18px;">
                    <span
                        style="display: inline-block; padding: 4px 10px; background: #f3f4f6; border-radius: 9999px; font-size: 12px; font-weight: 700; color: #4b5563;">
                        Démarrer l'aventure
                    </span>
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