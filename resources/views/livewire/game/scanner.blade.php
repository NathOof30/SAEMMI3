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
                            if (this.isLocked || document.getElementById('fullscreen-scan-overlay')) {
                                return;
                            }

                            const now = Date.now();
                            if (this.lastScannedCode === scannedText && (now - this.lastScanTime < 4000)) {
                                return;
                            }

                            this.isLocked = true;
                            this.lastScannedCode = scannedText;
                            this.lastScanTime = now;

                            if (typeof @this !== 'undefined' && @this.verifyCode) {
                                try {
                                    const promise = @this.verifyCode(scannedText);
                                    if (promise && typeof promise.catch === 'function') {
                                        promise.catch(() => {
                                            this.isLocked = false;
                                        });
                                    }
                                } catch (err) {
                                    console.error('Erreur verifyCode:', err);
                                    this.isLocked = false;
                                }
                            } else {
                                this.isLocked = false;
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
                s.onerror = () => {
                    this.isStarting = false;
                    this.isInitialized = false;
                    window._expeditionScannerRunning = false;
                    this.cameraErrorMessage = 'Impossible de charger le module de scan QR.';
                };
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
            this.lastScanTime = Date.now();
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
    }" x-init="initScanner()" @tab-switched.window="if ($event.detail && $event.detail.tab === 'scanner') {
        const qrEl = document.getElementById('qr-reader');
        const video = qrEl ? qrEl.querySelector('video') : null;
        const isVideoActive = video && video.srcObject && video.srcObject.active && !video.paused && !video.ended;
        if (!isVideoActive) {
            reactivateScanner();
        }
    } else if ($event.detail && $event.detail.tab !== 'scanner') {
        cleanup(false);
    }">
    @if($scanStatus)
        <div id="fullscreen-scan-overlay" x-data="{
                    dismiss() {
                        const status = '{{ $scanStatus }}';
                        const target = '{{ $scanTargetTab }}';
                        $wire.dismissScanOverlay();
                        if (target) {
                            if (typeof tab !== 'undefined') {
                                tab = target;
                            }
                            this.$dispatch('set-tab', { tab: target });
                            if (window.Livewire) {
                                window.Livewire.dispatch('set-tab', { tab: target });
                            }
                        }
                        if (status === 'error') {
                            reactivateScanner();
                        }
                    }
                }" x-on:click.self="dismiss()"
            style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box; {{ $scanStatus === 'success' ? 'background: rgba(0, 0, 0, 0.88);' : 'background: rgba(28, 4, 4, 0.92);' }} backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); animation: fadeInOverlay 0.2s ease-out;">

            <style>
                @keyframes fadeInOverlay {
                    from {
                        opacity: 0;
                    }

                    to {
                        opacity: 1;
                    }
                }

                @keyframes scaleInCard {
                    from {
                        transform: scale(0.85);
                        opacity: 0;
                    }

                    to {
                        transform: scale(1);
                        opacity: 1;
                    }
                }

                .scan-card-glow-success {
                    box-shadow: 0 10px 0 #1A1A1A, 0 0 0 8px rgba(34, 197, 94, 0.35), 0 0 50px rgba(34, 197, 94, 0.6) !important;
                }

                .scan-card-glow-error {
                    box-shadow: 0 10px 0 #1A1A1A, 0 0 0 8px rgba(239, 68, 68, 0.35), 0 0 50px rgba(239, 68, 68, 0.6) !important;
                }
            </style>

            <div class="{{ $scanStatus === 'success' ? 'scan-card-glow-success' : 'scan-card-glow-error' }}"
                style="background: #FFFFFF; border: 4px solid #1A1A1A; border-radius: 24px; padding: 28px 22px; width: 100%; max-width: 350px; text-align: center; position: relative; animation: scaleInCard 0.25s cubic-bezier(0.16, 1, 0.3, 1);">

                <!-- Badge Icône Néobrutaliste avec Halo lumineux & Outline noir -->
                @if($scanStatus === 'success')
                    <div
                        style="margin: 0 auto 18px auto; width: 80px; height: 80px; border-radius: 50%; background: #DCFCE7; border: 3.5px solid #1A1A1A; box-shadow: 0 4px 0 #1A1A1A, 0 0 25px rgba(34, 197, 94, 0.5); display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 48 48" width="46" height="46" fill="none">
                            <path d="M10 25 L20 35 L38 13" stroke="#1A1A1A" stroke-width="8" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M10 25 L20 35 L38 13" stroke="#22C55E" stroke-width="5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                @else
                    <div
                        style="margin: 0 auto 18px auto; width: 80px; height: 80px; border-radius: 50%; background: #FEE2E2; border: 3.5px solid #1A1A1A; box-shadow: 0 4px 0 #1A1A1A, 0 0 25px rgba(239, 68, 68, 0.5); display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 48 48" width="46" height="46" fill="none">
                            <path d="M14 14 L34 34 M34 14 L14 34" stroke="#1A1A1A" stroke-width="8" stroke-linecap="round" />
                            <path d="M14 14 L34 34 M34 14 L14 34" stroke="#EF4444" stroke-width="5" stroke-linecap="round" />
                        </svg>
                    </div>
                @endif

                <!-- Titre -->
                <h3 id="scan-overlay-title"
                    style="font-size: 21px; font-weight: 900; margin: 0 0 10px 0; letter-spacing: -0.5px; {{ $scanStatus === 'success' ? 'color: #15803D;' : 'color: #B91C1C;' }}">
                    {{ $scanTitle }}
                </h3>

                <!-- Sous-titre explicatif -->
                <p id="scan-overlay-subtitle"
                    style="font-size: 14px; font-weight: 600; color: #374151; line-height: 1.45; margin: 0 0 20px 0;">
                    {{ $scanSubtitle }}
                </p>

                <!-- Bouton d'action / indicateur néobrutaliste -->
                @if($scanStatus === 'success')
                    <button type="button" x-on:click="dismiss()" id="scan-overlay-action-btn" class="btn-bounce"
                        style="width: 100%; padding: 12px 16px; background: #F4B316; color: #1A1A1A; font-weight: 900; font-size: 14px; border: 3px solid #1A1A1A; border-radius: 12px; box-shadow: 0 4px 0 #1A1A1A; cursor: pointer;">
                        {{ $scanTargetTab === 'inventory' ? 'Ouvrir mon inventaire →' : 'Poursuivre vers la boussole →' }}
                    </button>
                @else
                    <button type="button" x-on:click="dismiss()" id="scan-overlay-dismiss-btn" class="btn-bounce"
                        style="width: 100%; padding: 12px 16px; background: #F3F4F6; color: #1A1A1A; font-weight: 900; font-size: 14px; border: 3px solid #1A1A1A; border-radius: 12px; box-shadow: 0 4px 0 #1A1A1A; cursor: pointer;">
                        Réessayer le scan ✕
                    </button>
                @endif
            </div>
        </div>
    @endif
    <div style="max-width: 480px; margin: 0 auto; padding: 10px 16px;">
        <div style="margin-bottom: 12px;">
            <button type="button" id="scanner-back-to-hub-btn" x-on:click="tab = 'hub'"
                wire:click="$dispatch('set-tab', { tab: 'hub' })" class="btn-bounce"
                style="background: #FFFFFF; border: 2.5px solid #1A1A1A; border-radius: 12px; padding: 8px 14px; font-size: 13px; font-weight: 800; color: #1A1A1A; box-shadow: 0 3px 0 #1A1A1A; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                ← Retour à la boussole
            </button>
        </div>

        <div
            style="background: #FFFFFF; border: 3.5px solid #1A1A1A; border-radius: 20px; box-shadow: 0 6px 0 #1A1A1A; padding: 20px;">
            <h2
                style="font-size: 20px; font-weight: 900; color: #841818; margin: 0 0 10px 0; text-transform: uppercase;">
                Scanner de QR Code</h2>

            <p style="font-size: 13px; margin: 0 0 12px 0; color: #4B5563;">
                Phase actuelle :
                <strong style="color: #1A1A1A;">
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
                <p
                    style="background: #DCFCE7; border: 2px solid #16A34A; border-radius: 8px; padding: 8px 12px; color: #166534; font-size: 13px; font-weight: 700; margin-bottom: 12px;">
                    <strong>Succès :</strong> {{ $message }}
                </p>
            @endif

            @if($error)
                <p
                    style="background: #FEE2E2; border: 2px solid #DC2626; border-radius: 8px; padding: 8px 12px; color: #991B1B; font-size: 13px; font-weight: 700; margin-bottom: 12px;">
                    <strong>Erreur :</strong> {{ $error }}
                </p>
            @endif

            <div x-show="cameraErrorMessage"
                style="display: none; background: #FEE2E2; border: 2px solid #DC2626; border-radius: 8px; padding: 8px 12px; color: #991B1B; font-size: 13px; margin-bottom: 12px;">
                <p style="margin: 0;"><strong>Erreur Caméra :</strong> <span x-text="cameraErrorMessage"></span></p>
            </div>

            <div style="margin-bottom: 14px;">
                <button type="button" id="btn-reactivate-scanner" x-on:click="reactivateScanner()" class="btn-bounce"
                    style="padding: 8px 14px; background: #F3F4F6; border: 2px solid #1A1A1A; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer;">
                    Réactiver le scanner
                </button>
                <span x-show="isLocked" style="display: none; margin-left: 8px; font-size: 11px; color: #6B7280;">
                    (Scanner en pause : patientez 2 s ou cliquez pour réactiver)
                </span>
            </div>

            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 15px; font-weight: 800; color: #1A1A1A; margin: 0 0 8px 0;">Caméra</h3>
                <div id="qr-reader" wire:ignore
                    style="border-radius: 12px; overflow: hidden; border: 2px solid #1A1A1A; background: #000000;">
                </div>
            </div>

            <div style="border-top: 1px dashed #D1D5DB; padding-top: 14px; margin-top: 14px;">
                <h3 style="font-size: 13px; font-weight: 800; color: #6B7280; margin: 0 0 8px 0;">Simulation manuelle
                    (Debug)</h3>
                <form wire:submit="verifyManualCode" style="display: flex; flex-direction: column; gap: 8px;">
                    <div>
                        <label for="manualCode"
                            style="display: block; font-size: 12px; font-weight: 700; color: #4B5563; margin-bottom: 4px;">Code
                            QR :</label>
                        <input type="text" id="manualCode" wire:model="manualCode"
                            placeholder="Exemple: MAP_LOC_1 ou MJ_STEP_1"
                            style="width: 100%; box-sizing: border-box; padding: 8px 10px; border: 2px solid #1A1A1A; border-radius: 8px; font-size: 13px;">
                    </div>
                    <button type="submit" class="btn-bounce"
                        style="padding: 8px 12px; background: #F4B316; border: 2px solid #1A1A1A; border-radius: 8px; font-weight: 800; font-size: 13px; cursor: pointer; box-shadow: 0 2px 0 #1A1A1A;">
                        Simuler le scan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>