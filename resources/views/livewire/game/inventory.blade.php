<div>
    @if($hasWon)
        <div>
            <h2>Victoire !</h2>
            <p>Félicitations à l'équipe <strong>{{ $user?->team_name ?? $user?->name ?? 'Équipe' }}</strong> !</p>
            <p>Vous avez triomphé de toutes les étapes et reconstitué le trésor de Lens.</p>
            <p>Voici le code final à 4 chiffres pour ouvrir le cadenas à l'Office de Tourisme :</p>
            <h3>Code final : {{ $finalCode }}</h3>
            <p>Rendez-vous à l'Office de Tourisme (Destination Lens) pour ouvrir le cadenas et récupérer votre récompense Asmodee !</p>

            <h3>Fragments et indices récoltés (6/6) :</h3>
            <ul>
                @foreach($fragments as $fragment)
                    <li>
                        Étape {{ $fragment->step->id }} : {{ $fragment->step->name }} — Fragment : <strong>{{ $fragment->step->code_part }}</strong>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div>
            <h2>Inventaire des Fragments</h2>
            <p>Progression : {{ $fragments->count() }} / 6 fragments débloqués</p>

            @if($fragments->isEmpty())
                <p>Aucun fragment débloqué pour le moment. Résolvez la première énigme et trouvez le Maître du Jeu !</p>
            @else
                <ul>
                    @foreach($fragments as $fragment)
                        <li>
                            Étape {{ $fragment->step->id }} : {{ $fragment->step->name }} — Fragment : <strong>{{ $fragment->step->code_part }}</strong>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</div>
