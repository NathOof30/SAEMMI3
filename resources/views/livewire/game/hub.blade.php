<div
    style="width: 100%; max-width: 480px; margin: 0 auto; padding: 4px 16px 0 16px; box-sizing: border-box; font-family: var(--font-body); position: relative; height: 100%; flex: 1; min-height: 0; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden;">
    @if($isFinished)
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; padding: 24px; box-shadow: 0 6px 0 #1a1a1a; text-align: center; margin: auto;">
            <h2 class="font-display" style="font-family: var(--font-display); font-size: 28px; font-weight: 700; color: #841818; margin-top: 0;">Expédition Terminée !</h2>
            <p style="font-size: 15px; color: #374151; line-height: 1.5;">
                Toutes les étapes ont été complétées ! Rendez-vous dans l'onglet <strong>[Inventaire]</strong> pour
                assembler votre clé complète et découvrir votre trésor secret.
            </p>
            <button type="button" x-on:click="tab = 'inventory'" wire:click="$dispatch('set-tab', { tab: 'inventory' })"
                class="btn-bounce font-display"
                style="font-family: var(--font-display); margin-top: 14px; padding: 12px 20px; background: #F4B316; color: #1a1a1a; font-weight: 700; font-size: 19px; letter-spacing: 0.03em; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                Découvrir mon trésor secret →
            </button>
        </div>
    @elseif(!$step)
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; padding: 24px; box-shadow: 0 6px 0 #1a1a1a; text-align: center; margin: auto;">
            <p>Aucune étape active disponible.</p>
        </div>
    @else
        <div id="hub-compass-root"
            data-phase="{{ $user ? $user->phase : '' }}"
            data-user-id="{{ $user ? $user->id : 0 }}"
            data-step-id="{{ $step->id }}"
            data-step-lat="{{ $step->lat }}"
            data-step-lng="{{ $step->lng }}"
            data-step-radius="{{ (int) ($step->target_radius ?? 30) }}"
            data-gps-idle-label="{{ $user && $user->phase === 'WALKING_TO_TARGET' ? 'Recherche de position GPS en cours...' : 'Non active en phase de recherche' }}"
            x-data="{
                debugModalOpen: false,
                helpModalOpen: false,
                helpView: 'guide',
                helpTutorialSlide: 0,
                manualScanEnabled: false,
                deckExpanded: false,
                activeTab: 'hub',
                sensorsPaused: false,
                wasWithinTarget: false,
                withinTargetZone: false,
                spotStage: 'walking',
                visibilityHandler: null,
                touchStartY: null,
                touchStartScrollTop: 0,
                touchStartedInScroll: false,
                spotStorageKey() {
                    const root = document.getElementById('hub-compass-root');
                    const uid = root && root.dataset.userId ? root.dataset.userId : '{{ $user ? $user->id : 0 }}';
                    const sid = root && root.dataset.stepId ? root.dataset.stepId : '{{ $step->id }}';
                    return 'expedition_spot_state_' + uid + '_' + sid;
                },
                loadSpotStage() {
                    if (!this.isWalkingPhase()) {
                        this.spotStage = 'walking';
                        this.withinTargetZone = false;
                        try {
                            localStorage.removeItem(this.spotStorageKey());
                        } catch (e) {}
                        return;
                    }
                    try {
                        const saved = localStorage.getItem(this.spotStorageKey());
                        if (saved === 'at_spot_challenge' || saved === 'ready_to_scan') {
                            this.spotStage = saved;
                            this.withinTargetZone = true;
                            return;
                        }
                    } catch (e) {}
                    this.spotStage = 'walking';
                },
                setSpotStage(stage) {
                    this.spotStage = stage;
                    try {
                        if (stage === 'at_spot_challenge' || stage === 'ready_to_scan') {
                            localStorage.setItem(this.spotStorageKey(), stage);
                        } else {
                            localStorage.removeItem(this.spotStorageKey());
                        }
                    } catch (e) {}
                },
                confirmArrival() {
                    this.gpsHelpOpen = false;
                    this.withinTargetZone = true;
                    this.setSpotStage('at_spot_challenge');
                },
                completeChallengeAndOpenScanner() {
                    this.setSpotStage('ready_to_scan');
                },
                returnToCompass() {
                    this.gpsHelpOpen = false;
                    this.setSpotStage('walking');
                    this.withinTargetZone = this.distanceMeters !== null && this.distanceMeters <= this.targetRadius;
                    this.wasWithinTarget = this.withinTargetZone;
                },
                openHelpModal(view = 'guide') {
                    this.helpView = view;
                    if (view === 'tutorial') {
                        this.helpTutorialSlide = 0;
                    }
                    this.helpModalOpen = true;
                },
                isWalkingPhase() {
                    const root = document.getElementById('hub-compass-root');
                    return root ? root.dataset.phase === 'WALKING_TO_TARGET' : ('{{ $user ? $user->phase : '' }}' === 'WALKING_TO_TARGET');
                },
                isHubVisible() {
                    const pane = document.getElementById('tab-pane-hub');
                    const paneVisible = !pane || pane.style.display !== 'none';
                    const pageVisible = typeof document === 'undefined' || document.visibilityState !== 'hidden';
                    return paneVisible && pageVisible && this.activeTab === 'hub';
                },
                targetLat: {{ $step->lat }},
                targetLng: {{ $step->lng }},
                targetRadius: {{ (int) ($step->target_radius ?? 30) }},
                approachRadius: {{ (int) max(120, ($step->target_radius ?? 30) + 80) }},
                targetLabel: 'Monument de l’étape',
                debugTargetLat: 50.4376667,
                debugTargetLng: 2.8093333,
                userLat: null,
                userLng: null,
                distanceText: '{{ $user && $user->phase === 'WALKING_TO_TARGET' ? 'Recherche de position GPS en cours...' : '' }}',
                distanceMeters: null,
                gpsAccuracy: null,
                gpsStatus: 'searching',
                gpsErrorMessage: null,
                gpsWaitTooLong: false,
                gpsTimeoutId: null,
                gpsHelpOpen: false,
                bearing: null,
                deviceHeading: null,
                headingSource: 'aucun',
                needleAngle: 0,
                displayedAngle: 0,
                dialAngle: 0,
                displayedDialAngle: 0,
                mysteryAngle: 0,
                displayedMysteryAngle: 0,
                needsPermission: false,
                permissionState: 'prompt',
                watchId: null,
                orientationBound: false,
                orientationHandler: null,
                lastCompassTimestamp: null,
                isMocking: false,
                mockLat: '{{ $step->lat }}',
                mockLng: '{{ $step->lng }}',
                mockHeading: '0',
                teleportToTarget(offsetMeters = 0) {
                    if (offsetMeters === 0) {
                        this.userLat = this.targetLat;
                        this.userLng = this.targetLng;
                        this.mockLat = this.targetLat;
                        this.mockLng = this.targetLng;
                    } else {
                        const latOffset = -(offsetMeters / 111111);
                        this.userLat = this.targetLat + latOffset;
                        this.userLng = this.targetLng;
                        this.mockLat = this.userLat.toFixed(7);
                        this.mockLng = this.userLng.toFixed(7);
                    }
                    if (offsetMeters > this.targetRadius) {
                        this.withinTargetZone = false;
                        this.wasWithinTarget = false;
                        this.setSpotStage('walking');
                    }
                    this.isMocking = true;
                    this.gpsAccuracy = null;
                    this.updateDistance(this.userLat, this.userLng);
                    this.recalculate(true);
                },
                haversineDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371e3;
                    const toRad = Math.PI / 180;
                    const dLat = (lat2 - lat1) * toRad;
                    const dLon = (lon2 - lon1) * toRad;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                              Math.cos(lat1 * toRad) * Math.cos(lat2 * toRad) *
                              Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return Math.round(R * c);
                },
                calculateBearing(lat1, lon1, lat2, lon2) {
                    const toRad = Math.PI / 180;
                    const toDeg = 180 / Math.PI;
                    const phi1 = parseFloat(lat1) * toRad;
                    const phi2 = parseFloat(lat2) * toRad;
                    const deltaLambda = (parseFloat(lon2) - parseFloat(lon1)) * toRad;

                    const y = Math.sin(deltaLambda) * Math.cos(phi2);
                    const x = Math.cos(phi1) * Math.sin(phi2) -
                              Math.sin(phi1) * Math.cos(phi2) * Math.cos(deltaLambda);
                    return (Math.atan2(y, x) * toDeg + 360) % 360;
                },
                shortestAngleDiff(fromAngle, toAngle) {
                    return ((((toAngle - fromAngle) % 360) + 540) % 360) - 180;
                },
                updateNeedle(targetNeedle, instant = false) {
                    if (targetNeedle === null || isNaN(targetNeedle)) return;
                    targetNeedle = ((targetNeedle % 360) + 360) % 360;

                    if (instant) {
                        this.displayedAngle = targetNeedle;
                        this.needleAngle = Math.round(targetNeedle) % 360;
                        return;
                    }

                    const diff = this.shortestAngleDiff(this.displayedAngle, targetNeedle);
                    this.displayedAngle = (this.displayedAngle + diff * 0.35);
                    this.displayedAngle = ((this.displayedAngle % 360) + 360) % 360;
                    this.needleAngle = Math.round(this.displayedAngle) % 360;
                },
                updateDial(targetDial, instant = false) {
                    if (targetDial === null || isNaN(targetDial)) return;
                    targetDial = ((targetDial % 360) + 360) % 360;

                    if (instant) {
                        this.displayedDialAngle = targetDial;
                        this.dialAngle = Math.round(targetDial) % 360;
                        return;
                    }

                    const diff = this.shortestAngleDiff(this.displayedDialAngle, targetDial);
                    this.displayedDialAngle = (this.displayedDialAngle + diff * 0.35);
                    this.displayedDialAngle = ((this.displayedDialAngle % 360) + 360) % 360;
                    this.dialAngle = Math.round(this.displayedDialAngle) % 360;
                },
                updateMystery(targetMystery, instant = false) {
                    if (targetMystery === null || isNaN(targetMystery)) return;
                    targetMystery = ((targetMystery % 360) + 360) % 360;

                    if (instant) {
                        this.displayedMysteryAngle = targetMystery;
                        this.mysteryAngle = Math.round(targetMystery) % 360;
                        return;
                    }

                    const diff = this.shortestAngleDiff(this.displayedMysteryAngle, targetMystery);
                    this.displayedMysteryAngle = (this.displayedMysteryAngle + diff * 0.35);
                    this.displayedMysteryAngle = ((this.displayedMysteryAngle % 360) + 360) % 360;
                    this.mysteryAngle = Math.round(this.displayedMysteryAngle) % 360;
                },
                recalculate(instant = false) {
                    if (this.userLat !== null && this.userLng !== null) {
                        this.bearing = this.calculateBearing(this.userLat, this.userLng, this.targetLat, this.targetLng);
                    }

                    const heading = (this.deviceHeading !== null && !isNaN(this.deviceHeading)) ? this.deviceHeading : 0;
                    const targetDial = (-heading + 360) % 360;
                    this.updateDial(targetDial, instant);

                    this.updateMystery(heading, instant);

                    let targetNeedle = 0;
                    if (this.bearing !== null) {
                        targetNeedle = (this.bearing - heading + 360) % 360;
                    }
                    this.updateNeedle(targetNeedle, instant);
                },
                updateDistance(userLat, userLng) {
                    const distance = this.haversineDistance(parseFloat(userLat), parseFloat(userLng), this.targetLat, this.targetLng);
                    this.distanceMeters = distance;
                    this.gpsStatus = 'ok';
                    this.gpsErrorMessage = null;
                    this.gpsWaitTooLong = false;
                    if (this.gpsTimeoutId) {
                        clearTimeout(this.gpsTimeoutId);
                        this.gpsTimeoutId = null;
                    }
                    const nowWithin = distance <= this.targetRadius;
                    if (nowWithin) {
                        this.gpsHelpOpen = false;
                        if (!this.withinTargetZone && !this.sensorsPaused && typeof window.expeditionVibrate === 'function') {
                            window.expeditionVibrate([60, 40, 60]);
                        }
                        this.withinTargetZone = true;
                    }
                    this.wasWithinTarget = nowWithin;
                    this.distanceText = distance + ' mètres';
                    const el = document.getElementById('distance-display');
                    if (el) {
                        el.innerText = 'Distance : ' + this.distanceText;
                    }
                },
                isWithinTarget() {
                    return this.withinTargetZone || (this.distanceMeters !== null && this.distanceMeters <= this.targetRadius);
                },
                isInApproachZone() {
                    if (this.distanceMeters === null || this.distanceMeters <= this.targetRadius) {
                        return false;
                    }
                    if (this.distanceMeters <= this.approachRadius) {
                        return true;
                    }
                    return this.gpsAccuracy !== null
                        && this.gpsAccuracy > this.targetRadius
                        && (this.distanceMeters - this.gpsAccuracy) <= this.targetRadius;
                },
                hasGpsIssue() {
                    return this.gpsStatus === 'denied'
                        || this.gpsStatus === 'unavailable'
                        || this.gpsStatus === 'unsupported'
                        || (this.distanceMeters === null && this.gpsWaitTooLong);
                },
                showGpsHelpButton() {
                    return !this.isWithinTarget() && (this.isInApproachZone() || this.hasGpsIssue());
                },
                requestOrientationPermission() {
                    const markGranted = () => {
                        this.permissionState = 'granted';
                        this.needsPermission = false;
                        try {
                            localStorage.setItem('compass_permission_granted', '1');
                            window.__compass_permission_granted = true;
                        } catch (e) {}
                        this.startOrientationListener();
                    };

                    if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                        DeviceOrientationEvent.requestPermission()
                            .then(response => {
                                this.permissionState = response;
                                if (response === 'granted') {
                                    markGranted();
                                } else {
                                    this.needsPermission = true;
                                }
                            })
                            .catch(() => {
                                this.permissionState = 'denied';
                                this.needsPermission = true;
                            });
                    } else {
                        markGranted();
                    }

                    if (navigator.geolocation) {
                        try {
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    if (this.isWalkingPhase()) {
                                        this.onGpsPosition(pos);
                                    }
                                },
                                () => {}
                            );
                        } catch (e) {}
                    }
                },
                startOrientationListener() {
                    if (this.orientationBound || !this.isHubVisible()) return;
                    this.orientationBound = true;

                    this.orientationHandler = (event) => {
                        if (this.isMocking) return;

                        let heading = null;
                        if (typeof event.webkitCompassHeading !== 'undefined' && event.webkitCompassHeading !== null) {
                            heading = event.webkitCompassHeading;
                        } else if (event.alpha !== null && typeof event.alpha !== 'undefined') {
                            heading = ((360 - event.alpha) % 360 + 360) % 360;
                        }

                        if (heading !== null && !isNaN(heading)) {
                            this.deviceHeading = heading;
                            this.headingSource = 'boussole';
                            this.lastCompassTimestamp = Date.now();
                            this.recalculate(false);
                        }
                    };

                    if ('ondeviceorientationabsolute' in window) {
                        window.addEventListener('deviceorientationabsolute', this.orientationHandler, true);
                    } else if (window.DeviceOrientationEvent) {
                        window.addEventListener('deviceorientation', this.orientationHandler, true);
                    }
                },
                onGpsPosition(pos) {
                    if (this.isMocking) return;

                    this.userLat = pos.coords.latitude;
                    this.userLng = pos.coords.longitude;
                    this.gpsAccuracy = (pos.coords && typeof pos.coords.accuracy === 'number' && !isNaN(pos.coords.accuracy))
                        ? pos.coords.accuracy
                        : null;
                    this.updateDistance(this.userLat, this.userLng);

                    if (pos.coords.heading !== null && !isNaN(pos.coords.heading) && pos.coords.heading >= 0) {
                        const isCompassStale = !this.lastCompassTimestamp || (Date.now() - this.lastCompassTimestamp > 3000);
                        if (isCompassStale && this.headingSource === 'boussole') {
                            this.headingSource = 'aucun';
                        }

                        if (this.headingSource !== 'boussole') {
                            this.deviceHeading = ((pos.coords.heading % 360) + 360) % 360;
                            this.headingSource = 'gps';
                        }
                    }

                    this.recalculate(false);
                },
                startGpsWatch() {
                    const el = document.getElementById('distance-display');
                    if (!navigator.geolocation) {
                        this.gpsStatus = 'unsupported';
                        this.gpsErrorMessage = 'Géolocalisation non supportée par votre navigateur.';
                        this.distanceText = 'Géolocalisation non supportée par votre navigateur.';
                        if (el) el.innerText = 'Distance : ' + this.distanceText;
                        return;
                    }

                    if (this.watchId) {
                        navigator.geolocation.clearWatch(this.watchId);
                        this.watchId = null;
                    }
                    if (this.gpsTimeoutId) {
                        clearTimeout(this.gpsTimeoutId);
                        this.gpsTimeoutId = null;
                    }

                    this.gpsTimeoutId = setTimeout(() => {
                        if (this.distanceMeters === null && !this.isMocking) {
                            this.gpsWaitTooLong = true;
                        }
                    }, 8000);

                    this.watchId = navigator.geolocation.watchPosition(
                        (pos) => this.onGpsPosition(pos),
                        (err) => {
                            if (this.isMocking) return;
                            if (this.gpsTimeoutId) {
                                clearTimeout(this.gpsTimeoutId);
                                this.gpsTimeoutId = null;
                            }
                            if (err && err.code === 1) {
                                this.gpsStatus = 'denied';
                                this.gpsErrorMessage = 'Permission GPS refusée. Activez la localisation ou touchez ? si vous êtes sur place.';
                                this.distanceText = 'Permission GPS refusée';
                            } else {
                                this.gpsStatus = 'unavailable';
                                this.gpsErrorMessage = 'Signal GPS indisponible ou imprécis. Touchez ? si vous êtes déjà arrivé.';
                                this.distanceText = 'Signal GPS indisponible (' + (err && err.message ? err.message : 'signal faible') + ')';
                            }
                            if (el) el.innerText = 'Distance : ' + this.distanceText;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                },
                getGuidanceText() {
                    if (this.spotStage !== 'walking' || this.isWithinTarget()) {
                        return 'Vous êtes arrivé sur les lieux ! Rejoignez l\'animateur sur place.';
                    }
                    if (this.needleAngle === null || this.bearing === null) {
                        return 'Recherche de cap en cours...';
                    }
                    const angle = ((this.needleAngle % 360) + 360) % 360;
                    if (angle >= 345 || angle <= 15) {
                        return 'Tout droit ! Vous marchez en direction de la cible.';
                    } else if (angle > 15 && angle < 75) {
                        return 'Légèrement à droite (' + angle + '°)';
                    } else if (angle >= 75 && angle < 165) {
                        return 'Tournez à droite (' + angle + '°)';
                    } else if (angle >= 165 && angle <= 195) {
                        return 'Faites demi-tour (cible derrière vous)';
                    } else if (angle > 195 && angle <= 285) {
                        return 'Tournez à gauche (' + (360 - angle) + '°)';
                    } else {
                        return 'Légèrement à gauche (' + (360 - angle) + '°)';
                    }
                },
                init() {
                    if (typeof this.$cleanup === 'function') {
                        this.$cleanup(() => this.cleanup());
                    }

                    this.loadSpotStage();

                    const hubPane = document.getElementById('tab-pane-hub');
                    this.activeTab = (hubPane && hubPane.style.display === 'none') ? 'other' : 'hub';

                    if (!this.visibilityHandler && typeof document !== 'undefined') {
                        this.visibilityHandler = () => {
                            if (document.visibilityState === 'hidden') {
                                this.pauseSensors();
                            } else if (document.visibilityState === 'visible' && this.activeTab === 'hub') {
                                this.resumeSensors();
                            }
                        };
                        document.addEventListener('visibilitychange', this.visibilityHandler);
                    }

                    try {
                        this.manualScanEnabled = localStorage.getItem('expedition_debug_manual_scan') === '1';
                    } catch (e) {
                        this.manualScanEnabled = Boolean(window.__expeditionManualScanEnabled);
                    }
                    window.__expeditionManualScanEnabled = this.manualScanEnabled;

                    const hasStoredPermission = window.__compass_permission_granted || (function() {
                        try {
                            return localStorage.getItem('compass_permission_granted') === '1';
                        } catch (e) {
                            return false;
                        }
                    })();

                    if (hasStoredPermission) {
                        this.needsPermission = false;
                        this.permissionState = 'granted';
                        if (this.isHubVisible()) {
                            this.startOrientationListener();
                        }

                        if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function' && !window.__compass_permission_granted) {
                            setTimeout(() => {
                                if (this.deviceHeading === null && !this.isMocking) {
                                    this.needsPermission = true;
                                }
                            }, 1800);
                        }
                    } else if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                        this.needsPermission = true;
                        this.permissionState = 'prompt';
                    } else {
                        this.needsPermission = false;
                        if (this.isHubVisible()) {
                            this.startOrientationListener();
                        }
                    }

                    const isWalking = this.isWalkingPhase();
                    const el = document.getElementById('distance-display');

                    if (!navigator.geolocation) {
                        this.gpsStatus = 'unsupported';
                        this.gpsErrorMessage = 'Géolocalisation non supportée par votre navigateur.';
                        this.distanceText = 'Géolocalisation non supportée par votre navigateur.';
                        if (el) el.innerText = 'Distance : ' + this.distanceText;
                        return;
                    }

                    if (isWalking && this.isHubVisible()) {
                        this.startGpsWatch();
                    }
                },
                pauseSensors() {
                    this.sensorsPaused = true;
                    if (this.gpsTimeoutId) {
                        clearTimeout(this.gpsTimeoutId);
                        this.gpsTimeoutId = null;
                    }
                    if (this.watchId && navigator.geolocation) {
                        navigator.geolocation.clearWatch(this.watchId);
                        this.watchId = null;
                    }
                    if (this.orientationHandler) {
                        window.removeEventListener('deviceorientationabsolute', this.orientationHandler, true);
                        window.removeEventListener('deviceorientation', this.orientationHandler, true);
                        this.orientationBound = false;
                    }
                },
                resumeSensors() {
                    if (!this.isHubVisible()) {
                        return;
                    }
                    this.sensorsPaused = false;
                    if (!this.needsPermission) {
                        this.startOrientationListener();
                    }
                    if (this.isWalkingPhase() && !this.isMocking && navigator.geolocation && !this.watchId) {
                        this.startGpsWatch();
                    }
                },
                calculateMock() {
                    const lat = parseFloat(this.mockLat);
                    const lng = parseFloat(this.mockLng);
                    const heading = parseFloat(this.mockHeading);

                    this.isMocking = true;
                    this.gpsAccuracy = null;

                    if (!isNaN(lat) && !isNaN(lng)) {
                        const mockDist = this.haversineDistance(lat, lng, this.targetLat, this.targetLng);
                        if (mockDist > this.targetRadius) {
                            this.withinTargetZone = false;
                            this.wasWithinTarget = false;
                            this.setSpotStage('walking');
                        }
                        this.userLat = lat;
                        this.userLng = lng;
                        this.updateDistance(lat, lng);
                    }

                    if (!isNaN(heading)) {
                        this.deviceHeading = ((heading % 360) + 360) % 360;
                        this.headingSource = 'simulation';
                    }

                    this.recalculate(true);
                },
                setDebugTarget() {
                    this.targetLat = this.debugTargetLat;
                    this.targetLng = this.debugTargetLng;
                    this.targetLabel = 'Cible debug';

                    if (this.userLat !== null && this.userLng !== null) {
                        this.updateDistance(this.userLat, this.userLng);
                    }

                    this.recalculate(true);
                },
                resetStepTarget() {
                    const root = document.getElementById('hub-compass-root');
                    this.targetLat = root && root.dataset.stepLat ? parseFloat(root.dataset.stepLat) : {{ $step->lat }};
                    this.targetLng = root && root.dataset.stepLng ? parseFloat(root.dataset.stepLng) : {{ $step->lng }};
                    this.targetRadius = root && root.dataset.stepRadius ? parseInt(root.dataset.stepRadius, 10) : {{ (int) ($step->target_radius ?? 30) }};
                    this.targetLabel = 'Monument de l’étape';

                    if (this.userLat !== null && this.userLng !== null) {
                        this.updateDistance(this.userLat, this.userLng);
                    }

                    this.recalculate(true);
                },
                resetLiveSensors() {
                    this.isMocking = false;
                    this.userLat = null;
                    this.userLng = null;
                    this.bearing = null;
                    this.headingSource = 'aucun';
                    this.deviceHeading = null;
                    this.lastCompassTimestamp = null;
                    this.distanceMeters = null;
                    this.gpsAccuracy = null;
                    this.gpsWaitTooLong = false;
                    this.gpsHelpOpen = false;
                    this.wasWithinTarget = false;
                    this.withinTargetZone = false;
                    this.setSpotStage('walking');
                    if (this.gpsTimeoutId) {
                        clearTimeout(this.gpsTimeoutId);
                        this.gpsTimeoutId = null;
                    }
                    const el = document.getElementById('distance-display');
                    const isWalking = this.isWalkingPhase();
                    if (!navigator.geolocation) {
                        this.gpsStatus = 'unsupported';
                        this.gpsErrorMessage = 'Géolocalisation non supportée par votre navigateur.';
                        this.distanceText = 'Géolocalisation non supportée par votre navigateur.';
                        if (el) el.innerText = 'Distance : ' + this.distanceText;
                    } else {
                        this.gpsStatus = 'searching';
                        this.gpsErrorMessage = null;
                        const root = document.getElementById('hub-compass-root');
                        const idleLabel = (root && root.dataset.gpsIdleLabel) ? root.dataset.gpsIdleLabel : 'Non active en phase de recherche';
                        this.distanceText = isWalking ? idleLabel : '';
                        if (el) el.innerText = 'Distance : ' + idleLabel;
                        if (isWalking) {
                            this.startGpsWatch();
                        }
                    }
                    this.recalculate(false);
                },
                toggleManualScan() {
                    try {
                        localStorage.setItem('expedition_debug_manual_scan', this.manualScanEnabled ? '1' : '0');
                    } catch (e) {}
                    window.__expeditionManualScanEnabled = this.manualScanEnabled;
                    window.dispatchEvent(new CustomEvent('debug-manual-scan-toggled', {
                        detail: { enabled: this.manualScanEnabled }
                    }));
                },
                toggleDeck() {
                    this.deckExpanded = !this.deckExpanded;
                },
                onDeckTouchStart(event) {
                    if (!event.touches || !event.touches.length) return;
                    this.touchStartY = event.touches[0].clientY;
                    const scrollEl = document.getElementById('phase-b-story-scroll');
                    this.touchStartedInScroll = !!(scrollEl && event.target && scrollEl.contains(event.target));
                    this.touchStartScrollTop = scrollEl ? scrollEl.scrollTop : 0;
                },
                onDeckTouchEnd(event) {
                    if (this.touchStartY === null || !event.changedTouches || !event.changedTouches.length) return;
                    const endY = event.changedTouches[0].clientY;
                    const deltaY = endY - this.touchStartY;
                    this.touchStartY = null;
                    if (!this.deckExpanded && deltaY < -35) {
                        this.deckExpanded = true;
                    } else if (this.deckExpanded && deltaY > 35) {
                        if (!this.touchStartedInScroll || this.touchStartScrollTop <= 2) {
                            this.deckExpanded = false;
                        }
                    }
                    this.touchStartedInScroll = false;
                },
                cleanup() {
                    this.pauseSensors();
                    if (this.visibilityHandler && typeof document !== 'undefined') {
                        document.removeEventListener('visibilitychange', this.visibilityHandler);
                        this.visibilityHandler = null;
                    }
                    this.orientationHandler = null;
                }
            }" x-init="init()"
            @tab-switched.window="activeTab = ($event.detail && $event.detail.tab) ? $event.detail.tab : 'hub'; if (activeTab === 'hub') { resumeSensors(); } else { pauseSensors(); }"
            @keydown.escape.window="helpModalOpen = false; debugModalOpen = false;"
            style="display: flex; flex-direction: column; align-items: center; width: 100%; height: 100%; flex: 1; min-height: 0; position: relative; justify-content: space-between; overflow: hidden;">

            <!-- Bouton Aide Discret (?) en haut à gauche sous le header -->
            <div style="position: absolute; top: 0; left: 4px; z-index: 30;">
                <button type="button" id="hub-help-btn" x-on:click="openHelpModal('guide')"
                    title="Aide, règles et dépannage" aria-label="Aide, règles et dépannage"
                    class="btn-bounce font-display"
                    style="font-family: var(--font-display); background: #FFFFFF; border: 2px solid #1A1A1A; border-radius: 9999px; width: 30px; height: 30px; padding: 0; font-size: 17px; font-weight: 700; color: #841818; box-shadow: 0 2px 0 #1A1A1A; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; line-height: 1;">
                    ?
                </button>
            </div>

            <!-- Modale d'Aide & Relance du Tutoriel (sans déconnexion) -->
            <div id="hub-help-modal" x-show="helpModalOpen" x-cloak
                x-on:click.self="helpModalOpen = false"
                role="dialog" aria-modal="true" aria-labelledby="hub-help-modal-title"
                class="alpine-flex"
                style="position: fixed; inset: 0; z-index: 120; background: rgba(0, 0, 0, 0.72); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); padding: 16px; display: flex; align-items: center; justify-content: center; box-sizing: border-box;">
                <div style="width: 100%; max-width: 420px; max-height: 86dvh; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-radius: 22px; box-shadow: 0 8px 0 #1A1A1A; padding: 18px 16px; display: flex; flex-direction: column; overflow: hidden; box-sizing: border-box;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #E5E7EB; padding-bottom: 10px; margin-bottom: 12px; flex-shrink: 0;">
                        <h3 id="hub-help-modal-title" class="font-display"
                            style="font-family: var(--font-display); margin: 0; font-size: 21px; font-weight: 700; color: #841818; letter-spacing: 0.02em;">
                            <span x-show="helpView === 'guide'">Aide de l'Expédition</span>
                            <span x-show="helpView === 'tutorial'" x-cloak>Tutoriel en 5 étapes</span>
                        </h3>
                        <button type="button" id="hub-help-close-btn" x-on:click="helpModalOpen = false" aria-label="Fermer l'aide"
                            style="background: #F3F4F6; border: 2px solid #1A1A1A; border-radius: 8px; width: 30px; height: 30px; font-size: 15px; font-weight: 800; cursor: pointer; color: #1A1A1A; display: inline-flex; align-items: center; justify-content: center;">
                            ✕
                        </button>
                    </div>

                    <!-- Vue 1 : Guide complet, boussole et dépannage (Niveau 2 : contour seul, espacement aéré) -->
                    <div id="hub-help-guide-scroll" x-show="helpView === 'guide'" class="alpine-flex" style="flex: 1; min-height: 0; overflow-y: auto; -webkit-overflow-scrolling: touch; display: flex; flex-direction: column; gap: 14px; padding: 6px 4px 10px 2px;">
                        <div style="flex-shrink: 0; background: #FFFBEB; border: 2px solid #1A1A1A; border-radius: 14px; padding: 13px 14px;">
                            <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #841818;">
                                Rôles de l'équipe (Enfant &amp; Parent)
                            </h4>
                            <p style="margin: 0 0 6px 0; font-size: 13px; color: #374151; line-height: 1.4;">
                                <strong>L'Enfant (Explorateur) :</strong> observe la <strong>carte papier</strong> avec sa <strong>loupe</strong> pour déchiffrer l'énigme de Gali et repérer le QR code du lieu sur la carte (Phase A).
                            </p>
                            <p style="margin: 0; font-size: 13px; color: #374151; line-height: 1.4;">
                                <strong>Le Parent (Guide) :</strong> s'oriente en ville grâce à la <strong>boussole</strong> (Phase B) puis scanne le badge QR de l'animateur sur place.
                            </p>
                        </div>

                        <div style="flex-shrink: 0; background: #F4EBD9; border: 2px solid #1A1A1A; border-radius: 14px; padding: 13px 14px;">
                            <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #1A1A1A;">
                                Lire et calibrer la boussole
                            </h4>
                            <p style="margin: 0 0 6px 0; font-size: 13px; color: #374151; line-height: 1.4;">
                                Sur la flèche bicolore, suivez toujours la <strong>pointe JAUNE OR</strong> : elle désigne votre destination (la pointe rouge indique l'opposé).
                            </p>
                            <p style="margin: 0; font-size: 13px; color: #374151; line-height: 1.4;">
                                <strong>Calibration :</strong> si l'aiguille hésite, décrivez plusieurs grands mouvements en <strong>« 8 »</strong> avec le téléphone loin d'objets métalliques, à plat dans la main.
                            </p>
                            <div x-show="needsPermission" x-cloak style="margin-top: 8px;">
                                <button type="button" x-on:click="requestOrientationPermission()" class="btn-bounce"
                                    style="width: 100%; padding: 7px 10px; background: #841818; color: #FFFFFF; border: 2px solid #1A1A1A; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer;">
                                    Autoriser la boussole du téléphone
                                </button>
                            </div>
                        </div>

                        <div style="flex-shrink: 0; background: #F9FAFB; border: 2px solid #1A1A1A; border-radius: 14px; padding: 13px 14px;">
                            <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #1A1A1A;">
                                Dépannage rapide
                            </h4>
                            <p style="margin: 0 0 5px 0; font-size: 12px; color: #374151; line-height: 1.4;">
                                <strong>Perte de réseau :</strong> la boussole et la distance GPS continuent hors ligne une fois l'écran chargé. Seul le scan de validation nécessite Internet.
                            </p>
                            <p style="margin: 0 0 5px 0; font-size: 12px; color: #374151; line-height: 1.4;">
                                <strong>Caméra bloquée :</strong> autorisez la caméra dans votre navigateur, puis appuyez sur <em>Réactiver le scanner</em> dans l'onglet Scanner.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #374151; line-height: 1.4;">
                                <strong>GPS imprécis :</strong> entre bâtiments ou zones boisées, si vous êtes devant le lieu, ouvrez directement le scanner avec le bouton ci-dessous.
                            </p>
                        </div>
                    </div>

                    <div x-show="helpView === 'guide'" class="alpine-flex" style="flex-shrink: 0; display: flex; flex-direction: column; gap: 10px; margin-top: 12px; padding-top: 12px; border-top: 2px solid #E5E7EB;">
                        <button type="button" id="hub-replay-tuto-btn" x-on:click="openHelpModal('tutorial')" class="btn-bounce font-display"
                            style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 11px 14px; background: #F4B316; color: #1A1A1A; font-size: 17px; font-weight: 700; border: 2.5px solid #1A1A1A; border-radius: 12px; box-shadow: 0 3px 0 #1A1A1A; cursor: pointer;">
                            Relancer le tutoriel (5 étapes)
                        </button>
                        <button type="button" id="hub-help-open-scanner-btn"
                            x-on:click="helpModalOpen = false; tab = 'scanner'"
                            wire:click="$dispatch('set-tab', { tab: 'scanner' })"
                            class="btn-bounce"
                            style="width: 100%; box-sizing: border-box; padding: 9px 14px; background: #FFFFFF; color: #841818; font-size: 13px; font-weight: 800; border: 2px solid #1A1A1A; border-radius: 10px; box-shadow: 0 2px 0 #1A1A1A; cursor: pointer;">
                            Ouvrir le scanner directement →
                        </button>
                    </div>

                    <!-- Vue 2 : Relecture interactive des 5 slides du tutoriel partagé sans se déconnecter -->
                    <div x-show="helpView === 'tutorial'" x-cloak class="alpine-flex" style="flex: 1; min-height: 0; display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                        <div style="flex: 1; min-height: 0; overflow-y: auto; -webkit-overflow-scrolling: touch; background: #FDF8EC; border: 2px solid #1A1A1A; border-radius: 16px; padding: 16px 14px; text-align: center;">
                            <x-tutorial-slides slide-var="helpTutorialSlide" :anti-spoil="true" />
                        </div>

                        <div style="flex-shrink: 0; display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 8px;">
                            <div style="display: flex; justify-content: flex-start;">
                                <button type="button" id="hub-tuto-prev-btn" x-on:click="if (helpTutorialSlide > 0) helpTutorialSlide--"
                                    :disabled="helpTutorialSlide === 0" class="btn-bounce"
                                    style="width: 40px; height: 40px; border-radius: 10px; border: 2.5px solid #1A1A1A; background: #FFFFFF; font-weight: 700; font-size: 16px; display: inline-flex; align-items: center; justify-content: center;"
                                    :style="{ opacity: helpTutorialSlide === 0 ? 0.3 : 1, cursor: helpTutorialSlide === 0 ? 'not-allowed' : 'pointer', boxShadow: helpTutorialSlide === 0 ? 'none' : '0 2px 0 #1A1A1A' }">
                                    ←
                                </button>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button type="button" x-on:click="helpTutorialSlide = 0" aria-label="Slide 1"
                                    style="width: 12px; height: 12px; min-width: 12px; min-height: 12px; flex-shrink: 0; box-sizing: border-box; border-radius: 50%; border: 2px solid #1A1A1A; padding: 0; cursor: pointer;"
                                    :style="{ background: helpTutorialSlide === 0 ? '#F4B316' : '#E5E7EB' }"></button>
                                <button type="button" x-on:click="helpTutorialSlide = 1" aria-label="Slide 2"
                                    style="width: 12px; height: 12px; min-width: 12px; min-height: 12px; flex-shrink: 0; box-sizing: border-box; border-radius: 50%; border: 2px solid #1A1A1A; padding: 0; cursor: pointer;"
                                    :style="{ background: helpTutorialSlide === 1 ? '#F4B316' : '#E5E7EB' }"></button>
                                <button type="button" x-on:click="helpTutorialSlide = 2" aria-label="Slide 3"
                                    style="width: 12px; height: 12px; min-width: 12px; min-height: 12px; flex-shrink: 0; box-sizing: border-box; border-radius: 50%; border: 2px solid #1A1A1A; padding: 0; cursor: pointer;"
                                    :style="{ background: helpTutorialSlide === 2 ? '#F4B316' : '#E5E7EB' }"></button>
                                <button type="button" x-on:click="helpTutorialSlide = 3" aria-label="Slide 4"
                                    style="width: 12px; height: 12px; min-width: 12px; min-height: 12px; flex-shrink: 0; box-sizing: border-box; border-radius: 50%; border: 2px solid #1A1A1A; padding: 0; cursor: pointer;"
                                    :style="{ background: helpTutorialSlide === 3 ? '#F4B316' : '#E5E7EB' }"></button>
                                <button type="button" x-on:click="helpTutorialSlide = 4" aria-label="Slide 5"
                                    style="width: 12px; height: 12px; min-width: 12px; min-height: 12px; flex-shrink: 0; box-sizing: border-box; border-radius: 50%; border: 2px solid #1A1A1A; padding: 0; cursor: pointer;"
                                    :style="{ background: helpTutorialSlide === 4 ? '#F4B316' : '#E5E7EB' }"></button>
                            </div>
                            <div style="display: flex; justify-content: flex-end;">
                                <button type="button" id="hub-tuto-next-btn" x-show="helpTutorialSlide < 4" x-on:click="helpTutorialSlide++"
                                    class="btn-bounce font-display"
                                    style="font-family: var(--font-display); padding: 7px 14px; background: #F4B316; color: #1A1A1A; border: 2.5px solid #1A1A1A; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 2px 0 #1A1A1A; cursor: pointer;">
                                    Suivant →
                                </button>
                                <button type="button" id="hub-tuto-done-btn" x-show="helpTutorialSlide === 4" x-cloak x-on:click="helpModalOpen = false"
                                    class="btn-bounce font-display"
                                    style="font-family: var(--font-display); padding: 7px 14px; background: #841818; color: #FFFFFF; border: 2.5px solid #1A1A1A; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 2px 0 #1A1A1A; cursor: pointer;">
                                    Compris !
                                </button>
                            </div>
                        </div>

                        <div style="flex-shrink: 0; text-align: center;">
                            <button type="button" id="hub-tuto-back-guide-btn" x-on:click="helpView = 'guide'"
                                style="background: none; border: none; color: #6B7280; font-size: 12px; font-weight: 700; text-decoration: underline; cursor: pointer; padding: 0;">
                                ← Retour à l'aide
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bouton Debug Discret en haut à droite sous le header -->
            <div style="position: absolute; top: 0; right: 4px; z-index: 30;">
                <button type="button" x-on:click="debugModalOpen = !debugModalOpen" title="Console de debug GPS"
                    style="background: rgba(0,0,0,0.05); border: 1.5px solid rgba(0,0,0,0.15); border-radius: 8px; padding: 4px 8px; font-size: 11px; font-weight: 700; color: #4B5563; opacity: 0.35; cursor: pointer; transition: opacity 0.2s;"
                    onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='0.35'">
                    [•] debug
                </button>
            </div>

            <!-- Modale flottante de debug (accessible via le bouton discret [•] debug en Phase A et B) -->
            <div x-show="debugModalOpen" x-cloak x-on:click.outside="debugModalOpen = false"
                style="position: fixed; inset: 20px; max-width: 420px; max-height: 80vh; margin: auto; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-radius: 18px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); padding: 18px; z-index: 100; overflow-y: auto;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 2px solid #E5E7EB; padding-bottom: 8px;">
                    <h4 class="font-display" style="font-family: var(--font-display); margin: 0; font-size: 18px; font-weight: 700; color: #841818;">Console Technique &amp; Debug
                        GPS</h4>
                    <button type="button" x-on:click="debugModalOpen = false"
                        style="background: none; border: none; font-size: 18px; font-weight: 700; cursor: pointer; color: #1A1A1A;">✕</button>
                </div>

                <div style="margin-bottom: 12px; background: #FFFBEB; border: 2px solid #1A1A1A; border-radius: 10px; padding: 10px 12px;">
                    <label for="debug-manual-scan-toggle"
                        style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 12px; font-weight: 800; color: #1A1A1A; margin: 0;">
                        <input type="checkbox" id="debug-manual-scan-toggle" x-model="manualScanEnabled" x-on:change="toggleManualScan()"
                            style="width: 18px; height: 18px; accent-color: #841818; cursor: pointer; flex-shrink: 0;">
                        <span>Activer la simulation de scan (PC)</span>
                    </label>
                </div>

                <div style="font-size: 12px; color: #4B5563;">
                    <p id="distance-display" style="margin: 4px 0;">Distance : {{ $user && $user->phase === 'WALKING_TO_TARGET' ? 'Recherche de position GPS en cours...' : 'Non active en phase de recherche' }}</p>
                    <p id="bearing-display" style="margin: 4px 0;">Cap vers la cible : <strong
                            x-text="bearing !== null ? Math.round(bearing) + '°' : 'En attente GPS...'">En attente
                            GPS...</strong></p>
                    <p id="heading-display" style="margin: 4px 0;">Orientation du téléphone : <strong
                            x-text="deviceHeading !== null ? Math.round(deviceHeading) + '° (' + headingSource + ')' : 'En attente capteur/mouvement...'">En
                            attente capteur/mouvement...</strong></p>
                    <p id="needle-display" style="margin: 4px 0;">Angle de l'aiguille : <strong
                            x-text="needleAngle !== null ? needleAngle + '°' : '0°'">0°</strong></p>
                    <p id="guidance-display" style="margin: 4px 0;">Indication : <strong
                            x-text="getGuidanceText()">Recherche de cap en cours...</strong></p>
                </div>

                <div wire:ignore style="margin-top: 14px; border-top: 1px dashed #D1D5DB; padding-top: 10px;">
                    <!-- Actions de téléportation directe sans émojis -->
                    <div style="display: flex; gap: 8px; flex-direction: column; margin-bottom: 12px;">
                        <button type="button" id="teleport-target-btn" x-on:click="teleportToTarget(0)" class="btn-bounce"
                            style="width: 100%; padding: 10px; font-size: 13px; font-weight: 900; background: #F4B316; color: #1A1A1A; border: 2px solid #1A1A1A; border-radius: 8px; box-shadow: 0 2px 0 #1A1A1A; cursor: pointer; text-align: center;">
                            TP sur le spot (0m)
                        </button>
                        <button type="button" id="teleport-approach-btn" x-on:click="teleportToTarget(100)"
                            class="btn-bounce"
                            style="width: 100%; padding: 8px; font-size: 12px; font-weight: 800; background: #F3F4F6; color: #1A1A1A; border: 2px solid #1A1A1A; border-radius: 8px; box-shadow: 0 2px 0 #1A1A1A; cursor: pointer; text-align: center;">
                            TP approche (100m)
                        </button>
                    </div>

                    <p style="margin: 4px 0; font-size: 12px;"><small>Cible active : <strong x-text="targetLabel">Monument
                                de
                                l’étape</strong> (Lat <span x-text="targetLat">{{ $step->lat }}</span>, Lng <span
                                x-text="targetLng">{{ $step->lng }}</span>, rayon <span x-text="targetRadius">{{ (int) ($step->target_radius ?? 30) }}</span> m)</small></p>
                    <p style="margin: 4px 0; font-size: 12px;"><small>Cible alternative : 50°26'15.6"N 2°48'33.6"E
                            (50.4376667,
                            2.8093333)</small></p>

                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin: 8px 0;">
                        <button type="button" id="debug-target-btn" x-on:click="setDebugTarget()"
                            style="padding: 4px 8px; font-size: 11px; background: #F3F4F6; border: 1.5px solid #1A1A1A; border-radius: 6px; cursor: pointer;">Cible
                            debug</button>
                        <button type="button" id="reset-step-target-btn" x-on:click="resetStepTarget()"
                            style="padding: 4px 8px; font-size: 11px; background: #F3F4F6; border: 1.5px solid #1A1A1A; border-radius: 6px; cursor: pointer;">Cible
                            étape</button>
                        <button type="button" id="reset-live-gps-btn" x-show="isMocking" x-on:click="resetLiveSensors()"
                            style="padding: 4px 8px; font-size: 11px; background: #F3F4F6; border: 1.5px solid #1A1A1A; border-radius: 6px; cursor: pointer;">Capteurs
                            réels</button>
                    </div>

                    <div
                        style="display: grid; grid-template-columns: auto 1fr; gap: 6px; align-items: center; margin-top: 8px; font-size: 12px;">
                        <label for="mock-lat">Lat :</label>
                        <input type="text" id="mock-lat" x-model="mockLat" value="{{ $step->lat }}"
                            style="font-size: 12px; padding: 4px 6px; border: 1.5px solid #1A1A1A; border-radius: 6px;">
                        <label for="mock-lng">Lng :</label>
                        <input type="text" id="mock-lng" x-model="mockLng" value="{{ $step->lng }}"
                            style="font-size: 12px; padding: 4px 6px; border: 1.5px solid #1A1A1A; border-radius: 6px;">
                        <label for="mock-heading">Cap (°) :</label>
                        <input type="text" id="mock-heading" x-model="mockHeading" value="0"
                            style="font-size: 12px; padding: 4px 6px; border: 1.5px solid #1A1A1A; border-radius: 6px;">
                    </div>

                    <button type="button" id="mock-gps-btn" x-on:click="calculateMock()" class="btn-bounce"
                        style="margin-top: 10px; width: 100%; padding: 8px; font-size: 12px; font-weight: 800; background: #F4B316; border: 2px solid #1A1A1A; border-radius: 8px; box-shadow: 0 2px 0 #1A1A1A; cursor: pointer;">
                        Calculer avec coordonnées simulées
                    </button>
                </div>
            </div>

            <!-- Permission iOS Safari -->
            <div id="compass-permission-section" x-show="needsPermission && (!isWalkingPhase() || spotStage === 'walking')" x-cloak
                style="flex-shrink: 0; width: 100%; box-sizing: border-box; margin-top: 34px; margin-bottom: 4px; background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 12px; padding: 8px 10px; text-align: center;">
                <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 700; color: #92400E;">Permission boussole requise
                    sur smartphone :</p>
                <button type="button" id="request-orientation-btn" x-on:click="requestOrientationPermission()"
                    class="btn-bounce"
                    style="padding: 6px 14px; background: #841818; color: #ffffff; border: 2px solid #1A1A1A; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer;">
                    Activer la boussole du téléphone
                </button>
            </div>

            @if($user && $user->phase === 'GUESSING_LOCATION')
                <!-- Phase A : Boussole Mystère avec Cadran rotatif et '?' doré animé -->
                <div
                    style="flex: 1 1 0%; display: flex; align-items: center; justify-content: center; min-height: 0; width: 100%; padding: 4px 0; box-sizing: border-box; overflow: hidden;">
                    <svg viewBox="0 0 200 200" width="200" height="200" aria-label="Boussole mystère de recherche"
                        style="width: auto; height: 100%; max-width: min(70vw, 260px); max-height: min(70vw, 260px); aspect-ratio: 1 / 1; display: block;">
                        <!-- Cadran extérieur vert sauge cerclé de noir -->
                        <circle cx="100" cy="100" r="94" fill="#9FB8AD" stroke="#1A1A1A" stroke-width="4" />

                        <!-- Cadran crème intermédiaire avec cercles concentriques noirs -->
                        <circle cx="100" cy="100" r="78" fill="#F4EBD9" stroke="#1A1A1A" stroke-width="3.5" />

                        <!-- Cadran rotatif portant les 4 points cardinaux N, S, O, E -->
                        <g id="compass-dial" :transform="'rotate(' + dialAngle + ' 100 100)'">
                            <circle cx="100" cy="100" r="50" fill="none" stroke="#1A1A1A" stroke-width="2.5"
                                stroke-dasharray="2 4" opacity="0.6" />
                            <!-- Graduations cardinales -->
                            <line x1="100" y1="23" x2="100" y2="28" stroke="#1A1A1A" stroke-width="2.5" />
                            <line x1="177" y1="100" x2="172" y2="100" stroke="#1A1A1A" stroke-width="2.5" />
                            <line x1="100" y1="177" x2="100" y2="172" stroke="#1A1A1A" stroke-width="2.5" />
                            <line x1="23" y1="100" x2="28" y2="100" stroke="#1A1A1A" stroke-width="2.5" />
                            <!-- Points cardinaux -->
                            <text x="100" y="38" text-anchor="middle" dominant-baseline="central" font-size="16" font-weight="900" fill="#B91C1C">N</text>
                            <text x="162" y="100" text-anchor="middle" dominant-baseline="central" font-size="14"
                                font-weight="900" fill="#1A1A1A">E</text>
                            <text x="100" y="162" text-anchor="middle" dominant-baseline="central" font-size="14"
                                font-weight="900" fill="#1A1A1A">S</text>
                            <text x="38" y="100" text-anchor="middle" dominant-baseline="central" font-size="14"
                                font-weight="900" fill="#1A1A1A">O</text>
                        </g>

                        <!-- Point d'interrogation stylisé jaune or à contour noir franc avec balancement idle-sway -->
                        <g :transform="'rotate(' + mysteryAngle + ' 100 100)'">
                            <g class="idle-sway">
                                <text id="phase-a-question-mark" x="100" y="122" text-anchor="middle" font-size="70"
                                    font-weight="700" fill="#F4B316" stroke="#1A1A1A" stroke-width="4" stroke-linejoin="round"
                                    paint-order="stroke fill"
                                    style="font-family: var(--font-display);">?</text>
                            </g>
                        </g>
                    </svg>
                </div>

                <!-- Deck de Cartes en éventail (-5deg / +5deg) façon jeu de plateau -->
                <div style="position: relative; flex-shrink: 0; width: calc(100% - 44px); max-width: 295px; margin: auto auto 0 auto; z-index: 10;">
                    <!-- Carte arrière-plan Gauche (-5 deg, décalée vers le bas de 14px) -->
                    <div
                        style="position: absolute; top: 14px; bottom: -40px; left: 0; right: 0; background: #E5E7EB; border: 2px solid #1A1A1A; border-bottom: none; border-radius: 20px 20px 0 0; transform: rotate(-5deg); transform-origin: bottom center; z-index: 1;">
                    </div>

                    <!-- Carte arrière-plan Droite (+5 deg, décalée vers le bas de 14px) -->
                    <div
                        style="position: absolute; top: 14px; bottom: -40px; left: 0; right: 0; background: #E5E7EB; border: 2px solid #1A1A1A; border-bottom: none; border-radius: 20px 20px 0 0; transform: rotate(5deg); transform-origin: bottom center; z-index: 2;">
                    </div>

                    <!-- Carte principale au premier plan (descend au-delà du bas) -->
                    <div
                        style="position: relative; z-index: 3; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-bottom: none; border-radius: 24px 24px 0 0; padding: 20px 20px 36px 20px; box-shadow: 0 -4px 12px rgba(0,0,0,0.06); margin-bottom: -16px;">
                        <h3 class="font-display" style="font-family: var(--font-display); font-size: 23px; font-weight: 700; margin: 0 0 10px 0; color: #1A1A1A; letter-spacing: 0.02em; line-height: 1.1;">
                            Destination : <span style="color: #F4B316;">Mystère (Étape {{ $step->id }}/6)</span>
                        </h3>

                        <div style="margin-bottom: 16px;">
                            <p
                                style="font-size: 12px; font-weight: 800; color: #841818; text-transform: uppercase; margin: 0 0 6px 0;">
                                Phase A : Recherche sur la carte
                            </p>
                            <p style="font-size: 14px; color: #374151; line-height: 1.45; margin: 0 0 8px 0;">
                                <strong>Énigme de Gali :</strong> {{ $step->riddle_text }}
                            </p>
                            <p style="font-size: 12px; color: #6B7280; line-height: 1.4; margin: 0;">
                                Observez la carte papier avec la loupe pour trouver le lieu, puis scannez son QR code.
                            </p>
                        </div>

                        <!-- Gros bouton d'action Scanner en bas de carte -->
                        <button type="button" id="phase-a-scanner-btn" x-on:click="tab = 'scanner'"
                            wire:click="$dispatch('set-tab', { tab: 'scanner' })" class="btn-bounce font-display"
                            style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 14px 16px; background: #F4B316; color: #1A1A1A; font-size: 22px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 4px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em;">
                            Scanner la carte
                        </button>
                    </div>
                </div>
            @elseif($user && $user->phase === 'WALKING_TO_TARGET')
                <!-- Phase B1 : Déplacement physique avec la Boussole Géante & Aiguille bicolore -->
                <div x-show="spotStage === 'walking'" class="alpine-flex"
                    style="flex: 1 1 0%; display: flex; align-items: center; justify-content: center; min-height: 0; width: 100%; padding: 4px 0; box-sizing: border-box; overflow: hidden;">
                    <svg viewBox="0 0 200 200" width="200" height="200" aria-label="Boussole d'orientation vers la cible"
                        style="width: auto; height: 100%; max-width: min(70vw, 260px); max-height: min(70vw, 260px); aspect-ratio: 1 / 1; display: block;">
                        <!-- Cadran extérieur vert sauge cerclé de noir -->
                        <circle cx="100" cy="100" r="94" fill="#9FB8AD" stroke="#1A1A1A" stroke-width="4" />

                        <!-- Cadran crème intermédiaire avec cercles concentriques noirs -->
                        <circle cx="100" cy="100" r="78" fill="#F4EBD9" stroke="#1A1A1A" stroke-width="3.5" />

                        <!-- Cadran rotatif portant les 4 points cardinaux N, S, O, E -->
                        <g id="compass-dial" :transform="'rotate(' + dialAngle + ' 100 100)'">
                            <circle cx="100" cy="100" r="50" fill="none" stroke="#1A1A1A" stroke-width="2.5"
                                stroke-dasharray="2 4" opacity="0.6" />
                            <!-- Graduations cardinales -->
                            <line x1="100" y1="23" x2="100" y2="28" stroke="#1A1A1A" stroke-width="2.5" />
                            <line x1="177" y1="100" x2="172" y2="100" stroke="#1A1A1A" stroke-width="2.5" />
                            <line x1="100" y1="177" x2="100" y2="172" stroke="#1A1A1A" stroke-width="2.5" />
                            <line x1="23" y1="100" x2="28" y2="100" stroke="#1A1A1A" stroke-width="2.5" />
                            <!-- Points cardinaux -->
                            <text x="100" y="38" text-anchor="middle" dominant-baseline="central" font-size="16" font-weight="900" fill="#B91C1C">N</text>
                            <text x="162" y="100" text-anchor="middle" dominant-baseline="central" font-size="14"
                                font-weight="900" fill="#1A1A1A">E</text>
                            <text x="100" y="162" text-anchor="middle" dominant-baseline="central" font-size="14"
                                font-weight="900" fill="#1A1A1A">S</text>
                            <text x="38" y="100" text-anchor="middle" dominant-baseline="central" font-size="14"
                                font-weight="900" fill="#1A1A1A">O</text>
                        </g>

                        <!-- Aiguille bicolore différenciée (Pointe Cible Jaune Or / Queue Rouge Sang) avec pivot central rouge -->
                        <g :transform="'rotate(' + needleAngle + ' 100 100)'">
                            <!-- Pointe Cible (flèche losange supérieure Jaune Or vers la cible) -->
                            <polygon id="compass-needle-target" points="100,24 124,100 100,100 76,100" fill="#F4B316"
                                stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />

                            <!-- Pointe Opposée (flèche losange inférieure Rouge Sang à contour noir) -->
                            <polygon id="compass-needle-opposite" points="100,176 124,100 100,100 76,100" fill="#B91C1C"
                                stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />

                            <!-- Pivot central rouge vif à contour noir franc -->
                            <circle cx="100" cy="100" r="13" fill="#B91C1C" stroke="#1A1A1A" stroke-width="3.5" />
                            <circle cx="100" cy="100" r="4" fill="#F4B316" />
                        </g>
                    </svg>
                </div>

                <!-- Phase B2 : Emblème du lieu sur place (même DA que le badge '?' de Phase A) -->
                <div id="phase-b-landmark-emblem" x-show="spotStage !== 'walking'" x-cloak class="alpine-flex"
                    style="flex: 1 1 0%; display: flex; align-items: center; justify-content: center; min-height: 0; width: 100%; padding: 4px 0; box-sizing: border-box; overflow: hidden;">
                    <svg id="phase-b-landmark-svg" data-step-icon="{{ $step->id }}" viewBox="0 0 200 200" width="200" height="200"
                        aria-label="Emblème de l'étape : {{ $step->cleanName() }}"
                        style="width: auto; height: 100%; max-width: min(70vw, 260px); max-height: min(70vw, 260px); aspect-ratio: 1 / 1; display: block;">
                        <!-- Cadran extérieur vert sauge cerclé de noir (identique Phase A) -->
                        <circle cx="100" cy="100" r="94" fill="#9FB8AD" stroke="#1A1A1A" stroke-width="4" />

                        <!-- Cadran crème intermédiaire avec cercle concentrique noir -->
                        <circle cx="100" cy="100" r="78" fill="#F4EBD9" stroke="#1A1A1A" stroke-width="3.5" />
                        <circle cx="100" cy="100" r="64" fill="none" stroke="#1A1A1A" stroke-width="2"
                            stroke-dasharray="2 4" opacity="0.3" />

                        @switch($step->id)
                            @case(1)
                                <!-- Étape 1 : Gare de Lens (Locomotive) -->
                                <g id="landmark-icon-step-1">
                                    <line x1="48" y1="138" x2="152" y2="138" stroke="#1A1A1A" stroke-width="4" stroke-linecap="round" />
                                    <circle cx="130" cy="60" r="6" fill="#FFFFFF" stroke="#1A1A1A" stroke-width="2.5" />
                                    <circle cx="141" cy="54" r="8" fill="#FFFFFF" stroke="#1A1A1A" stroke-width="2.5" />
                                    <polygon points="122,90 124,70 136,70 138,90" fill="#1A1A1A" stroke="#1A1A1A" stroke-width="2.5" stroke-linejoin="round" />
                                    <rect x="56" y="74" width="32" height="48" rx="3" fill="#841818" stroke="#1A1A1A" stroke-width="3.5" />
                                    <rect x="52" y="70" width="40" height="7" rx="3" fill="#F4B316" stroke="#1A1A1A" stroke-width="3" />
                                    <rect x="63" y="82" width="18" height="15" rx="2.5" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" />
                                    <rect x="86" y="88" width="54" height="34" rx="4" fill="#841818" stroke="#1A1A1A" stroke-width="3.5" />
                                    <path d="M 103 88 C 103 78, 117 78, 117 88 Z" fill="#F4B316" stroke="#1A1A1A" stroke-width="3" />
                                    <line x1="98" y1="88" x2="98" y2="122" stroke="#F4B316" stroke-width="3" />
                                    <line x1="120" y1="88" x2="120" y2="122" stroke="#F4B316" stroke-width="3" />
                                    <rect x="138" y="92" width="7" height="26" rx="2" fill="#F4B316" stroke="#1A1A1A" stroke-width="3" />
                                    <polygon points="138,122 150,134 138,134" fill="#F4B316" stroke="#1A1A1A" stroke-width="3" stroke-linejoin="round" />
                                    <rect x="54" y="118" width="88" height="8" rx="2" fill="#1A1A1A" />
                                    <circle cx="72" cy="128" r="11" fill="#F4B316" stroke="#1A1A1A" stroke-width="3.5" />
                                    <circle cx="72" cy="128" r="3.5" fill="#1A1A1A" />
                                    <circle cx="100" cy="128" r="11" fill="#F4B316" stroke="#1A1A1A" stroke-width="3.5" />
                                    <circle cx="100" cy="128" r="3.5" fill="#1A1A1A" />
                                    <circle cx="127" cy="130" r="8" fill="#F4B316" stroke="#1A1A1A" stroke-width="3" />
                                    <circle cx="127" cy="130" r="2.5" fill="#1A1A1A" />
                                    <line x1="72" y1="128" x2="100" y2="128" stroke="#841818" stroke-width="3" stroke-linecap="round" />
                                </g>
                                @break
                            @case(2)
                                <!-- Étape 2 : Centre-Ville (Beffroi / Façade Art déco lensoise) -->
                                <g id="landmark-icon-step-2">
                                    <line x1="48" y1="142" x2="152" y2="142" stroke="#1A1A1A" stroke-width="4" stroke-linecap="round" />
                                    <rect x="54" y="96" width="92" height="46" rx="2" fill="#841818" stroke="#1A1A1A" stroke-width="3.5" />
                                    <rect x="61" y="106" width="12" height="18" rx="1.5" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" />
                                    <rect x="127" y="106" width="12" height="18" rx="1.5" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" />
                                    <rect x="80" y="64" width="40" height="78" fill="#841818" stroke="#1A1A1A" stroke-width="3.5" />
                                    <polygon points="84,64 92,50 108,50 116,64" fill="#F4B316" stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />
                                    <line x1="100" y1="40" x2="100" y2="50" stroke="#1A1A1A" stroke-width="3.5" stroke-linecap="round" />
                                    <circle cx="100" cy="82" r="11" fill="#F4B316" stroke="#1A1A1A" stroke-width="3" />
                                    <polyline points="100,76 100,82 105,84" fill="none" stroke="#1A1A1A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <line x1="80" y1="102" x2="120" y2="102" stroke="#F4B316" stroke-width="3" />
                                    <path d="M 90 142 L 90 120 A 10 10 0 0 1 110 120 L 110 142 Z" fill="#F4B316" stroke="#1A1A1A" stroke-width="3" stroke-linejoin="round" />
                                </g>
                                @break
                            @case(3)
                                <!-- Étape 3 : Stade Bollaert-Delelis (Stade & ballon de football) -->
                                <g id="landmark-icon-step-3">
                                    <line x1="58" y1="78" x2="54" y2="58" stroke="#1A1A1A" stroke-width="3" stroke-linecap="round" />
                                    <rect x="48" y="54" width="12" height="6" rx="1.5" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" />
                                    <line x1="142" y1="78" x2="146" y2="58" stroke="#1A1A1A" stroke-width="3" stroke-linecap="round" />
                                    <rect x="140" y="54" width="12" height="6" rx="1.5" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" />
                                    <path d="M 48 110 C 48 72, 152 72, 152 110 L 144 132 L 56 132 Z" fill="#841818" stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />
                                    <path d="M 56 96 C 72 78, 128 78, 144 96" fill="none" stroke="#F4B316" stroke-width="5" stroke-linecap="round" />
                                    <circle cx="100" cy="118" r="26" fill="#FFFFFF" stroke="#1A1A1A" stroke-width="3.5" />
                                    <polygon points="100,106 111,114 107,127 93,127 89,114" fill="#1A1A1A" />
                                    <line x1="100" y1="106" x2="100" y2="92" stroke="#1A1A1A" stroke-width="2.5" />
                                    <line x1="111" y1="114" x2="124" y2="109" stroke="#1A1A1A" stroke-width="2.5" />
                                    <line x1="107" y1="127" x2="116" y2="138" stroke="#1A1A1A" stroke-width="2.5" />
                                    <line x1="93" y1="127" x2="84" y2="138" stroke="#1A1A1A" stroke-width="2.5" />
                                    <line x1="89" y1="114" x2="76" y2="109" stroke="#1A1A1A" stroke-width="2.5" />
                                    <path d="M 90 94 L 100 92 L 110 94" fill="none" stroke="#F4B316" stroke-width="3" stroke-linecap="round" />
                                </g>
                                @break
                            @case(4)
                                <!-- Étape 4 : Musée Louvre-Lens (Galerie & musée de verre) -->
                                <g id="landmark-icon-step-4">
                                    <line x1="44" y1="136" x2="156" y2="136" stroke="#1A1A1A" stroke-width="4" stroke-linecap="round" />
                                    <polygon points="82,88 100,60 118,88" fill="#F4B316" stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />
                                    <line x1="100" y1="60" x2="91" y2="88" stroke="#1A1A1A" stroke-width="2" />
                                    <line x1="100" y1="60" x2="109" y2="88" stroke="#1A1A1A" stroke-width="2" />
                                    <rect x="48" y="96" width="104" height="40" rx="2" fill="#F4B316" stroke="#1A1A1A" stroke-width="3.5" />
                                    <rect x="44" y="88" width="112" height="9" rx="2" fill="#841818" stroke="#1A1A1A" stroke-width="3.5" />
                                    <rect x="55" y="102" width="22" height="28" fill="#FFFFFF" stroke="#1A1A1A" stroke-width="2.5" />
                                    <line x1="59" y1="124" x2="73" y2="108" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" />
                                    <rect x="123" y="102" width="22" height="28" fill="#FFFFFF" stroke="#1A1A1A" stroke-width="2.5" />
                                    <line x1="127" y1="124" x2="141" y2="108" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" />
                                    <rect x="85" y="102" width="30" height="34" fill="#841818" stroke="#1A1A1A" stroke-width="3" />
                                    <rect x="93" y="110" width="14" height="26" fill="#FFFFFF" stroke="#1A1A1A" stroke-width="2.5" />
                                </g>
                                @break
                            @case(5)
                                <!-- Étape 5 : Terrils de Loos-en-Gohelle (Terrils jumeaux) -->
                                <g id="landmark-icon-step-5">
                                    <line x1="44" y1="138" x2="156" y2="138" stroke="#1A1A1A" stroke-width="4" stroke-linecap="round" />
                                    <polygon points="46,138 82,66 116,138" fill="#1A1A1A" stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />
                                    <polygon points="82,66 116,138 92,138" fill="#841818" stroke="#1A1A1A" stroke-width="2.5" stroke-linejoin="round" />
                                    <polygon points="84,138 118,62 154,138" fill="#1A1A1A" stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />
                                    <polygon points="118,62 154,138 128,138" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" stroke-linejoin="round" />
                                    <line x1="118" y1="45" x2="118" y2="62" stroke="#1A1A1A" stroke-width="3" stroke-linecap="round" />
                                    <polygon points="118,45 135,50 118,56" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" stroke-linejoin="round" />
                                </g>
                                @break
                            @default
                                <!-- Étape 6 : Office de Tourisme (Pavillon d'accueil & coffre final) -->
                                <g id="landmark-icon-step-6">
                                    <polygon points="52,78 100,52 148,78" fill="#841818" stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />
                                    <circle cx="100" cy="67" r="5" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" />
                                    <path d="M 62 104 C 62 84, 138 84, 138 104 Z" fill="#F4B316" stroke="#1A1A1A" stroke-width="3.5" stroke-linejoin="round" />
                                    <rect x="62" y="104" width="76" height="36" rx="4" fill="#841818" stroke="#1A1A1A" stroke-width="3.5" />
                                    <rect x="74" y="90" width="9" height="50" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" />
                                    <rect x="117" y="90" width="9" height="50" fill="#F4B316" stroke="#1A1A1A" stroke-width="2.5" />
                                    <rect x="92" y="98" width="16" height="20" rx="3" fill="#F4B316" stroke="#1A1A1A" stroke-width="3" />
                                    <circle cx="100" cy="106" r="2.8" fill="#1A1A1A" />
                                    <line x1="100" y1="108" x2="100" y2="113" stroke="#1A1A1A" stroke-width="2.5" stroke-linecap="round" />
                                </g>
                        @endswitch
                    </svg>
                </div>

                <!-- Deck de Cartes en éventail (-5deg / +5deg) compact par défaut, extensible vers le haut par tap ou swipe -->
                <div id="phase-b-deck-wrapper"
                    x-on:touchstart.passive="onDeckTouchStart($event)"
                    x-on:touchend="onDeckTouchEnd($event)"
                    style="position: relative; flex-shrink: 0; width: calc(100% - 44px); max-width: 295px; margin: auto auto 0 auto; z-index: 10;">
                    <!-- Carte arrière-plan Gauche (-5 deg, décalée vers le bas de 14px) -->
                    <div
                        style="position: absolute; top: 14px; bottom: -40px; left: 0; right: 0; background: #E5E7EB; border: 2px solid #1A1A1A; border-bottom: none; border-radius: 20px 20px 0 0; transform: rotate(-5deg); transform-origin: bottom center; z-index: 1;">
                    </div>

                    <!-- Carte arrière-plan Droite (+5 deg, décalée vers le bas de 14px) -->
                    <div
                        style="position: absolute; top: 14px; bottom: -40px; left: 0; right: 0; background: #E5E7EB; border: 2px solid #1A1A1A; border-bottom: none; border-radius: 20px 20px 0 0; transform: rotate(5deg); transform-origin: bottom center; z-index: 2;">
                    </div>

                    <!-- Carte principale au premier plan -->
                    <div id="phase-b-card"
                        style="position: relative; z-index: 3; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-bottom: none; border-radius: 24px 24px 0 0; padding: 10px 18px 28px 18px; box-shadow: 0 -4px 12px rgba(0,0,0,0.06); margin-bottom: -16px;">
                        <!-- Poignée de tirage & bouton tap pour étendre -->
                        <button type="button" id="phase-b-deck-toggle-btn" x-on:click="toggleDeck()"
                            :aria-expanded="deckExpanded ? 'true' : 'false'"
                            style="width: 100%; background: transparent; border: none; padding: 2px 0 8px 0; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                            <span style="display: block; width: 42px; height: 4px; border-radius: 9999px; background: #D1D5DB;"></span>
                            <span style="font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.04em;">
                                Carnet de Gali &amp; Histoire du lieu ↑
                            </span>
                        </button>

                        <!-- Sous-écran 1 : Marche à la boussole -->
                        <div id="phase-b-walking-panel" x-show="spotStage === 'walking'">
                            <h3 class="font-display" style="font-family: var(--font-display); font-size: 22px; font-weight: 700; margin: 0 0 6px 0; color: #1A1A1A; letter-spacing: 0.02em; line-height: 1.1;">
                                Destination : <span style="color: #F4B316;">{{ $step->cleanName() }}</span>
                            </h3>

                            <p style="font-size: 13px; color: #4B5563; line-height: 1.35; margin: 0 0 10px 0;">
                                Suivez la boussole, observez autour de vous et trouvez le spot
                            </p>

                            <!-- Message d'erreur permission/signal GPS si le signal réel est dégradé ou indisponible -->
                            <p x-show="!isMocking && gpsErrorMessage" x-cloak id="hub-gps-error-msg"
                                style="font-size: 11px; font-weight: 700; color: #B91C1C; background: #FEE2E2; border: 1.5px solid #EF4444; border-radius: 8px; padding: 6px 8px; margin: 0 0 10px 0; line-height: 1.35;"
                                x-text="gpsErrorMessage"></p>

                            <!-- Gros bouton d'action en bas de carte + encart d'aide contextuel -->
                            <div style="position: relative; width: 100%;">
                                <!-- Encart d'aide contextuel GPS imprécis (ouvert au clic sur ?) -->
                                <div x-show="gpsHelpOpen && showGpsHelpButton()" x-cloak id="hub-gps-help-popover"
                                    x-on:click.outside="gpsHelpOpen = false"
                                    style="position: absolute; bottom: calc(100% + 10px); left: 0; right: 0; z-index: 25; background: #FFFBEB; border: 3px solid #1A1A1A; border-radius: 16px; padding: 14px; box-shadow: 0 6px 0 #1A1A1A;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 10px;">
                                        <p style="margin: 0; font-size: 13px; font-weight: 800; color: #841818; line-height: 1.35;">
                                            Vous êtes arrivé mais le point GPS n'est pas précis ?
                                        </p>
                                        <button type="button" x-on:click="gpsHelpOpen = false" aria-label="Fermer l'aide GPS"
                                            style="background: none; border: none; font-size: 16px; font-weight: 700; color: #1A1A1A; cursor: pointer; padding: 0 2px; line-height: 1;">
                                            ✕
                                        </button>
                                    </div>
                                    <button type="button" id="hub-gps-fallback-scanner-btn"
                                        x-on:click="confirmArrival()" class="btn-bounce font-display"
                                        style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 11px 14px; background: #F4B316; color: #1A1A1A; font-size: 16px; font-weight: 700; border: 2.5px solid #1A1A1A; border-radius: 10px; box-shadow: 0 3px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.03em;">
                                        Je suis devant le lieu
                                    </button>
                                    <button type="button" id="hub-gps-retry-btn"
                                        x-on:click="gpsHelpOpen = false; resetLiveSensors()"
                                        style="display: block; width: 100%; margin-top: 8px; padding: 7px 10px; background: #FFFFFF; color: #841818; font-size: 12px; font-weight: 800; border: 2px solid #1A1A1A; border-radius: 8px; cursor: pointer;">
                                        Actualiser la position GPS
                                    </button>
                                </div>

                                <!-- Si distance > targetRadius (ou GPS en attente) : affiche la distance réelle ou 'Signal GPS...' + bouton ? contextuel -->
                                <div x-show="!isWithinTarget()" style="width: 100%;">
                                    <div style="display: flex; align-items: stretch; gap: 8px; width: 100%;">
                                        <div id="hub-distance-metric-btn" class="font-display"
                                            style="font-family: var(--font-display); flex: 1; min-width: 0; box-sizing: border-box; padding: 12px 12px; background: #841818; color: #FFFFFF; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; letter-spacing: 0.04em; display: flex; align-items: center; justify-content: center;"
                                            :style="{ fontSize: distanceMeters !== null ? '28px' : '22px' }">
                                            <span id="metric-distance-text"
                                                x-text="distanceMeters !== null ? distanceMeters + ' m' : (gpsStatus === 'denied' ? 'GPS refusé' : (gpsStatus === 'unavailable' || gpsStatus === 'unsupported' ? 'GPS indisponible' : 'Signal GPS...'))">Signal GPS...</span>
                                        </div>

                                        <div x-show="showGpsHelpButton()" x-cloak style="flex-shrink: 0;">
                                            <button type="button"
                                                id="hub-gps-help-btn"
                                                x-on:click.stop="gpsHelpOpen = !gpsHelpOpen"
                                                title="Vous êtes arrivé mais le point GPS n'est pas précis ?"
                                                aria-label="Vous êtes arrivé mais le point GPS n'est pas précis ? Confirmer votre arrivée"
                                                class="btn-bounce font-display"
                                                style="font-family: var(--font-display); width: 54px; height: 100%; box-sizing: border-box; background: #FEF3C7; color: #841818; font-size: 26px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                                ?
                                            </button>
                                        </div>
                                    </div>

                                    <div x-show="showGpsHelpButton()" x-cloak>
                                        <button type="button"
                                            x-on:click.stop="gpsHelpOpen = !gpsHelpOpen"
                                            style="display: block; width: 100%; background: none; border: none; padding: 8px 0 0 0; font-size: 11px; font-weight: 700; color: #841818; text-align: center; cursor: pointer; text-decoration: underline;">
                                            Arrivé mais GPS imprécis ? Touchez ?
                                        </button>
                                    </div>
                                </div>

                                <!-- Si distance <= targetRadius : demande confirmation explicite avant de masquer la boussole -->
                                <button type="button" x-show="isWithinTarget()" x-cloak
                                    id="hub-confirm-arrival-btn" x-on:click="confirmArrival()"
                                    class="btn-bounce font-display"
                                    style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 14px 16px; background: #F4B316; color: #1A1A1A; font-size: 23px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em;">
                                    Vous êtes arrivé ?
                                </button>
                            </div>
                        </div>

                        <!-- Sous-écran 2 : Pause sur place devant l'animateur avant ouverture du scanner -->
                        <div id="phase-b-on-site-panel" x-show="spotStage !== 'walking'" x-cloak>
                            <div style="margin-bottom: 6px;">
                                <span
                                    style="display: inline-block; padding: 3px 10px; background: #FEF3C7; border: 1.5px solid #1A1A1A; border-radius: 9999px; font-size: 10px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.04em;">
                                    Sur place  |  Étape {{ $step->id }}/6
                                </span>
                            </div>

                            <h3 class="font-display" style="font-family: var(--font-display); font-size: 22px; font-weight: 700; margin: 0 0 8px 0; color: #1A1A1A; letter-spacing: 0.02em; line-height: 1.1;">
                                {{ $step->cleanName() }}
                            </h3>

                            <div x-show="spotStage === 'at_spot_challenge'">
                                <p style="font-size: 13px; color: #374151; line-height: 1.4; margin: 0 0 12px 0;">
                                    @if($step->id === 6)
                                        Halte, explorateurs ! Posez le téléphone un instant et rejoignez l'animateur ou l'équipe d'accueil du point final pour relever l'ultime épreuve en famille avant de scanner.
                                    @else
                                        Halte, explorateurs ! Posez le téléphone un instant et rejoignez l'animateur sur place pour relever l'épreuve en famille avant de scanner.
                                    @endif
                                </p>

                                <button type="button" id="hub-challenge-done-btn"
                                    x-on:click="completeChallengeAndOpenScanner(); tab = 'scanner'"
                                    wire:click="$dispatch('set-tab', { tab: 'scanner' })"
                                    class="btn-bounce font-display"
                                    style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 13px 16px; background: #F4B316; color: #1A1A1A; font-size: 21px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em;">
                                    Épreuve terminée ?
                                </button>
                            </div>

                            <div x-show="spotStage === 'ready_to_scan'" x-cloak>
                                <p style="font-size: 13px; color: #374151; line-height: 1.4; margin: 0 0 12px 0;">
                                    Épreuve terminée ! Présentez la caméra face au badge de l'animateur sur place pour récupérer votre fragment de clé.
                                </p>

                                <button type="button" id="hub-scanner-action-btn"
                                    x-on:click="tab = 'scanner'"
                                    wire:click="$dispatch('set-tab', { tab: 'scanner' })"
                                    class="btn-bounce font-display"
                                    style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 13px 16px; background: #F4B316; color: #1A1A1A; font-size: 19px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.03em;">
                                    Scanner le badge de l'animateur
                                </button>
                            </div>

                            <button type="button" id="hub-back-to-compass-btn"
                                x-on:click="returnToCompass()"
                                style="display: block; width: 100%; background: none; border: none; padding: 10px 0 0 0; font-size: 12px; font-weight: 700; color: #6B7280; text-align: center; cursor: pointer; text-decoration: underline;">
                                ← Revenir à la boussole
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Voile et Tiroir animé vers le haut : Le mot de Gali + L'Histoire patrimoniale du lieu -->
                <div id="phase-b-drawer-backdrop"
                    x-on:click="deckExpanded = false"
                    aria-hidden="true"
                    style="position: absolute; inset: 0; z-index: 23; background: rgba(250, 248, 245, 0.88); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); transition: opacity 0.25s ease;"
                    :style="{ opacity: deckExpanded ? 1 : 0, pointerEvents: deckExpanded ? 'auto' : 'none' }">
                </div>

                <div id="phase-b-story-drawer"
                    x-on:touchstart.passive="onDeckTouchStart($event)"
                    x-on:touchend="onDeckTouchEnd($event)"
                    :aria-hidden="deckExpanded ? 'false' : 'true'"
                    style="position: absolute; top: 32px; left: 4px; right: 4px; bottom: 0; z-index: 24; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-bottom: none; border-radius: 24px 24px 0 0; box-shadow: 0 -10px 28px rgba(0,0,0,0.18); padding: 10px 18px 16px 18px; display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box; transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.22s ease;"
                    :style="{ transform: deckExpanded ? 'translateY(0)' : 'translateY(105%)', opacity: deckExpanded ? 1 : 0, pointerEvents: deckExpanded ? 'auto' : 'none' }">
                    <div style="flex-shrink: 0;">
                        <button type="button" id="phase-b-deck-collapse-btn" x-on:click="deckExpanded = false"
                            style="width: 100%; background: transparent; border: none; padding: 2px 0 8px 0; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px;">
                            <span style="display: block; width: 42px; height: 4px; border-radius: 9999px; background: #D1D5DB;"></span>
                            <span style="font-size: 11px; font-weight: 800; color: #841818; text-transform: uppercase; letter-spacing: 0.04em;"
                                x-text="spotStage === 'walking' ? 'Replier vers la boussole ↓' : 'Replier vers l\'étape ↓'">
                                Replier vers la boussole ↓
                            </span>
                        </button>

                        <h3 class="font-display" style="font-family: var(--font-display); font-size: 22px; font-weight: 700; margin: 0 0 6px 0; color: #1A1A1A; letter-spacing: 0.02em; line-height: 1.1;">
                            Destination : <span style="color: #F4B316;">{{ $step->cleanName() }}</span>
                        </h3>
                    </div>

                    <div id="phase-b-story-scroll"
                        style="flex: 1; min-height: 0; overflow-y: auto; -webkit-overflow-scrolling: touch; display: flex; flex-direction: column; gap: 14px; margin: 4px 0 12px 0; padding: 4px 4px 8px 0;">
                        <div style="flex-shrink: 0; background: #FFFBEB; border: 2px solid #1A1A1A; border-radius: 14px; padding: 13px 14px;">
                            <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 4px 0; font-size: 16px; font-weight: 700; color: #841818; letter-spacing: 0.02em;">
                                Le mot de Gali
                            </h4>
                            <p style="margin: 0; font-size: 13px; color: #374151; line-height: 1.45;">
                                {{ $step->story_rp }}
                            </p>
                        </div>

                        <div style="flex-shrink: 0; background: #F4EBD9; border: 2px solid #1A1A1A; border-radius: 14px; padding: 13px 14px;">
                            <h4 class="font-display" style="font-family: var(--font-display); margin: 0 0 4px 0; font-size: 16px; font-weight: 700; color: #1A1A1A; letter-spacing: 0.02em;">
                                L'Histoire du lieu
                            </h4>
                            <p style="margin: 0; font-size: 13px; color: #374151; line-height: 1.45;">
                                {{ $step->heritage_info }}
                            </p>
                        </div>
                    </div>

                    <div id="phase-b-drawer-cta" style="flex-shrink: 0;">
                        <button type="button" x-show="spotStage === 'walking' && isWithinTarget()" x-cloak
                            id="phase-b-drawer-confirm-arrival-btn"
                            x-on:click="deckExpanded = false; confirmArrival()" class="btn-bounce font-display"
                            style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 12px 16px; background: #F4B316; color: #1A1A1A; font-size: 20px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 4px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em;">
                            Vous êtes arrivé ?
                        </button>
                        <button type="button" x-show="spotStage === 'walking' && !isWithinTarget()"
                            x-on:click="deckExpanded = false" class="btn-bounce font-display"
                            style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 11px 16px; background: #841818; color: #FFFFFF; font-size: 18px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 4px 0 #1A1A1A; text-align: center; cursor: pointer; letter-spacing: 0.03em;">
                            Retour à la boussole ↓
                        </button>
                        <button type="button" x-show="spotStage === 'at_spot_challenge'" x-cloak
                            id="phase-b-drawer-challenge-done-btn"
                            x-on:click="deckExpanded = false; completeChallengeAndOpenScanner(); tab = 'scanner'"
                            wire:click="$dispatch('set-tab', { tab: 'scanner' })" class="btn-bounce font-display"
                            style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 12px 16px; background: #F4B316; color: #1A1A1A; font-size: 20px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 4px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em;">
                            Épreuve terminée ?
                        </button>
                        <button type="button" x-show="spotStage === 'ready_to_scan'" x-cloak
                            id="phase-b-drawer-scanner-btn"
                            x-on:click="deckExpanded = false; tab = 'scanner'"
                            wire:click="$dispatch('set-tab', { tab: 'scanner' })" class="btn-bounce font-display"
                            style="font-family: var(--font-display); width: 100%; box-sizing: border-box; padding: 12px 16px; background: #F4B316; color: #1A1A1A; font-size: 18px; font-weight: 700; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 4px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.03em;">
                            Scanner le badge de l'animateur
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>