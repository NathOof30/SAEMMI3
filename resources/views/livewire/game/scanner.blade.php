<div wire:ignore.self x-data="{
        scannerInstance: null,
        isInitialized: false,
        isStarting: false,
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

        selectBestCamera(cameras) {
            if (!cameras || !cameras.length) {
                return null;
            }

            const camerasWithLabels = cameras.filter(c => c.label && typeof c.label === 'string' && c.label.trim().length > 0);
            if (!camerasWithLabels.length) {
                return null;
            }

            const scoreCamera = (c) => {
                const l = (c.label || '').toLowerCase();
                const isFront = /front|avant|selfie|user|face/i.test(l);
                if (isFront) {
                    return 1;
                }

                const isRear = /back|rear|arri[eè]re|environment/i.test(l);
                const isUltra = /ultra|0[\.,]5|grand.?angle.?ultra|tr[eè]s.?grand/i.test(l);
                const isTele = /tele|t[eé]l[eé]|zoom/i.test(l);
                const isMacro = /macro/i.test(l);
                const isDepth = /depth|profondeur|tof|infrared|infrarouge/i.test(l);
                const isMulti = /triple|virtual/i.test(l);
                const isWide = /wide|grand.?angle/i.test(l);

                if (isRear) {
                    if (!isWide && !isUltra && !isTele && !isMacro && !isDepth && !isMulti) {
                        if (/main|principale|standard|1x|primaire|camera(2)?\s*0/i.test(l)) {
                            return 110;
                        }
                        return 100;
                    }

                    if (isWide && !isUltra && !isTele && !isMacro && !isDepth && !isMulti) {
                        return 80;
                    }

                    if (!isUltra && !isTele) {
                        return 60;
                    }

                    return 30;
                }

                if (!isUltra && !isTele && !isMacro && !isDepth) {
                    return 20;
                }

                return 10;
            };

            let bestCamera = null;
            let highestScore = 0;

            for (const cam of camerasWithLabels) {
                const score = scoreCamera(cam);
                if (score > highestScore) {
                    highestScore = score;
                    bestCamera = cam;
                }
            }

            return bestCamera;
        },

        initScanner() {
            if (this.isInitialized || this.isStarting || window._expeditionScannerRunning) {
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

            this.isStarting = true;
            this.isInitialized = true;
            window._expeditionScannerRunning = true;
            this.cameraErrorMessage = null;

            const start = async () => {
                if (this.scannerInstance || window._html5QrCodeInstance) {
                    await this.cleanup(false);
                }

                if (typeof Html5Qrcode !== 'undefined') {
                    try {
                        let cameras = [];
                        try {
                            cameras = await Html5Qrcode.getCameras();
                        } catch (e) {
                            console.warn('getCameras non disponible ou refusé:', e);
                        }

                        const bestCamera = this.selectBestCamera(cameras);
                        const qrCode = new Html5Qrcode('qr-reader', false);
                        this.scannerInstance = qrCode;
                        window._html5QrCodeInstance = qrCode;

                        const scanConfig = {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        };

                        const onScanSuccess = (scannedText) => {
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
                        };

                        const onScanFailure = (error) => {
                            // Frame ordinaire en attente de code QR
                        };

                        let started = false;
                        let lastStartError = null;

                        // 1. Essai avec le deviceId de la caméra arrière standard identifiée
                        if (bestCamera && bestCamera.id) {
                            try {
                                await qrCode.start(bestCamera.id, scanConfig, onScanSuccess, onScanFailure);
                                started = true;
                            } catch (err) {
                                console.warn('Échec démarrage avec deviceId:', err);
                                lastStartError = err;
                            }
                        }

                        // 2. Repli facingMode exact environment
                        if (!started) {
                            try {
                                await qrCode.start({ facingMode: { exact: 'environment' } }, scanConfig, onScanSuccess, onScanFailure);
                                started = true;
                            } catch (err) {
                                console.warn('Échec facingMode exact environment:', err);
                                lastStartError = err;
                            }
                        }

                        // 3. Repli facingMode environment standard
                        if (!started) {
                            try {
                                await qrCode.start({ facingMode: 'environment' }, scanConfig, onScanSuccess, onScanFailure);
                                started = true;
                            } catch (err) {
                                console.warn('Échec facingMode environment:', err);
                                lastStartError = err;
                            }
                        }

                        // 4. Repli ultime sur première caméra disponible
                        if (!started && cameras && cameras.length > 0 && cameras[0].id) {
                            try {
                                await qrCode.start(cameras[0].id, scanConfig, onScanSuccess, onScanFailure);
                                started = true;
                            } catch (err) {
                                lastStartError = err;
                            }
                        }

                        if (!started) {
                            throw lastStartError || new Error('Impossible de démarrer la caméra.');
                        }
                    } catch (err) {
                        console.error('Erreur initialisation scanner:', err);
                        window._expeditionScannerRunning = false;
                        this.isInitialized = false;
                        this.cameraErrorMessage = (err && err.name ? err.name + ': ' : '') + (err && err.message ? err.message : 'Impossible de démarrer la caméra.');
                    } finally {
                        this.isStarting = false;
                    }
                } else {
                    this.isStarting = false;
                }
            };

            if (typeof this.$cleanup === 'function') {
                this.$cleanup(() => this.cleanup(true));
            }

            if (typeof Html5Qrcode === 'undefined') {
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
            this.isStarting = false;
            const inst = this.scannerInstance || window._html5QrCodeInstance;
            this.scannerInstance = null;
            window._html5QrCodeInstance = null;

            if (inst) {
                try {
                    if (inst.isScanning) {
                        await inst.stop();
                    }
                } catch (e) {
                    console.warn('Erreur stop scanner:', e);
                }
                try {
                    if (typeof inst.clear === 'function') {
                        inst.clear();
                    }
                } catch (e) {
                    console.warn('Erreur clear scanner:', e);
                }
            }

            this.stopCameraTracks();

            const qrEl = document.getElementById('qr-reader');
            if (qrEl) {
                qrEl.innerHTML = '';
            }

            this.isInitialized = false;
            if (fullReset) {
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
    @tab-switched.window=" if ($event.detail && $event.detail.tab==='scanner' ) { const
    qrEl=document.getElementById('qr-reader'); const video=qrEl ? qrEl.querySelector('video') : null; const
    isVideoActive=video && video.srcObject && video.srcObject.active && !video.paused && !video.ended; if
    (!isVideoActive) { reactivateScanner(); } } else if ($event.detail && $event.detail.tab !=='scanner' ) {
    cleanup(false); }">
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
            <button type="button" id="btn-reactivate-scanner" x-on:click="reactivateScanner()">Réactiver le
                scanner</button>
            <span x-show="isLocked" style="display: none;"><small>(Scanner en pause : patientez 2 s ou cliquez pour
                    réactiver)</small></span>
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
            <input type="text" id="manualCode" wire:model="manualCode" placeholder="Exemple: MAP_LOC_1 ou MJ_STEP_1">
            <button type="submit">
                Simuler le scan
            </button>
        </form>
    </div>
</div>