<div id="debug-demo-progression-section"
    style="margin-bottom: 12px; background: #F4EBD9; border: 2px solid #1A1A1A; border-radius: 12px; padding: 10px 12px;">
    <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.03em;">
        Démo : Étapes &amp; Progression
    </p>

    <div style="display: flex; flex-direction: column; gap: 6px;">
        <label for="debug-step-select" style="font-size: 11px; font-weight: 700; color: #374151;">
            Aller à une étape :
        </label>
        <select id="debug-step-select" x-model="demoStage"
            style="width: 100%; box-sizing: border-box; font-size: 12px; font-weight: 700; padding: 7px 8px; background: #FFFFFF; color: #1A1A1A; border: 1.5px solid #1A1A1A; border-radius: 8px; cursor: pointer;">
            @for($demoStepNum = 1; $demoStepNum <= 6; $demoStepNum++)
                <option value="{{ $demoStepNum }}">
                    Étape {{ $demoStepNum }}/6 ({{ $demoStepNum - 1 }}/6 fragments)
                </option>
            @endfor
            <option value="finished">Fin : Expédition terminée (6/6)</option>
        </select>

        <div x-show="demoStage !== 'finished'">
            <label for="debug-phase-select" style="display: block; margin-bottom: 4px; font-size: 11px; font-weight: 700; color: #374151;">
                Phase de l'étape :
            </label>
            <select id="debug-phase-select" x-model="demoPhase"
                style="width: 100%; box-sizing: border-box; font-size: 12px; font-weight: 700; padding: 7px 8px; background: #FFFFFF; color: #1A1A1A; border: 1.5px solid #1A1A1A; border-radius: 8px; cursor: pointer;">
                <option value="GUESSING_LOCATION">Phase A : Énigme carte (QR carte)</option>
                <option value="WALKING_TO_TARGET">Phase B : Boussole &amp; Animateur</option>
            </select>
        </div>

        <button type="button" id="debug-apply-step-btn" x-on:click="applyDemoStep()" class="btn-bounce"
            style="margin-top: 2px; width: 100%; padding: 8px 10px; font-size: 12px; font-weight: 900; background: #F4B316; color: #1A1A1A; border: 2px solid #1A1A1A; border-radius: 8px; box-shadow: 0 2px 0 #1A1A1A; cursor: pointer;">
            Aller à cette étape
        </button>

        <button type="button" id="debug-reset-progression-btn" x-on:click="resetDemoProgression()" class="btn-bounce"
            style="width: 100%; padding: 8px 10px; font-size: 12px; font-weight: 800; background: #FEE2E2; color: #991B1B; border: 2px solid #B91C1C; border-radius: 8px; box-shadow: 0 2px 0 #7F1D1D; cursor: pointer;">
            Reset progression (Début 0/6)
        </button>
    </div>
</div>
