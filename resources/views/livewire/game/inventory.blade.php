<div
    style="max-width: 480px; margin: 0 auto; padding: 10px 16px 20px 16px; font-family: system-ui, -apple-system, sans-serif; color: #1a1a1a;">
    <!-- Bouton Retour à la boussole en haut à gauche -->
    <div style="margin-bottom: 12px;">
        <button type="button" id="inventory-back-to-hub-btn" x-on:click="tab = 'hub'"
            wire:click="$dispatch('set-tab', { tab: 'hub' })" class="btn-bounce"
            style="background: #FFFFFF; border: 2.5px solid #1A1A1A; border-radius: 12px; padding: 8px 14px; font-size: 13px; font-weight: 800; color: #1A1A1A; box-shadow: 0 3px 0 #1A1A1A; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            ← Retour à la boussole
        </button>
    </div>

    @if($hasWon)
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 24px; text-align: center;">
            <div
                style="display: inline-block; background: #fef3c7; border: 2.5px solid #1a1a1a; border-radius: 9999px; padding: 6px 16px; font-size: 13px; font-weight: 800; color: #841818; margin-bottom: 12px; text-transform: uppercase;">
                Triomphe de l'Expédition
            </div>

            <h2 style="font-size: 26px; font-weight: 900; color: #841818; margin: 0 0 10px 0; text-transform: uppercase;">
                Victoire !
            </h2>

            <p style="font-size: 15px; margin: 0 0 8px 0; color: #374151;">
                Félicitations à l'équipe <strong>{{ $user?->team_name ?? $user?->name ?? 'Équipe' }}</strong> !
            </p>
            <p style="font-size: 14px; margin: 0 0 20px 0; color: #4b5563; line-height: 1.4;">
                Vous avez triomphé de toutes les étapes, aidé Gali à reconstituer sa carte et fait briller à nouveau la Lampe Cœur !
            </p>

            <div
                style="background: #fffbeb; border: 3px solid #1a1a1a; border-radius: 16px; padding: 16px; margin-bottom: 24px; box-shadow: 0 4px 0 #1a1a1a;">
                <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: 700; color: #92400e; text-transform: uppercase;">
                    Code Cadenas Coffre
                </p>
                <h3 style="font-size: 38px; font-weight: 900; color: #841818; margin: 0; letter-spacing: 4px;">
                    Code final : {{ $finalCode }}
                </h3>
                <p style="margin: 8px 0 0 0; font-size: 12px; color: #78350f;">
                    Rendez-vous à l'Office de Tourisme (Destination Lens) pour ouvrir le cadenas et récupérer votre
                    récompense Asmodee !
                </p>
            </div>

            <div
                style="background: #fdfaf2; border: 3px solid #1a1a1a; border-radius: 16px; padding: 20px; margin-bottom: 24px; box-shadow: 0 4px 0 #1a1a1a;">
                <h4 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 800; color: #841818;">
                    QR Code Retrait du Lot Dobble
                </h4>
                <p style="font-size: 13px; color: #4b5563; margin: 0 0 14px 0; line-height: 1.4;">
                    Présentez ce QR Code à l'accueil de l'Office de Tourisme pour débloquer votre boîte de jeu Dobble
                    collector.
                </p>

                @php
                    $token = $rewardToken ?: ($user?->reward_token ?? 'EXPEDITION_LENS_REWARD_PENDING');
                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=10&data=' . urlencode('EXPEDITION_LENS_REWARD_' . $token);
                @endphp

                <div
                    style="display: inline-block; background: #ffffff; padding: 12px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 3px 0 #1a1a1a; margin-bottom: 14px;">
                    <img src="{{ $qrUrl }}" alt="QR Code Retrait Lot" width="180" height="180"
                        style="display: block; max-width: 100%; height: auto;">
                    <div
                        style="font-family: monospace; font-size: 10px; font-weight: 700; color: #6b7280; margin-top: 6px; word-break: break-all;">
                        Token : {{ $token }}
                    </div>
                </div>

                <div style="margin-top: 6px;">
                    @if($rewardPicked)
                        <div
                            style="background: #dcfce7; border: 2px solid #16a34a; border-radius: 10px; padding: 10px; color: #166534; font-weight: 800; font-size: 14px;">
                            Statut : Lot déjà remis — Merci d'avoir joué !
                        </div>
                    @else
                        <div
                            style="background: #fef3c7; border: 2px solid #d97706; border-radius: 10px; padding: 10px; color: #92400e; font-weight: 800; font-size: 14px;">
                            Statut : Lot en attente de retrait
                        </div>
                    @endif
                </div>
            </div>

            <div
                style="text-align: left; background: #f9fafb; border: 2.5px solid #1a1a1a; border-radius: 14px; padding: 16px;">
                <h3 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 800; color: #1a1a1a;">
                    Fragments et indices récoltés (6/6) :
                </h3>
                <ul style="margin: 0; padding-left: 20px; font-size: 13px; line-height: 1.6; color: #374151;">
                    @foreach($fragments as $fragment)
                        <li style="margin-bottom: 4px;">
                            Étape {{ $fragment->step->id }} : {{ preg_replace('/^[EÉeé]tape\s*\d+\s*:\s*/iu', '', $fragment->step->name) }} — Fragment :
                            <strong>{{ $fragment->step->code_part }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @else
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; box-shadow: 0 6px 0 #1a1a1a; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <h2 style="font-size: 20px; font-weight: 900; color: #841818; margin: 0; text-transform: uppercase;">
                    Sac à Dos &amp; Inventaire
                </h2>
                <span
                    style="background: #841818; color: #F4B316; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 800; border: 2px solid #1a1a1a;">
                    {{ $fragments->count() }} / 6
                </span>
            </div>

            <p style="font-size: 14px; color: #4b5563; margin: 0 0 16px 0;">
                Progression : {{ $fragments->count() }} / 6 fragments débloqués
            </p>

            @if($fragments->isEmpty())
                <div
                    style="background: #fdfaf2; border: 2.5px dashed #d97706; border-radius: 12px; padding: 16px; text-align: center; color: #78350f; font-size: 13px; line-height: 1.5;">
                    <p style="margin: 0;">Aucun fragment débloqué pour le moment. Résolvez la première énigme de Gali avec la loupe et trouvez le Maître du Jeu !</p>
                </div>
            @else
                <ul style="margin: 0 0 16px 0; padding-left: 20px; font-size: 13px; line-height: 1.6; color: #374151;">
                    @foreach($fragments as $fragment)
                        <li style="margin-bottom: 6px;">
                            Étape {{ $fragment->step->id }} : {{ preg_replace('/^[EÉeé]tape\s*\d+\s*:\s*/iu', '', $fragment->step->name) }} — Fragment :
                            <strong>{{ $fragment->step->code_part }}</strong>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div style="margin-top: 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                @for($i = 1; $i <= 6; $i++)
                    @php
                        $unlocked = $fragments->firstWhere('step_id', $i);
                    @endphp
                    <div
                        style="border: 2px solid #1a1a1a; border-radius: 10px; padding: 10px 6px; text-align: center; background: {{ $unlocked ? '#fef3c7' : '#f3f4f6' }}; box-shadow: 0 2px 0 #1a1a1a;">
                        <div style="font-size: 11px; font-weight: 700; color: #6b7280;">Slot {{ $i }}</div>
                        <div
                            style="font-size: 13px; font-weight: 900; color: {{ $unlocked ? '#841818' : '#9ca3af' }}; margin-top: 4px;">
                            {{ $unlocked ? ($unlocked->step->code_part ?? 'OK') : '—' }}
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @endif
</div>