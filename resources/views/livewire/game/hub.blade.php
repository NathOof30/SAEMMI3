<div>
    <h2>Boussole / Hub</h2>

    @if($isFinished)
        <p>Toutes les étapes ont été complétées ! Rendez-vous dans l'onglet <strong>[Inventaire]</strong> pour découvrir le code secret final.</p>
    @elseif(!$step)
        <p>Aucune étape active disponible.</p>
    @else
        <h3>{{ $step->name }} (Étape {{ $user?->current_step ?? 1 }}/6)</h3>

        @if($user && $user->phase === 'GUESSING_LOCATION')
            <div>
                <h4>Phase A : Recherche sur la carte</h4>
                <p><strong>Énigme :</strong> {{ $step->riddle_text }}</p>
                <p>L'Enfant (Explorateur) cherche le lieu sur sa carte papier à l'aide du filtre optique. Le Parent scanne ensuite le QR code correspondant sur la carte (ex: <code>MAP_LOC_{{ $step->id }}</code>) dans l'onglet <strong>[Scanner]</strong>.</p>
            </div>
        @elseif($user && $user->phase === 'WALKING_TO_TARGET')
            <div>
                <h4>Phase B : Déplacement physique vers le lieu</h4>
                <p>La famille marche jusqu'au monument réel à Lens en suivant la boussole GPS ci-dessous. Sur place, réalisez le défi physique puis scannez le QR code de l'animateur (ex: <code>MJ_STEP_{{ $step->id }}</code>) dans l'onglet <strong>[Scanner]</strong>.</p>
            </div>
            <div x-data="{
                targetLat: {{ $step->lat }},
                targetLng: {{ $step->lng }},
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
                    this.displayedAngle = (this.displayedAngle + diff * 0.35);
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
            }" x-init="init()">
                <h4>Direction la cible</h4>
                <p id="distance-display">Distance : Recherche de position GPS en cours...</p>

                <div id="compass-permission-section" x-show="needsPermission">
                    <p><small>Sur smartphone (iOS Safari), l'accès à la boussole électronique nécessite votre permission explicite :</small></p>
                    <button type="button" id="request-orientation-btn" x-on:click="requestOrientationPermission()">Activer la boussole du téléphone</button>
                </div>

                <figure>
                    <svg viewBox="0 0 200 200" width="200" height="200" aria-label="Boussole d'orientation vers la cible">
                        <!-- Cadran extérieur de la boussole -->
                        <circle cx="100" cy="100" r="95" fill="none" stroke="black" stroke-width="2" />
                        <circle cx="100" cy="100" r="85" fill="none" stroke="gray" stroke-width="1" stroke-dasharray="3,5" />

                        <!-- Repères cardinaux fixes du téléphone (Haut = Devant l'utilisateur) -->
                        <text x="100" y="22" text-anchor="middle" font-size="12" font-weight="bold" fill="black">DEVANT</text>
                        <text x="180" y="104" text-anchor="middle" font-size="12" font-weight="bold" fill="gray">D</text>
                        <text x="100" y="190" text-anchor="middle" font-size="12" font-weight="bold" fill="gray">ARR</text>
                        <text x="20" y="104" text-anchor="middle" font-size="12" font-weight="bold" fill="gray">G</text>

                        <!-- Aiguille orientée vers la cible (interpolée sur le plus court chemin) -->
                        <g :transform="'rotate(' + needleAngle + ' 100 100)'">
                            <!-- Pointe Cible (flèche rouge vers la destination) -->
                            <polygon id="compass-needle-target" points="100,26 112,100 88,100" fill="red" />
                            <!-- Pointe Opposée (flèche foncée) -->
                            <polygon id="compass-needle-opposite" points="100,174 112,100 88,100" fill="black" />
                            <!-- Pivot central -->
                            <circle cx="100" cy="100" r="7" fill="black" />
                            <circle cx="100" cy="100" r="3" fill="white" />
                        </g>
                    </svg>
                    <figcaption><small>Flèche rouge = direction physique du monument à suivre dans la rue.</small></figcaption>
                </figure>

                <div>
                    <p id="bearing-display">Cap vers la cible : <strong x-text="bearing !== null ? Math.round(bearing) + '°' : 'En attente GPS...'">En attente GPS...</strong></p>
                    <p id="heading-display">Orientation du téléphone : <strong x-text="deviceHeading !== null ? Math.round(deviceHeading) + '° (' + headingSource + ')' : 'En attente capteur/mouvement...'">En attente capteur/mouvement...</strong></p>
                    <p id="needle-display">Angle de l'aiguille : <strong x-text="needleAngle !== null ? needleAngle + '°' : '0°'">0°</strong></p>
                    <p id="guidance-display">Indication : <strong x-text="getGuidanceText()">Recherche de cap en cours...</strong></p>
                </div>

                <div wire:ignore>
                    <p><small>(Cible : Lat {{ $step->lat }}, Lng {{ $step->lng }})</small></p>

                    <div>
                        <h5>Test manuel GPS & Boussole (Debug)</h5>
                        <label for="mock-lat">Lat :</label>
                        <input type="text" id="mock-lat" x-model="mockLat" value="{{ $step->lat }}">
                        <label for="mock-lng">Lng :</label>
                        <input type="text" id="mock-lng" x-model="mockLng" value="{{ $step->lng + 0.001 }}">
                        <label for="mock-heading">Cap boussole (°) :</label>
                        <input type="text" id="mock-heading" x-model="mockHeading" value="0">
                        <button type="button" id="mock-gps-btn" x-on:click="calculateMock()">Calculer avec ces coordonnées</button>
                        <button type="button" id="reset-live-gps-btn" x-show="isMocking" x-on:click="resetLiveSensors()">Reprendre capteurs réels</button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
