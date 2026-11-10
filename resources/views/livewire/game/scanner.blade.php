<div x-data="{
    scannerInstance: null,
    initScanner() {
        const start = () => {
            const qrEl = document.getElementById('qr-reader');
            if (!qrEl) return;

            if (this.scannerInstance) {
                try {
                    this.scannerInstance.clear();
                } catch (e) {}
            }

            if (typeof Html5QrcodeScanner !== 'undefined') {
                try {
                    const scanner = new Html5QrcodeScanner(
                        'qr-reader',
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        false
                    );
                    this.scannerInstance = scanner;
                    window._html5QrCodeInstance = scanner;

                    scanner.render(
                        (scannedText) => {
                            if (typeof @this !== 'undefined' && @this.verifyCode) {
                                @this.verifyCode(scannedText);
                            }
                        },
                        (error) => {
                            // En attente ou erreur mineure de frame
                        }
                    );
                } catch (err) {
                    console.error('Erreur initialisation scanner:', err);
                }
            }
        };

        if (typeof this.$cleanup === 'function') {
            this.$cleanup(() => this.cleanup());
        }

        if (typeof Html5QrcodeScanner === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/html5-qrcode';
            s.onload = start;
            document.head.appendChild(s);
        } else {
            start();
        }
    },
    cleanup() {
        if (this.scannerInstance) {
            try {
                this.scannerInstance.clear();
            } catch (e) {}
            this.scannerInstance = null;
        }
    }
}" x-init="initScanner()">
    <h2>Scanner de QR Code</h2>

    <p>
        Phase actuelle :
        <strong>
            @if($user && ($user->fragments()->count() >= 6 || $user->current_step > 6))
                Terminé (Toutes les étapes validées)
            @elseif($user && $user->phase === 'GUESSING_LOCATION')
                Recherche de l'emplacement (Scanner le QR Map)
            @elseif($user && $user->phase === 'WALKING_TO_TARGET')
                Marche vers la cible (Scanner le QR MJ)
            @else
                Terminé
            @endif
        </strong>
    </p>

    @if($message)
        <p><strong>Succès :</strong> {{ $message }}</p>
    @endif

    @if($error)
        <p><strong>Erreur :</strong> {{ $error }}</p>
    @endif

    <div>
        <h3>Caméra</h3>
        <div id="qr-reader" wire:ignore></div>
    </div>

    <div>
        <h3>Simulation manuelle (Debug)</h3>
        <form wire:submit="verifyManualCode">
            <label for="manualCode">Code QR :</label>
            <input
                type="text"
                id="manualCode"
                wire:model="manualCode"
                placeholder="Exemple: MAP_LOC_1 ou MJ_STEP_1"
            >
            <button type="submit">
                Simuler le scan
            </button>
        </form>
    </div>
</div>
