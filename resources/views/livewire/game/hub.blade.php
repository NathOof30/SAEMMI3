<div style="width: 100%; max-width: 480px; margin: 0 auto; padding: 10px 16px 20px 16px; box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif;">
    @if($isFinished)
        <div style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; padding: 24px; box-shadow: 0 6px 0 #1a1a1a; text-align: center;">
            <h2 style="font-size: 24px; font-weight: 900; color: #841818; margin-top: 0;">Expédition Terminée !</h2>
            <p style="font-size: 15px; color: #374151; line-height: 1.5;">
                Toutes les étapes ont été complétées ! Rendez-vous dans l'onglet <strong>[Inventaire]</strong> pour découvrir le code secret final.
            </p>
            <button type="button"
                    x-on:click="tab = 'inventory'"
                    wire:click="$dispatch('set-tab', { tab: 'inventory' })"
                    style="margin-top: 14px; padding: 12px 20px; background: #F4B316; color: #1a1a1a; font-weight: 900; font-size: 16px; border: 2.5px solid #1a1a1a; border-radius: 12px; box-shadow: 0 4px 0 #1a1a1a; cursor: pointer;">
                Voir mon code secret Asmodee
            </button>
        </div>
    @elseif(!$step)
        <div style="background: #ffffff; border: 3.5px solid #1a1a1a; border-radius: 20px; padding: 24px; box-shadow: 0 6px 0 #1a1a1a; text-align: center;">
            <p>Aucune étape active disponible.</p>
        </div>
    @else
        @if($user && $user->phase === 'GUESSING_LOCATION')
            <!-- Phase A : Recherche sur la carte papier -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 16px;">
                <!-- Card Destination avec outline noir franc (Phase A) -->
                <div style="position: relative; width: 100%; max-width: 360px; margin: 16px auto 0 auto;">
                    <!-- Ailes d'arrière-plan de la carte (effet collection) -->
                    <div style="position: absolute; inset: 0; background: #D1D5DB; border: 3px solid #1A1A1A; border-radius: 20px; transform: scale(1.05, 0.96); z-index: 1;"></div>

                    <!-- Carte principale blanche -->
                    <div style="position: relative; z-index: 2; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-radius: 20px; padding: 20px; box-shadow: 0 6px 0 #1A1A1A;">
                        <h3 style="font-size: 20px; font-weight: 900; margin: 0 0 10px 0; color: #1A1A1A;">
                            Destination : <span style="color: #F4B316;">{{ $step->name }}</span>
                        </h3>

                        <div style="margin-bottom: 16px;">
                            <p style="font-size: 12px; font-weight: 800; color: #841818; text-transform: uppercase; margin: 0 0 6px 0;">
                                Phase A : Recherche sur la carte
                            </p>
                            <p style="font-size: 14px; color: #374151; line-height: 1.45; margin: 0 0 8px 0;">
                                <strong>Énigme :</strong> {{ $step->riddle_text }}
                            </p>
                            <p style="font-size: 12px; color: #6B7280; line-height: 1.4; margin: 0;">
                                L'Enfant (Explorateur) cherche le lieu sur sa carte papier à l'aide du filtre optique. Le Parent scanne ensuite le QR code correspondant sur la carte (ex: <code>MAP_LOC_{{ $step->id }}</code>) dans l'onglet <strong>[Scanner]</strong>.
                            </p>
                        </div>

                        <!-- Gros bouton d'action Scanner en bas de carte -->
                        <button type="button"
                                id="phase-a-scanner-btn"
                                class="btn-bounce"
                                x-on:click="tab = 'scanner'"
                                wire:click="$dispatch('set-tab', { tab: 'scanner' })"
                                style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #F4B316; color: #1A1A1A; font-size: 20px; font-weight: 900; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 4px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px;">
                            Scanner la carte
                        </button>
                    </div>
                </div>
            </div>
        @elseif($user && $user->phase === 'WALKING_TO_TARGET')
            <!-- Phase B : Déplacement physique avec la Boussole Géante 60 FPS -->
            <div x-data="{
                targetLat: {{ $step->lat }},
                targetLng: {{ $step->lng }},
                targetLabel: 'Monument de l’étape',
                debugTargetLat: 50.4376667,
                debugTargetLng: 2.8093333,
                userLat: null,
                userLng: null,
                distanceText: 'Recherche de position GPS en cours...',
                distanceMeters: null,
                bearing: null,
                deviceHeading: null,
                headingSource: 'aucun',
                needleAngle: 0,
                displayedAngle: 0,
                needsPermission: false,
                permissionState: 'prompt',
                watchId: null,
                orientationBound: false,
                orientationHandler: null,
                lastCompassTimestamp: null,
                isMocking: false,
                mockLat: '{{ $step->lat }}',
                mockLng: '{{ $step->lng + 0.001 }}',
                mockHeading: '0',
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
                    if (Math.abs(diff) < 0.5) {
                        this.displayedAngle = targetNeedle;
                    } else {
                        this.displayedAngle = (this.displayedAngle + diff * 0.35);
                    }
                    this.displayedAngle = ((this.displayedAngle % 360) + 360) % 360;
                    this.needleAngle = Math.round(this.displayedAngle) % 360;
                },
                recalculate(instant = false) {
                    if (this.userLat !== null && this.userLng !== null) {
                        this.bearing = this.calculateBearing(this.userLat, this.userLng, this.targetLat, this.targetLng);
                    }

                    let targetNeedle = 0;
                    if (this.bearing !== null) {
                        const heading = (this.deviceHeading !== null && !isNaN(this.deviceHeading)) ? this.deviceHeading : 0;
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
                    if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                        DeviceOrientationEvent.requestPermission()
                            .then(response => {
                                this.permissionState = response;
                                if (response === 'granted') {
                                    this.needsPermission = false;
                                    this.startOrientationListener();
                                } else {
                                    this.needsPermission = true;
                                }
                            })
                            .catch(() => {
                                this.permissionState = 'denied';
                                this.needsPermission = true;
                            });
                    } else {
                        this.permissionState = 'granted';
                        this.needsPermission = false;
                        this.startOrientationListener();
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

                    if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                        this.needsPermission = true;
                        this.permissionState = 'prompt';
                    } else {
                        this.needsPermission = false;
                        this.startOrientationListener();
                    }

                    const el = document.getElementById('distance-display');
                    if (!navigator.geolocation) {
                        this.distanceText = 'Géolocalisation non supportée par votre navigateur.';
                        if (el) el.innerText = 'Distance : ' + this.distanceText;
                        return;
                    }

                    this.watchId = navigator.geolocation.watchPosition(
                        (pos) => this.onGpsPosition(pos),
                        (err) => {
                            this.distanceText = 'Signal GPS indisponible (' + err.message + ')';
                            if (el) el.innerText = 'Distance : ' + this.distanceText;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
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
            }" x-init="init()" style="display: flex; flex-direction: column; align-items: center; width: 100%;">

                <!-- Permission iOS Safari -->
                <div id="compass-permission-section" x-show="needsPermission" style="width: 100%; margin-bottom: 12px; background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 12px; padding: 10px; text-align: center;">
                    <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 700; color: #92400E;">Permission boussole requise sur smartphone :</p>
                    <button type="button"
                            id="request-orientation-btn"
                            x-on:click="requestOrientationPermission()"
                            style="padding: 6px 14px; background: #841818; color: #ffffff; border: 2px solid #1A1A1A; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer;">
                        Activer la boussole du téléphone
                    </button>
                </div>

                <h4 style="margin: 0 0 4px 0; font-size: 13px; font-weight: 900; color: #841818; text-transform: uppercase; letter-spacing: 0.5px;">Direction la cible</h4>

                <!-- Boussole Centrale Géante (Fidèle à la photo media_1794327054753_4b510fd0.png) -->
                <div style="position: relative; margin: 10px auto; width: 260px; height: 260px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 200 200" width="200" height="200" aria-label="Boussole d'orientation vers la cible" style="width: 250px; height: 250px; display: block;">
                        <!-- Cadran extérieur vert sauge cerclé de noir -->
                        <circle cx="100" cy="100" r="94" fill="#9FB8AD" stroke="#1A1A1A" stroke-width="6" />

                        <!-- Cadran crème intermédiaire avec cercles concentriques noirs -->
                        <circle cx="100" cy="100" r="78" fill="#F4EBD9" stroke="#1A1A1A" stroke-width="5" />
                        <circle cx="100" cy="100" r="50" fill="none" stroke="#1A1A1A" stroke-width="4.5" />

                        <!-- Repère cardinal fixe DEVANT -->
                        <text x="100" y="24" text-anchor="middle" font-size="9" font-weight="900" fill="#1A1A1A" opacity="0.75" letter-spacing="1">DEVANT</text>

                        <!-- Aiguille losange or à contour noir avec pivot central rouge (Rotation 60 FPS réactive) -->
                        <g :transform="'rotate(' + needleAngle + ' 100 100)'" style="transition: transform 0.08s ease-out; transform-origin: 100px 100px;">
                            <!-- Aiguille losange unifiée or à contour noir franc -->
                            <polygon points="100,24 124,100 100,176 76,100"
                                     fill="#F4B316"
                                     stroke="#1A1A1A"
                                     stroke-width="4.5"
                                     stroke-linejoin="round" />

                            <!-- Pointe Cible (partie supérieure pour référence/accessibilité) -->
                            <polygon id="compass-needle-target"
                                     points="100,24 124,100 76,100"
                                     fill="#F4B316"
                                     stroke="none" />

                            <!-- Pointe Opposée (partie inférieure) -->
                            <polygon id="compass-needle-opposite"
                                     points="100,176 124,100 76,100"
                                     fill="#F4B316"
                                     stroke="none" />

                            <!-- Pivot central rouge vif à contour noir franc -->
                            <circle cx="100" cy="100" r="14" fill="#B91C1C" stroke="#1A1A1A" stroke-width="4.5" />
                        </g>
                    </svg>
                </div>

                <!-- Card inférieure avec outline noir franc (Fidèle à la maquette) -->
                <div style="position: relative; width: 100%; max-width: 360px; margin: 10px auto 0 auto;">
                    <!-- Ailes d'arrière-plan de la carte (effet deck de jeu) -->
                    <div style="position: absolute; inset: 0; background: #D1D5DB; border: 3px solid #1A1A1A; border-radius: 20px; transform: scale(1.05, 0.96); z-index: 1;"></div>

                    <!-- Carte principale blanche -->
                    <div style="position: relative; z-index: 2; background: #FFFFFF; border: 3.5px solid #1A1A1A; border-radius: 20px; padding: 20px; box-shadow: 0 6px 0 #1A1A1A;">
                        <h3 style="font-size: 20px; font-weight: 900; margin: 0 0 8px 0; color: #1A1A1A;">
                            Destination : <span style="color: #F4B316;">{{ $step->name }}</span>
                        </h3>

                        <p style="font-size: 13px; color: #4B5563; line-height: 1.4; margin: 0 0 18px 0;">
                            Suivez la boussole, observez autour de vous et trouvez le spot
                        </p>

                        <!-- Gros bouton d'action en bas de carte -->
                        <div style="width: 100%;">
                            <!-- Si distance > 30m : affiche le nombre de mètres restant en gros (ex: '300 m', fond bordeaux) -->
                            <div x-show="distanceMeters === null || distanceMeters > 30"
                                 id="hub-distance-metric-btn"
                                 style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #841818; color: #FFFFFF; font-size: 26px; font-weight: 900; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; letter-spacing: 1px;">
                                <span id="metric-distance-text" x-text="distanceMeters !== null ? distanceMeters + ' m' : '300 m'">300 m</span>
                            </div>

                            <!-- Si distance <= 30m : se transforme en bouton interactif 'Scanner' (fond or/vert) -->
                            <button type="button"
                                    x-show="distanceMeters !== null && distanceMeters <= 30"
                                    id="hub-scanner-action-btn"
                                    class="btn-bounce"
                                    x-on:click="tab = 'scanner'"
                                    wire:click="$dispatch('set-tab', { tab: 'scanner' })"
                                    style="width: 100%; box-sizing: border-box; padding: 14px 16px; background: #F4B316; color: #1A1A1A; font-size: 22px; font-weight: 900; border: 3px solid #1A1A1A; border-radius: 14px; box-shadow: 0 5px 0 #1A1A1A; text-align: center; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;">
                                Scanner le spot
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Informations techniques, guidage et outils de test GPS (repliables) -->
                <div style="width: 100%; max-width: 360px; margin-top: 14px;">
                    <details style="background: #F9FAFB; border: 2px solid #E5E7EB; border-radius: 12px; padding: 10px 14px; font-size: 12px; color: #4B5563;">
                        <summary style="font-weight: 700; cursor: pointer; color: #6B7280;">Détails GPS, capteurs & console de test</summary>

                        <div style="margin-top: 10px;">
                            <p id="distance-display" style="margin: 4px 0;">Distance : Recherche de position GPS en cours...</p>
                            <p id="bearing-display" style="margin: 4px 0;">Cap vers la cible : <strong x-text="bearing !== null ? Math.round(bearing) + '°' : 'En attente GPS...'">En attente GPS...</strong></p>
                            <p id="heading-display" style="margin: 4px 0;">Orientation du téléphone : <strong x-text="deviceHeading !== null ? Math.round(deviceHeading) + '° (' + headingSource + ')' : 'En attente capteur/mouvement...'">En attente capteur/mouvement...</strong></p>
                            <p id="needle-display" style="margin: 4px 0;">Angle de l'aiguille : <strong x-text="needleAngle !== null ? needleAngle + '°' : '0°'">0°</strong></p>
                            <p id="guidance-display" style="margin: 4px 0;">Indication : <strong x-text="getGuidanceText()">Recherche de cap en cours...</strong></p>
                        </div>

                        <div wire:ignore style="margin-top: 12px; border-top: 1px dashed #D1D5DB; padding-top: 8px;">
                            <p style="margin: 4px 0;"><small>Cible active : <strong x-text="targetLabel">Monument de l’étape</strong> (Lat <span x-text="targetLat">{{ $step->lat }}</span>, Lng <span x-text="targetLng">{{ $step->lng }}</span>)</small></p>
                            <p style="margin: 4px 0;"><small>Cible alternative : 50°26'15.6"N 2°48'33.6"E (50.4376667, 2.8093333)</small></p>

                            <div style="display: flex; gap: 6px; flex-wrap: wrap; margin: 8px 0;">
                                <button type="button" id="debug-target-btn" x-on:click="setDebugTarget()" style="padding: 4px 8px; font-size: 11px;">Cible debug</button>
                                <button type="button" id="reset-step-target-btn" x-on:click="resetStepTarget()" style="padding: 4px 8px; font-size: 11px;">Cible étape</button>
                                <button type="button" id="reset-live-gps-btn" x-show="isMocking" x-on:click="resetLiveSensors()" style="padding: 4px 8px; font-size: 11px;">Capteurs réels</button>
                            </div>

                            <div style="display: grid; grid-template-columns: auto 1fr; gap: 4px; align-items: center; margin-top: 6px;">
                                <label for="mock-lat">Lat :</label>
                                <input type="text" id="mock-lat" x-model="mockLat" value="{{ $step->lat }}" style="font-size: 11px; padding: 2px 4px;">
                                <label for="mock-lng">Lng :</label>
                                <input type="text" id="mock-lng" x-model="mockLng" value="{{ $step->lng + 0.001 }}" style="font-size: 11px; padding: 2px 4px;">
                                <label for="mock-heading">Cap (°) :</label>
                                <input type="text" id="mock-heading" x-model="mockHeading" value="0" style="font-size: 11px; padding: 2px 4px;">
                            </div>

                            <button type="button" id="mock-gps-btn" x-on:click="calculateMock()" style="margin-top: 6px; width: 100%; padding: 4px; font-size: 11px; font-weight: 700;">
                                Calculer avec coordonnées simulées
                            </button>
                        </div>
                    </details>
                </div>
            </div>
        @endif
    @endif
</div>
