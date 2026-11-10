<div
    style="width: 100%; max-width: 480px; margin: 0 auto; padding: 4px 16px 0 16px; box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif; position: relative; height: 100%; flex: 1; min-height: 0; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden;">
    @if($isFinished)
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; padding: 24px; box-shadow: 0 6px 0 #1a1a1a; text-align: center; margin: auto;">
            <h2 style="font-size: 24px; font-weight: 900; color: #841818; margin-top: 0;">Expédition Terminée !</h2>
            <p style="font-size: 15px; color: #374151; line-height: 1.5;">
                Toutes les étapes ont été complétées ! Rendez-vous dans l'onglet <strong>[Inventaire]</strong> pour
                découvrir le code secret final.
            </p>
            <button type="button" x-on:click="tab = 'inventory'" wire:click="$dispatch('set-tab', { tab: 'inventory' })"
                class="btn-bounce"
                style="margin-top: 14px; padding: 12px 20px; background: #F4B316; color: #1a1a1a; font-weight: 900; font-size: 16px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                Voir mon code secret Asmodee
            </button>
        </div>
    @elseif(!$step)
        <div
            style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; padding: 24px; box-shadow: 0 6px 0 #1a1a1a; text-align: center; margin: auto;">
            <p>Aucune étape active disponible.</p>
        </div>
    @else
        <div x-data="{
                debugModalOpen: false,
                targetLat: {{ $step->lat }},
                targetLng: {{ $step->lng }},
                targetLabel: 'Monument de l’étape',
                debugTargetLat: 50.4376667,
                debugTargetLng: 2.8093333,
                userLat: null,
                userLng: null,
                distanceText: '{{ $user && $user->phase === 'WALKING_TO_TARGET' ? 'Recherche de position GPS en cours...' : '' }}',
                distanceMeters: null,
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
                    this.isMocking = true;
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
                    this.distanceText = distance + ' mètres';
                    const el = document.getElementById('distance-display');
                    if (el) {
                        el.innerText = 'Distance : ' + this.distanceText;
                    }
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
                            navigator.geolocation.getCurrentPosition(() => {}, () => {});
                        } catch (e) {}
                    }
                },
                startOrientationListener() {
                    if (this.orientationBound) return;
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
                getGuidanceText() {
                    if (this.distanceMeters !== null && this.distanceMeters <= 15) {
                        return 'Vous êtes arrivé sur les lieux ! Cherchez l\'indice ou le Maître du Jeu.';
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
                        this.startOrientationListener();

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
                        this.startOrientationListener();
                    }

                    const isWalking = '{{ $user ? $user->phase : '' }}' === 'WALKING_TO_TARGET';
                    const el = document.getElementById('distance-display');

                    if (!navigator.geolocation) {
                        this.distanceText = 'Géolocalisation non supportée par votre navigateur.';
                        if (el) el.innerText = 'Distance : ' + this.distanceText;
                        return;
                    }

                    if (isWalking) {
                        this.watchId = navigator.geolocation.watchPosition(
                            (pos) => this.onGpsPosition(pos),
                            (err) => {
                                this.distanceText = 'Signal GPS indisponible (' + err.message + ')';
                                if (el) el.innerText = 'Distance : ' + this.distanceText;
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    }
                },
                calculateMock() {
                    const lat = parseFloat(this.mockLat);
                    const lng = parseFloat(this.mockLng);
                    const heading = parseFloat(this.mockHeading);

                    this.isMocking = true;

                    if (!isNaN(lat) && !isNaN(lng)) {
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
                    this.targetLat = {{ $step->lat }};
                    this.targetLng = {{ $step->lng }};
                    this.targetLabel = 'Monument de l’étape';

                    if (this.userLat !== null && this.userLng !== null) {
                        this.updateDistance(this.userLat, this.userLng);
                    }

                    this.recalculate(true);
                },
                resetLiveSensors() {
                    this.isMocking = false;
                    this.headingSource = 'aucun';
                    this.deviceHeading = null;
                    this.lastCompassTimestamp = null;
                    this.recalculate(false);
                },
                cleanup() {
                    if (this.watchId) {
                        navigator.geolocation.clearWatch(this.watchId);
                        this.watchId = null;
                    }
                    if (this.orientationHandler) {
                        window.removeEventListener('deviceorientationabsolute', this.orientationHandler, true);
                        window.removeEventListener('deviceorientation', this.orientationHandler, true);
                        this.orientationHandler = null;
                        this.orientationBound = false;
                    }
                }
            }" x-init="init()"
            style="display: flex; flex-direction: column; align-items: center; width: 100%; height: 100%; flex: 1; min-height: 0; position: relative; justify-content: space-between; overflow: hidden;">

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
                    <h4 style="margin: 0; font-size: 15px; font-weight: 900; color: #841818;">Console Technique &amp; Debug
                        GPS</h4>
                    <button type="button" x-on:click="debugModalOpen = false"
                        style="background: none; border: none; font-size: 18px; font-weight: 900; cursor: pointer; color: #1A1A1A;">✕</button>
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
                                x-text="targetLng">{{ $step->lng }}</span>)</small></p>
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
            <div id="compass-permission-section" x-show="needsPermission"
                style="width: 100%; margin-bottom: 12px; background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 12px; padding: 10px; text-align: center;">
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
                    style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 0; width: 100%; margin: auto 0;">
                    <svg viewBox="0 0 200 200" width="200" height="200" aria-label="Boussole mystère de recherche"
                        style="width: min(70vw, 260px); height: min(70vw, 260px); display: block; max-width: min(70vw, 260px); max-height: min(70vw, 260px);">
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
                                    font-weight="900" fill="#F4B316" stroke="#1A1A1A" stroke-width="4" stroke-linejoin="round"
                                    paint-order="stroke fill"
                                    style="font-family: 'Arial Black', Impact, system-ui, sans-serif;">?</text>
                            </g>
                        </g>
                    </svg>
                </div>

                <!-- Deck de Cartes en éventail (-5deg / +5deg) façon jeu Asmodee -->
                <div style="position: relative; width: calc(100% - 44px); max-width: 295px; margin: auto auto 0 auto; z-index: 10;">
                    <!-- Carte arrière-plan Gauche (-5 deg, décalée vers le bas de 14px) -->
                    <div
                        style="position: absolute; top: 14px; bottom: -40px; left: 0; right: 0; background: #E5E7EB; border: 2.5px solid #1A1A1A; border-bottom: none; border-radius: 20px 20px 0 0; transform: rotate(-5deg); transform-origin: bottom center; z-index: 1; box-shadow: 0 4px 0 #1A1A1A;">
                    </div>

                    <!-- Carte arrière-plan Droite (+5 deg, décalée vers le bas de 14px) -->
                    <div
                        style="position: absolute; top: 14px; bottom: -40px; left: 0; right: 0; background: #E5E7EB; border: 2.5px solid #1A1A1A; border-bottom: none; border-radius: 20px 20px 0 0; transform: rotate(5deg); transform-origin: bottom center; z-index: 2; box-shadow: 0 4px 0 #1A1A1A;">
                    </div>

                    <!-- Carte principale au premier plan (descend au-delà du bas) -->
                    <div
                        style="position: relative; z-index: 3; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-bottom: none; border-radius: 24px 24px 0 0; padding: 20px 20px 36px 20px; box-shadow: 0 -4px 12px rgba(0,0,0,0.06); margin-bottom: -16px;">
                        <h3 style="font-size: 20px; font-weight: 900; margin: 0 0 10px 0; color: #1A1A1A;">
                            Destination : <span style="color: #F4B316;">{{ preg_replace('/^[EÉeé]tape\s*\d+\s*:\s*/iu', '', $step->name) }}</span>
                        </h3>

                        <div style="margin-bottom: 16px;">
                            <p
                                style="font-size: 12px; font-weight: 800; color: #841818; text-transform: uppercase; margin: 0 0 6px 0;">
                                Phase A : Recherche sur la carte
                            </p>
                            <p style="font-size: 14px; color: #374151; line-height: 1.45; margin: 0 0 8px 0;">
                                <strong>Énigme :</strong> {{ $step->riddle_text }}
                            </p>
                            <p style="font-size: 12px; color: #6B7280; line-height: 1.4; margin: 0;">
                                Trouvez le lieu sur la carte papier, puis scannez son QR code.
                            </p>
                        </div>

                        <!-- Gros bouton d'action Scanner en bas de carte -->
                        <button type="button" id="phase-a-scanner-btn" x-on:click="tab = 'scanner'"
                            wire:click="$dispatch('set-tab', { tab: 'scanner' })" class="btn-bounce"
                            style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #F4B316; color: #1A1A1A; font-size: 20px; font-weight: 900; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 4px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px;">
                            Scanner la carte
                        </button>
                    </div>
                </div>
            @elseif($user && $user->phase === 'WALKING_TO_TARGET')
                <!-- Phase B : Déplacement physique avec la Boussole Géante & Aiguille bicolore -->
                <div
                    style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 0; width: 100%; margin: auto 0;">
                    <svg viewBox="0 0 200 200" width="200" height="200" aria-label="Boussole d'orientation vers la cible"
                        style="width: min(70vw, 260px); height: min(70vw, 260px); display: block; max-width: min(70vw, 260px); max-height: min(70vw, 260px);">
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

                <!-- Deck de Cartes en éventail (-5deg / +5deg) façon jeu Asmodee -->
                <div style="position: relative; width: calc(100% - 44px); max-width: 295px; margin: auto auto 0 auto; z-index: 10;">
                    <!-- Carte arrière-plan Gauche (-5 deg, décalée vers le bas de 14px) -->
                    <div
                        style="position: absolute; top: 14px; bottom: -40px; left: 0; right: 0; background: #E5E7EB; border: 2.5px solid #1A1A1A; border-bottom: none; border-radius: 20px 20px 0 0; transform: rotate(-5deg); transform-origin: bottom center; z-index: 1; box-shadow: 0 4px 0 #1A1A1A;">
                    </div>

                    <!-- Carte arrière-plan Droite (+5 deg, décalée vers le bas de 14px) -->
                    <div
                        style="position: absolute; top: 14px; bottom: -40px; left: 0; right: 0; background: #E5E7EB; border: 2.5px solid #1A1A1A; border-bottom: none; border-radius: 20px 20px 0 0; transform: rotate(5deg); transform-origin: bottom center; z-index: 2; box-shadow: 0 4px 0 #1A1A1A;">
                    </div>

                    <!-- Carte principale au premier plan (descend au-delà du bas) -->
                    <div
                        style="position: relative; z-index: 3; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-bottom: none; border-radius: 24px 24px 0 0; padding: 20px 20px 36px 20px; box-shadow: 0 -4px 12px rgba(0,0,0,0.06); margin-bottom: -16px;">
                        <h3 style="font-size: 20px; font-weight: 900; margin: 0 0 8px 0; color: #1A1A1A;">
                            Destination : <span style="color: #F4B316;">{{ preg_replace('/^[EÉeé]tape\s*\d+\s*:\s*/iu', '', $step->name) }}</span>
                        </h3>

                        <p style="font-size: 13px; color: #4B5563; line-height: 1.4; margin: 0 0 16px 0;">
                            Suivez la boussole, observez autour de vous et trouvez le spot
                        </p>

                        <!-- Gros bouton d'action en bas de carte -->
                        <div style="width: 100%;">
                            <!-- Si distance > 30m : affiche le nombre de mètres restant en gros (ex: '300 m', fond bordeaux) -->
                            <div x-show="distanceMeters === null || distanceMeters > 30" id="hub-distance-metric-btn"
                                style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #841818; color: #FFFFFF; font-size: 26px; font-weight: 900; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; letter-spacing: 1px;">
                                <span id="metric-distance-text"
                                    x-text="distanceMeters !== null ? distanceMeters + ' m' : '300 m'">300 m</span>
                            </div>

                            <!-- Si distance <= 30m : se transforme en bouton interactif 'Scanner le spot' (fond or) -->
                            <button type="button" x-show="distanceMeters !== null && distanceMeters <= 30"
                                id="hub-scanner-action-btn" x-on:click="tab = 'scanner'"
                                wire:click="$dispatch('set-tab', { tab: 'scanner' })" class="btn-bounce"
                                style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #F4B316; color: #1A1A1A; font-size: 22px; font-weight: 900; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;">
                                Scanner le spot
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>