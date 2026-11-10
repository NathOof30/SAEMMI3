<div x-data="{
    scannerInstance: null,
    isInitialized: false,
    isLocked: false,
    cameraErrorMessage: null,

    initScanner() {
        if (this.isInitialized) {
            return;
        }

        const qrEl = document.getElementById('qr-reader');
        if (!qrEl) {
            return;
        }

        this.isInitialized = true;
        this.cameraErrorMessage = null;

        const start = () => {
            if (this.scannerInstance) {
                try {
                    this.stopCameraTracks();
                    this.scannerInstance.clear();
                } catch (e) {}
                this.scannerInstance = null;
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
                            if (this.isLocked) {
                                return;
                            }
                            this.isLocked = true;

                            const unlock = () => {
                                setTimeout(() => {
                                    this.isLocked = false;
                                }, 2500);
                            };

                            if (typeof @this !== 'undefined' && @this.verifyCode) {
                                try {
                                    const promise = @this.verifyCode(scannedText);
                                    if (promise && typeof promise.then === 'function') {
                                        promise.then(() => unlock()).catch(() => unlock());
                                    } else {
                                        unlock();
                                    }
                                } catch (err) {
                                    console.error('Erreur verifyCode:', err);
                                    unlock();
                                }
                            } else {
                                unlock();
                            }
                        },
                        (error) => {
                            // En attente ou erreur mineure de frame ordinaire
                        }
                    );
                } catch (err) {
                    console.error('Erreur initialisation scanner:', err);
                    this.cameraErrorMessage = (err && err.name ? err.name + ': ' : '') + (err && err.message ? err.message : 'Impossible de démarrer la caméra.');
                    this.isInitialized = false;
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

    stopCameraTracks() {
        try {
            const qrEl = document.getElementById('qr-reader');
            if (qrEl) {
                const videos = qrEl.querySelectorAll('video');
                videos.forEach(video => {
                    if (video.srcObject && typeof video.srcObject.getTracks === 'function') {
                        video.srcObject.getTracks().forEach(track => {
                            try {
                                track.stop();
                            } catch (e) {}
                        });
                        video.srcObject = null;
                    }
                });
            }
        } catch (e) {
            console.warn('Erreur arrêt pistes vidéo:', e);
        }
    },

    cleanup() {
        this.stopCameraTracks();
        if (this.scannerInstance) {
            try {
                this.scannerInstance.clear();
            } catch (e) {}
            this.scannerInstance = null;
        }
        this.isInitialized = false;
        this.isLocked = false;
    },

    reactivateScanner() {
        this.isLocked = false;
        this.cameraErrorMessage = null;
        if (typeof @this !== 'undefined' && @this.resetScanState) {
            @this.resetScanState();
        }

        const qrEl = document.getElementById('qr-reader');
        const video = qrEl ? qrEl.querySelector('video') : null;
        const isVideoActive = video && video.srcObject && !video.paused && !video.ended;

        if (!isVideoActive || !this.scannerInstance) {
            this.cleanup();
            setTimeout(() => {
                this.initScanner();
            }, 150);
        }
    }
}"
x-init="initScanner()"
@tab-switched.window="if ($event.detail.tab === 'scanner' && !isInitialized) { initScanner(); }"
>
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

    <div x-show="cameraErrorMessage" style="display: none;">
        <p><strong>Erreur Caméra :</strong> <span x-text="cameraErrorMessage"></span></p>
    </div>

    <div>
        <p>
            <button type="button" id="btn-reactivate-scanner" x-on:click="reactivateScanner()">Réactiver le scanner</button>
            <span x-show="isLocked" style="display: none;"><small>(Scanner en pause : patientez 2 s ou cliquez pour réactiver)</small></span>
        </p>
    </div>

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
