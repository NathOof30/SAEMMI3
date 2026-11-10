<div
    wire:ignore.self
    x-data="{
        scannerInstance: null,
        isInitialized: false,
        isLocked: false,
        lastScannedCode: null,
        lastScanTime: 0,
        cameraErrorMessage: null,

        isScannerTabVisible() {
            const pane = document.getElementById('tab-pane-scanner');
            if (!pane) {
                return true;
            }
            return pane.style.display !== 'none' && pane.offsetParent !== null;
        },

        initScanner() {
            if (this.isInitialized || window._expeditionScannerRunning) {
                return;
            }

            if (!this.isScannerTabVisible()) {
                return;
            }

            const qrEl = document.getElementById('qr-reader');
            if (!qrEl) {
                return;
            }

            const existingVideo = qrEl.querySelector('video');
            if (existingVideo && existingVideo.srcObject && existingVideo.srcObject.active) {
                this.isInitialized = true;
                window._expeditionScannerRunning = true;
                return;
            }

            this.isInitialized = true;
            window._expeditionScannerRunning = true;
            this.cameraErrorMessage = null;

            const start = async () => {
                if (this.scannerInstance || window._html5QrCodeInstance) {
                    await this.cleanup(false);
                }

                if (typeof Html5QrcodeScanner !== 'undefined') {
                    try {
                        const scanner = new Html5QrcodeScanner(
                            'qr-reader',
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 },
                                videoConstraints: {
                                    facingMode: { ideal: 'environment' }
                                }
                            },
                            false
                        );
                        this.scannerInstance = scanner;
                        window._html5QrCodeInstance = scanner;

                        scanner.render(
                            (scannedText) => {
                                if (this.isLocked) {
                                    return;
                                }

                                const now = Date.now();
                                if (this.lastScannedCode === scannedText && (now - this.lastScanTime < 4000)) {
                                    return;
                                }

                                this.isLocked = true;
                                this.lastScannedCode = scannedText;
                                this.lastScanTime = now;

                                const unlock = () => {
                                    setTimeout(() => {
                                        this.isLocked = false;
                                    }, 2000);
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
                                // Frame ordinaire en attente de code
                            }
                        );
                    } catch (err) {
                        console.error('Erreur initialisation scanner:', err);
                        window._expeditionScannerRunning = false;
                        this.isInitialized = false;
                        this.cameraErrorMessage = (err && err.name ? err.name + ': ' : '') + (err && err.message ? err.message : 'Impossible de démarrer la caméra.');
                    }
                }
            };

            if (typeof this.$cleanup === 'function') {
                this.$cleanup(() => this.cleanup(true));
            }

            if (typeof Html5QrcodeScanner === 'undefined') {
                const s = document.createElement('script');
                s.src = 'https://unpkg.com/html5-qrcode';
                s.onload = () => start();
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

        async cleanup(fullReset = true) {
            window._expeditionScannerRunning = false;
            const inst = this.scannerInstance || window._html5QrCodeInstance;
            if (inst) {
                try {
                    if (typeof inst.clear === 'function') {
                        await inst.clear();
                    }
                } catch (e) {
                    console.warn('Erreur clear scanner:', e);
                }
                this.scannerInstance = null;
                window._html5QrCodeInstance = null;
            }
            this.stopCameraTracks();

            if (fullReset) {
                this.isInitialized = false;
                this.isLocked = false;
            }
        },

        async reactivateScanner() {
            this.isLocked = false;
            this.lastScannedCode = null;
            this.lastScanTime = 0;
            this.cameraErrorMessage = null;

            if (typeof @this !== 'undefined' && @this.resetScanState) {
                @this.resetScanState();
            }

            const qrEl = document.getElementById('qr-reader');
            const video = qrEl ? qrEl.querySelector('video') : null;
            const isVideoActive = video && video.srcObject && video.srcObject.active && !video.paused && !video.ended;

            if (isVideoActive && window._expeditionScannerRunning) {
                return;
            }

            await this.cleanup(true);
            setTimeout(() => {
                this.initScanner();
            }, 100);
        }
    }"
    x-init="initScanner()"
    @tab-switched.window="if ($event.detail.tab === 'scanner') {
        const qrEl = document.getElementById('qr-reader');
        const video = qrEl ? qrEl.querySelector('video') : null;
        const isVideoActive = video && video.srcObject && video.srcObject.active && !video.paused && !video.ended;
        if (!isVideoActive) {
            reactivateScanner();
        }
    }"
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
