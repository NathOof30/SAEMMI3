<div>
    <header>
        <h1>L'Expédition Secrète</h1>
        <p>Jeu de piste hybride — Destination Lens Tourisme & Asmodee</p>
        <p><small>Application réservée au Parent (Maître du Jeu). L'Enfant utilise le kit physique (carte papier, carnet, filtre optique).</small></p>
    </header>

    <main>
        <h2>Connexion / Inscription Équipe</h2>
        <form wire:submit="login">
            <div>
                <label for="team_name">Nom de l'équipe :</label>
                <input type="text" id="team_name" wire:model="team_name" placeholder="Ex: Les Explorateurs du Louvre">
                @error('team_name')
                    <p role="alert"><strong>Erreur :</strong> {{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" wire:model="email" placeholder="Ex: famille@lens.fr">
                @error('email')
                    <p role="alert"><strong>Erreur :</strong> {{ $message }}</p>
                @enderror
            </div>

            <button type="submit">Rejoindre l'aventure</button>
        </form>
    </main>
</div>

