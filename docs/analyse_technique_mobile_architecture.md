# Expédition Lens — Analyse Technique & Architecturale Mobile

> **Projet :** Chasse au trésor IRL familiale à Lens (Destination Lens Tourisme & Asmodee)  
> **Contexte :** Web App PWA mobile utilisant Laravel 11, Livewire 3 et Alpine.js  
> **Objectif du document :** Répondre aux interrogations fondamentales sur le test sur smartphone réel, l'accès aux capteurs matériels (Caméra, GPS, Boussole/Gyroscope), la pertinence de la stack Livewire vs SPA/React, la stratégie d'hébergement et l'interdépendance des choix techniques.

---

## Sommaire

1. [Problématique n°1 : Tester sur smartphone réel & Accès Matériel (HTTPS / Secure Context)](#1-problématique-n1--tester-sur-smartphone-réel--accès-matériel-https--secure-context)
   - [Le verrou de sécurité des navigateurs mobiles](#le-verrou-de-sécurité-des-navigateurs-mobiles)
   - [Solutions concrètes de test local sur smartphone](#solutions-concrètes-de-test-local-sur-smartphone)
   - [Spécificités et pièges des capteurs (iOS vs Android)](#spécificités-et-pièges-des-capteurs-ios-vs-android)
2. [Problématique n°2 : Calcul Mathématique de la Boussole Intelligente IRL](#2-problématique-n2--calcul-mathématique-de-la-boussole-intelligente-irl)
   - [Pourquoi les seules coordonnées GPS ne suffisent pas](#pourquoi-les-seules-coordonnées-gps-ne-suffisent-pas)
   - [La formule du cap (Bearing) et de l'orientation (Heading)](#la-formule-du-cap-bearing-et-de-lorientation-heading)
   - [Fréquence de rafraîchissement (60 FPS) et contrainte réseau](#fréquence-de-rafraîchissement-60-fps-et-contrainte-réseau)
3. [Problématique n°3 : Pertinence de la Stack — Livewire 3 vs React / SPA](#3-problématique-n3--pertinence-de-la-stack--livewire-3-vs-react--spa)
   - [Comment fonctionne Livewire et comment il NE DOIT PAS être utilisé](#comment-fonctionne-livewire-et-comment-il-ne-doit-pas-être-utilisé)
   - [L'architecture en "Îlots" : Livewire + Alpine.js (Analyse du code actuel)](#larchitecture-en-îlots--livewire--alpinejs-analyse-du-code-actuel)
   - [Comparatif exhaustif : Livewire 3 + Alpine.js vs SPA React/Vue](#comparatif-exhaustif--livewire-3--alpinejs-vs-spa-reactvue)
   - [Verdict technique & Recommandation](#verdict-technique--recommandation)
4. [Problématique n°4 : Hébergement & Déploiement](#4-problématique-n4--hébergement--déploiement)
   - [Contraintes techniques indispensables](#contraintes-techniques-indispensables)
   - [Comparatif des plateformes d'hébergement](#comparatif-des-plateformes-dhébergement)
   - [Gestion de la base de données (SQLite vs MySQL/PostgreSQL)](#gestion-de-la-base-de-données-sqlite-vs-mysqlpostgresql)
5. [Matrice des Dépendances Systémiques & Effets en Cascade](#5-matrice-des-dépendances-systémiques--effets-en-cascade)
   - [Graphe des dépendances croisées](#graphe-des-dépendances-croisées)
   - [Tableau d'impact décisionnel](#tableau-dimpact-décisionnel)
6. [Plan d'Action Recommandé & Guide Pas à Pas](#6-plan-daction-recommandé--guide-pas-à-pas)

---

## 1. Problématique n°1 : Tester sur smartphone réel & Accès Matériel (HTTPS / Secure Context)

### Le verrou de sécurité des navigateurs mobiles

Lorsqu'on développe sur PC, tester une application mobile via l'adresse IP locale (ex: `http://192.168.1.45:8000`) depuis un smartphone connecté au même réseau Wi-Fi **bloque immédiatement les fonctionnalités matérielles critiques**.

Les spécifications du W3C imposent la notion de **Secure Context** (`window.isSecureContext === true`) pour débloquer les APIs sensibles :
- **`navigator.mediaDevices.getUserMedia` (Caméra pour le scanner QR) :** Bloqué ou indéfini sur HTTP non-sécurisé.
- **`navigator.geolocation.watchPosition` (GPS haute précision) :** Refusé ou bridé avec une erreur `GeolocationPositionError: Only secure origins are allowed`.
- **`DeviceOrientationEvent` & `DeviceMotionEvent` (Gyroscope / Boussole) :** Les angles `alpha`, `beta`, `gamma` et `webkitCompassHeading` renvoient `null` ou ne déclenchent aucun événement.
- **`DeviceOrientationEvent.requestPermission()` (iOS Safari 13+) :** La méthode n'existe tout simplement pas hors contexte sécurisé (lève une exception `TypeError`).

> **Règle absolue :** La seule exception au HTTPS est `localhost` (`127.0.0.1`). Mais un smartphone accédant au PC via son IP Wi-Fi n'est **pas** sur `localhost` : il est considéré comme une connexion tierce non chiffrée.

---

### Solutions concrètes de test local sur smartphone

Voici les 4 méthodes éprouvées, classées de la plus simple et efficace à la plus avancée :

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          SOLUTIONS DE TEST MOBILE                           │
├───────────────────────┬──────────────────────┬──────────────────────────────┤
│ Méthode               │ Compatibilité OS     │ Avantages / Inconvénients    │
├───────────────────────┼──────────────────────┼──────────────────────────────┤
│ 1. Cloudflare Tunnel  │ iOS & Android (100%) │ URL HTTPS réelle instantanée │
│    (Recommandé n°1)   │ (Wi-Fi ou 4G)        │ Zéro install mobile, gratuit │
├───────────────────────┼──────────────────────┼──────────────────────────────┤
│ 2. Chrome USB Debug   │ Android uniquement   │ 0 latence, inspecteur PC     │
│    (Port Forwarding)  │                      │ Détecté comme "localhost"    │
├───────────────────────┼──────────────────────┼──────────────────────────────┤
│ 3. Ngrok / Expose     │ iOS & Android        │ Compte requis, écran warning │
│                       │                      │ sur version gratuite         │
├───────────────────────┼──────────────────────┼──────────────────────────────┤
│ 4. mkcert (SSL local) │ iOS & Android        │ Fonctionne sans internet     │
│                       │                      │ Certificat racine à installer│
└───────────────────────┴──────────────────────┴──────────────────────────────┘
```

#### Solution 1 : Cloudflare Tunnel (Recommandé — Rapide, gratuit, iOS + Android)
Cette méthode crée un tunnel sécurisé éphémère avec un certificat SSL Cloudflare officiel, parfaitement reconnu par Safari iOS et Chrome Android, sans ouvrir de ports sur votre box internet.

1. Lancer le serveur local Laravel sur le PC :
   ```bash
   php artisan serve --port=8000
   ```
2. Dans un second terminal, générer le tunnel HTTPS via `npx` (Node.js est déjà présent sur votre machine) :
   ```bash
   npx untun@latest tunnel http://localhost:8000
   ```
   *(ou télécharger directement le binaire officiel `cloudflared.exe` et lancer : `cloudflared tunnel --url http://localhost:8000`)*.
3. Le terminal affiche une URL publique HTTPS sécurisée du type :
   `https://random-phrase-xyz.trycloudflare.com`
4. Flasher le QR code de cette URL ou la copier sur votre smartphone (en Wi-Fi ou même en 4G dans la rue) :
   - **Caméra opérationnelle.**
   - **GPS haute précision opérationnel.**
   - **Capteurs d'orientation gyroscopiques opérationnels.**

#### Solution 2 : Chrome USB Debugging + Port Forwarding (Idéal pour Android)
Si vous possédez un smartphone Android, cette méthode est la plus performante car elle offre une latence nulle et permet de débugger en direct sur l'écran du PC.

1. Activer les **Options pour les développeurs** sur le smartphone Android, puis activer le **Débogage USB**.
2. Brancher le smartphone au PC par câble USB.
3. Sur le PC, ouvrir Google Chrome et naviguer vers : `chrome://inspect/#devices`.
4. Cliquer sur **Port forwarding...** et configurer :
   - Port : `8000`
   - IP target : `localhost:8000`
   - Cocher `Enable port forwarding`.
5. Sur le smartphone, ouvrir Google Chrome et taper simplement :
   `http://localhost:8000`
6. **Pourquoi ça marche ?** Parce que l'URL est littéralement `localhost`, Android Chrome accorde **toutes les autorisations de sécurité matérielles** (Secure Context) sans exiger de certificat HTTPS !
7. Vous pouvez inspecter la console JavaScript de votre téléphone, voir les erreurs en direct et inspecter le DOM depuis votre PC.

---

### Spécificités et pièges des capteurs (iOS vs Android)

L'accès aux capteurs physiques sur mobile n'est pas standardisé de la même façon entre Apple et Google :

1. **iOS Safari (iOS 13+) — La permission explicite sur interaction utilisateur :**
   - Sur iPhone, l'accès à la boussole (`DeviceOrientationEvent`) **ne peut pas démarrer automatiquement au chargement de la page**.
   - Il nécessite obligatoirement l'appel à `DeviceOrientationEvent.requestPermission()` déclenché par un geste utilisateur (clic sur un bouton `x-on:click`).
   - Si la permission est accordée, Safari fournit le cap absolu via une propriété non standard mais très précise : `event.webkitCompassHeading` (0° = Nord géographique, 90° = Est).

2. **Android Chrome — Le cap relatif vs absolu :**
   - Android ne fournit pas `webkitCompassHeading`.
   - Il fournit `event.alpha` sur l'événement `deviceorientation`. Cependant, `alpha` est souvent calculé par rapport à l'orientation de départ du téléphone (cap relatif) et non par rapport au pôle Nord.
   - Pour obtenir le cap géographique absolu sur Android, il faut écouter l'événement standard `deviceorientationabsolute` :
     ```javascript
     window.addEventListener('deviceorientationabsolute', (event) => {
         const heading = 360 - event.alpha; // Cap en degrés par rapport au Nord
     });
     ```

3. **Interférences magnétiques réelles à Lens :**
   - Les boussoles électroniques de smartphone utilisent un magnétomètre.
   - Dans le centre-ville de Lens, à proximité des voies ferrées de la gare, des lampadaires métalliques ou des structures métalliques du stade Bollaert, le magnétomètre subit une dérive (drift).
   - Une calibration (faire un "8" avec le téléphone dans les airs) ou un lissage mathématique (filtre passe-bas) est nécessaire pour éviter que la flèche ne tremble frénétiquement.

---

## 2. Problématique n°2 : Calcul Mathématique de la Boussole Intelligente IRL

### Pourquoi les seules coordonnées GPS ne suffisent pas

L'application doit guider une famille d'une étape A vers une étape B (monument historique).
Un GPS standard donne deux points :
- La position actuelle de la famille : $(lat_{\text{user}}, lng_{\text{user}})$
- La position cible du monument : $(lat_{\text{target}}, lng_{\text{target}})$

Grâce à ces deux points, on peut calculer l'azimut géographique (**Bearing**), par exemple : *"Le monument est à 45° Nord-Est"*.

**Mais si l'utilisateur tient son téléphone face à l'Ouest (270°), où doit pointer la flèche sur l'écran ?**
Si la flèche pointe simplement à 45° sur l'écran, elle pointera vers la droite de l'utilisateur, ce qui est faux !  
Pour que la flèche pointe réellement dans la direction physique du monument dans la rue, il faut déduire l'orientation spatiale du smartphone (**Heading**).

```
                      Nord Géographique (0°)
                               ▲
                               │
               Bearing (45°)   │
                    \          │
                     \         │
                      \        │
                       \       │
                        \      │
                         \     │
                          \    │
                           \   │
  Ouest (270°) ───────────── Phone ─────────────► Est (90°)
                            │ ▲
                            │ │ Phone Heading (330°)
                            │ │ (le haut du téléphone pointe ici)
```

---

### La formule du cap (Bearing) et de l'orientation (Heading)

Le calcul s'effectue en deux étapes vectorielles :

#### Étape 1 : Calcul de l'azimut géographique (Bearing) vers la cible
On utilise la trigonométrie sphérique (formule de grand cercle) :

$$\Delta \lambda = (lng_{\text{target}} - lng_{\text{user}}) \times \frac{\pi}{180}$$

$$\varphi_1 = lat_{\text{user}} \times \frac{\pi}{180}, \quad \varphi_2 = lat_{\text{target}} \times \frac{\pi}{180}$$

$$y = \sin(\Delta \lambda) \cdot \cos(\varphi_2)$$

$$x = \cos(\varphi_1) \cdot \sin(\varphi_2) - \sin(\varphi_1) \cdot \cos(\varphi_2) \cdot \cos(\Delta \lambda)$$

$$\theta_{\text{bearing}} = (\operatorname{atan2}(y, x) \times \frac{180}{\pi} + 360) \pmod{360}$$

#### Étape 2 : Récupération du cap du téléphone (Heading)
- **Sur iOS :** $H = \text{event.webkitCompassHeading}$
- **Sur Android :** $H = (360 - \text{event.alpha}) \pmod{360}$ (avec `deviceorientationabsolute`)

#### Étape 3 : Calcul de l'angle de rotation de l'aiguille à l'écran

$$\alpha_{\text{aiguille}} = (\theta_{\text{bearing}} - H + 360) \pmod{360}$$

On applique ensuite simplement cet angle en CSS sur l'icône de l'aiguille :
```css
transform: rotate(${needleAngle}deg);
transition: transform 0.1s ease-out;
```

---

### Fréquence de rafraîchissement (60 FPS) et contrainte réseau

Lorsque l'utilisateur tourne sur lui-même avec son smartphone dans la main, les capteurs gyroscopiques émettent des événements à **60 Hz (voire 120 Hz sur les écrans modernes)**, soit un événement toutes les 8 à 16 millisecondes.

**La règle d'or architecturale :**
> **AUCUNE requête réseau ne doit être émise vers le serveur pour animer la boussole ou la carte !**
> Si le calcul d'orientation ou la rotation de l'aiguille passait par le serveur (via Livewire ou un appel API), l'application serait instantanément saturée :
> - 60 requêtes HTTP par seconde = effondrement du serveur et épuisement du forfait 4G.
> - La latence réseau 4G (50ms à 300ms) rendrait l'aiguille saccadée et injouable.

Ce calcul doit donc s'exécuter **à 100% côté client**, dans le moteur JavaScript du navigateur.

---

## 3. Problématique n°3 : Pertinence de la Stack — Livewire 3 vs React / SPA

La question posée est essentielle : **"Est-ce que Livewire est intéressant ici ? En prenant en compte une boussole qui s'actualise fluidement, est-ce qu'un React ne serait pas plus intéressant ?"**

### Comment fonctionne Livewire et comment il NE DOIT PAS être utilisé

- **Ce que Livewire fait :** Livewire est un framework d'orchestration côté serveur pour Blade. Lorsqu'un utilisateur clique sur un bouton ou soumet un formulaire, Livewire envoie une requête AJAX avec le diff d'état, exécute le PHP, et renvoie un fragment HTML pour mettre à jour le DOM sans recharger la page.
- **Ce que Livewire NE FAIT PAS :** Livewire n'a jamais été conçu pour calculer des frames d'animation à 60 FPS, ni pour traiter des flux de caméra vidéo ou des données gyroscopiques continues.
- **Le piège à éviter :** Faire un `$wire.set('phoneHeading', angle)` sur chaque événement de rotation.

---

### L'architecture en "Îlots" : Livewire + Alpine.js (Analyse du code actuel)

Dans le projet actuel `expedition-lens`, examinons comment le code est déjà découpé :

1. **Dans [`resources/views/livewire/game/hub.blade.php`](file:///c:/Users/natha/OneDrive/Documents/B/BUT%20MMI/MMI3/projetMulComWeb/base/expedition-lens/resources/views/livewire/game/hub.blade.php) :**
   Le calcul de distance GPS est déjà confié à un composant client **Alpine.js** :
   ```html
   <div x-data="{
       targetLat: 50.4293,
       targetLng: 2.8315,
       distanceText: 'Recherche de position GPS en cours...',
       haversineDistance(...) { ... },
       init() {
           navigator.geolocation.watchPosition((pos) => {
               this.updateDistance(pos.coords.latitude, pos.coords.longitude);
           });
       }
   }">
       <p id="distance-display">Distance : ...</p>
   </div>
   ```
   **Constat :** Les coordonnées GPS reçues par `watchPosition` **ne font aucun aller-retour avec Laravel**. Alpine.js calcule la distance localement en JavaScript et modifie le DOM en mémoire.

2. **Dans [`resources/views/livewire/game/scanner.blade.php`](file:///c:/Users/natha/OneDrive/Documents/B/BUT%20MMI/MMI3/projetMulComWeb/base/expedition-lens/resources/views/livewire/game/scanner.blade.php) :**
   Le flux vidéo de la caméra tourne à 10 FPS entièrement dans le navigateur via `Html5QrcodeScanner`.
   **Livewire n'intervient qu'au moment précis où un QR code est détecté** (`@this.verifyCode(scannedText)`).

Cette approche s'appelle l'**Architecture en Îlots (Islands Architecture)** :
- **Serveur (Livewire) :** Machine à états du jeu (étapes 1 à 6, validation sécurisée des énigmes, attribution des fragments, persistance en base de données).
- **Client (Alpine.js) :** Gestion matérielle haute fréquence (animation de la boussole, rendu Leaflet, flux caméra).

---

### Comparatif exhaustif : Livewire 3 + Alpine.js vs SPA React/Vue

| Critère d'évaluation | Livewire 3 + Alpine.js (Actuel) | SPA React / Vue (Full Client) | Analyse & Impact |
|---|---|---|---|
| **Fluidité Boussole (60 FPS)** | **Excellente (60 FPS)** via Alpine.js + CSS transform | **Excellente (60 FPS)** via State React/Zustand | Égalité : les deux tournent en JavaScript local natif dans le navigateur. |
| **Intégration Carte Leaflet** | **Très simple** : `wire:ignore` + Alpine.js classique | **Complexe / Piégeuse** : `react-leaflet` se heurte souvent aux cycles de rendu du Virtual DOM | Avantage Livewire + Alpine : Leaflet manipule directement le DOM sans conflit de Virtual DOM. |
| **Sécurité & Anti-Triche** | **Maximale** : La validation des codes et les coordonnées futures restent sur le serveur PHP | **Faible / Vulnérable** : Si l'app est offline/SPA, les réponses et coordonnées sont exposées dans le bundle JS | Avantage majeur Livewire : impossible pour un joueur d'ouvrir DevTools et de tricher. |
| **Complexité de Développement** | **Faible** : 1 seul projet, pas d'API REST à créer, pas de tokens JWT/Sanctum, tests PHPUnit unifiés | **Élevée** : Réécriture complète, 2 codebases (Laravel API + React front), gestion CORS, synchronisation d'état | Réécrire en React quadruplerait le temps de développement sans bénéfice utilisateur. |
| **Gestion du Hors-Ligne (Offline)** | **Partielle** : Nécessite un Service Worker PWA pour cacher les assets ; validation des scans en ligne | **Native** : Facile avec Redux / Dexie (IndexedDB) pour stocker les scans hors ligne | Avantage React si le jeu doit être joué sans aucune connexion 4G. |
| **Taille du Bundle téléchargé** | **Légère (~80 Ko)** : HTML rendu par le serveur + runtime Alpine minimal | **Lourde (~300 Ko - 1 Mo)** : Runtime React + React-DOM + bibliothèques tierces | Avantage Livewire pour le chargement rapide sur réseau 4G instable. |

---

### Verdict technique & Recommandation

**Conclusion sans équivoque : Il ne faut PAS réécrire l'application en React.**
1. **Alpine.js est déjà un framework réactif complet :** Il possède la même réactivité que Vue ou React, mais sans le poids d'un Virtual DOM et sans configuration de build complexe.
2. **Pour la boussole et Leaflet :** Alpine.js est même **plus adapté** que React, car Leaflet et les capteurs d'orientation sont des APIs impératives. Avec `wire:ignore`, Alpine gère le canvas/SVG de l'aiguille et la carte Leaflet à 60 FPS sans aucune interférence de Livewire.
3. **Conserver Livewire pour le cœur métier :** Livewire est irremplaçable pour sécuriser le jeu (vérifier que le QR code scanné correspond bien à l'étape en cours de l'équipe sans divulguer les étapes suivantes).

---

## 4. Problématique n°4 : Hébergement & Déploiement

La question posée : **"Si je dois héberger, comment ? Où ? Ici il y a des choses qui tournent sur l'écran sans avoir forcément besoin de faire des requêtes à la base de données. On est parti dans le dev sans vraiment se poser toutes ces questions."**

### Contraintes techniques indispensables

Pour faire fonctionner Expédition Lens en conditions réelles, l'hébergement doit respecter :
1. **Certificat SSL / HTTPS obligatoire :** Sans certificat valide, aucun smartphone ne permettra d'activer la caméra ou la boussole.
2. **PHP 8.3 & Extensions :** Support de PDO SQLite ou MySQL, Mbstring, OpenSSL.
3. **Persistance des données :** Si SQLite est utilisé, le fichier `database/database.sqlite` ne doit pas être écrasé à chaque redéploiement (volume persistant).
4. **Zéro mise en veille (Anti Cold-Start) :** Si un conteneur s'endort après 15 minutes d'inactivité, la famille qui marche 20 minutes entre deux étapes se retrouverait avec un écran figé pendant 50 secondes le temps du réveil.

---

### Comparatif des plateformes d'hébergement

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       COMPARATIF DES OPTIONS D'HÉBERGEMENT                  │
├─────────────────────┬──────────────┬───────────────┬────────────────────────┤
│ Plateforme          │ Coût estimé  │ HTTPS Auto    │ Avis pour Expédition   │
├─────────────────────┼──────────────┼───────────────┼────────────────────────┤
│ 1. Railway.app      │ Gratuit      │ Oui           │ ⭐ LE MEILLEUR CHOIX   │
│    (PaaS)           │ (5$/m offert)│ Let's Encrypt │ Déploiement Git direct │
├─────────────────────┼──────────────┼───────────────┼────────────────────────┤
│ 2. Laravel Cloud    │ Essai / Payant│ Oui          │ Solution officielle    │
│    (Serverless)     │              │ Natif Laravel │ Idéal si accès dispo   │
├─────────────────────┼──────────────┼───────────────┼────────────────────────┤
│ 3. VPS Hetzner/OVH  │ 3 à 4 €/mois │ Oui           │ Performance maximale   │
│    + Coolify        │              │ Caddy auto    │ Nécessite config Linux │
├─────────────────────┼──────────────┼───────────────┼────────────────────────┤
│ 4. Render.com       │ Gratuit      │ Oui           │ ⚠️ DÉCONSEILLÉ         │
│                     │              │               │ Mise en veille 50s     │
├─────────────────────┼──────────────┼───────────────┼────────────────────────┤
│ 5. Vercel / Netlify │ Gratuit      │ Oui           │ ❌ INADAPTÉ            │
│                     │              │               │ Conçu pour Node/JS     │
└─────────────────────┴──────────────┴───────────────┴────────────────────────┘
```

#### Recommandation n°1 : Railway.app (Idéal pour prototype, projet étudiant & production légère)
- **Fonctionnement :** On connecte le dépôt GitHub à Railway. Railway détecte automatiquement le projet Laravel, compile les assets Vite (`npm run build`), lance les migrations et génère une URL HTTPS publique (ex: `https://expedition-lens.up.railway.app`).
- **Avantages :** 
  - Offre un crédit gratuit de 5$ chaque mois (amplement suffisant pour le projet).
  - Fournit un certificat HTTPS automatique et valide.
  - Permet d'attacher un volume persistant pour garder la base SQLite, ou d'instancier un MySQL en 1 clic.
  - Ne s'endort pas comme Render.

#### Recommandation n°2 : Laravel Cloud
- Plateforme officielle annoncée par Laravel pour déployer des applications Laravel sans gérer de serveur. Si vous disposez d'un compte ou d'un accès anticipé, le déploiement se fait sans aucune configuration.

#### Pourquoi exclure Vercel et Netlify ?
Vercel et Netlify sont optimisés pour les architectures frontend (Next.js, Nuxt, React). Faire tourner Laravel sur Vercel nécessite des wrappers serverless tiers (Bref/Vercel-PHP), qui imposent des contraintes lourdes : pas de stockage local (SQLite impossible sans base cloud tierce), temps d'exécution limité et latence de démarrage à froid.

---

### Gestion de la base de données (SQLite vs MySQL/PostgreSQL)

Dans le projet actuel, la base par défaut est **SQLite**.
- **En local :** SQLite est imbattable (zéro installation de serveur de base de données).
- **En hébergement léger (Railway avec volume) :** SQLite supporte parfaitement plusieurs dizaines de familles jouant simultanément à Lens, car le nombre d'écritures est minime (un enregistrement de progression toutes les 10 à 15 minutes par famille).
- **Si déploiement multi-instances :** Si l'application devait être répliquée sur plusieurs serveurs, une base MySQL managée (fournie en 1 clic sur Railway) devient nécessaire.

---

## 5. Matrice des Dépendances Systémiques & Effets en Cascade

Comme vous l'avez très justement souligné :  
*"Certaines solutions dépendent d'autres problèmes et de solutions des autres problèmes. Il faut revoir tout ça."*

Voici l'analyse des **6 dépendances croisées critiques** du projet :

```
                                  ┌────────────────────────┐
                                  │ HTTPS / Secure Context │
                                  └───────────┬────────────┘
                                              │
                      ┌───────────────────────┴───────────────────────┐
                      ▼                                               ▼
         ┌─────────────────────────┐                     ┌─────────────────────────┐
         │ Caméra (getUserMedia)   │                     │ Capteurs d'Orientation  │
         │ & Décodage QR Code      │                     │ & Boussole 60 FPS       │
         └────────────┬────────────┘                     └────────────┬────────────┘
                      │                                               │
                      │   ┌───────────────────────────────────────┐   │
                      └──►│ Architecture Client-Side (Alpine.js)  │◄──┘
                          │ vs Serveur (Livewire / Anti-Triche)   │
                          └───────────────────┬───────────────────┘
                                              │
                      ┌───────────────────────┴───────────────────────┐
                      ▼                                               ▼
         ┌─────────────────────────┐                     ┌─────────────────────────┐
         │ Couverture 4G en ville  │                     │ Autonomie de la         │
         │ & Mode Déconnecté (PWA) │                     │ Batterie Smartphone     │
         └─────────────────────────┘                     └─────────────────────────┘
```

---

### Tableau d'impact décisionnel

| Problème Source | Solution Retenue | Effet de Bord (Nouveau Problème) | Solution du Problème Dérivé |
|---|---|---|---|
| **1. Accès Caméra & Boussole** | Nécessite un contexte HTTPS sécurisé. | En développement local, `http://192.168.x.x` est bloqué par les téléphones. | Utiliser un tunnel Cloudflare (`npx untun`) pour tester immédiatement sur smartphone avec SSL. |
| **2. Boussole sur iOS Safari** | API `DeviceOrientationEvent`. | iOS 13+ bloque les capteurs sans geste utilisateur explicite. | Ajouter un bouton "Activer la boussole" ou lier l'autorisation à l'ouverture de l'onglet Boussole. |
| **3. Fluidité de l'aiguille (60 FPS)** | Animer l'aiguille en JavaScript pur (Alpine.js). | Les coordonnées de la cible doivent être connues par le navigateur du client. | On envoie uniquement les coordonnées de l'étape *en cours* (jamais les 6 étapes d'avance), préservant le secret. |
| **4. Zones blanches 4G dans Lens** | Le joueur marche entre deux bâtiments historiques. | La carte Leaflet ne charge plus les tuiles OpenStreetMap ; Livewire échoue lors du scan. | Pré-mettre en cache les tuiles du centre-ville de Lens via un Service Worker PWA simple. |
| **5. Précision GPS vs Batterie** | `enableHighAccuracy: true` sollicite le récepteur GPS satellite en continu. | Décharge rapide de la batterie du téléphone familial lors d'une session de 2 heures. | Couper `watchPosition` quand l'utilisateur change d'onglet (ex: va dans l'Inventaire) ou verrouille l'écran (`visibilitychange`). |
| **6. Sécurité vs Offline** | Validation du code QR côté serveur (Livewire) pour éviter la triche. | Le joueur ne peut pas valider un fragment sans un minimum de réseau 4G. | La marche et la boussole sont 100% hors-ligne. Seul le "bip" de validation nécessite 1 seconde de connexion. |

---

## 6. Plan d'Action Recommandé & Guide Pas à Pas

Pour avancer sereinement sans tout démolir et sans perdre de temps, voici la feuille de route pas à pas :

### Étape 1 : Tester immédiatement sur votre smartphone (Temps estimé : 5 minutes)
1. Ouvrir votre terminal dans le projet :
   ```bash
   php artisan serve --port=8000
   ```
2. Dans un second terminal :
   ```bash
   npx untun@latest tunnel http://localhost:8000
   ```
3. Scanner l'URL HTTPS sur votre smartphone.
4. **Vérification :** Ouvrir l'onglet `[Scanner]` et constater que la caméra s'allume instantanément sans avertissement de sécurité.

---

### Étape 2 : Implémenter la Boussole Intelligente dans Alpine (Temps estimé : 30 minutes)
Dans [`resources/views/livewire/game/hub.blade.php`](file:///c:/Users/natha/OneDrive/Documents/B/BUT%20MMI/MMI3/projetMulComWeb/base/expedition-lens/resources/views/livewire/game/hub.blade.php), enrichir l'objet `x-data` existant avec :
1. Une fonction de calcul de bearing (déjà détaillée au chapitre 2).
2. Un écouteur sur l'orientation du téléphone (`deviceorientationabsolute` ou `webkitCompassHeading`).
3. Un bouton d'autorisation pour iOS :
   ```javascript
   requestOrientation() {
       if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
           DeviceOrientationEvent.requestPermission().then(state => {
               if (state === 'granted') this.listenOrientation();
           });
       } else {
           this.listenOrientation();
       }
   }
   ```
4. Une flèche SVG dont le style applique `transform: rotate(needleAngle + 'deg')`.

---

### Étape 3 : Intégrer la Carte Leaflet proprement (Temps estimé : 20 minutes)
1. Ajouter le CDN Leaflet dans [`resources/views/components/layouts/app.blade.php`](file:///c:/Users/natha/OneDrive/Documents/B/BUT%20MMI/MMI3/projetMulComWeb/base/expedition-lens/resources/views/components/layouts/app.blade.php).
2. Dans `hub.blade.php`, placer un conteneur `<div id="map" wire:ignore></div>`.
3. Le tag `wire:ignore` est capital : il ordonne à Livewire de ne jamais toucher au DOM de la carte, laissant Leaflet et Alpine la contrôler de manière fluide à 60 FPS.

---

### Étape 4 : Déployer sur Railway pour les tests réels en extérieur à Lens
1. Créer un compte sur [Railway.app](https://railway.app).
2. Créer un nouveau projet depuis votre dépôt GitHub.
3. Définir les variables d'environnement (`APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`).
4. Tester le parcours complet directement dans les rues de Lens avec la famille !

---

### Conclusion Générale

- **La stack actuelle (Laravel 11 + Livewire 3 + Alpine.js) est parfaitement adaptée et viable.**  
- Aucun changement de framework vers React n'est requis ; au contraire, Alpine.js offre la réactivité client nécessaire pour la boussole et la caméra sans la complexité ni les failles de sécurité d'une SPA pure.
- En adoptant le protocole de test HTTPS (via Cloudflare Tunnel) et en isolant les calculs haute fréquence dans Alpine.js, l'application répondra à 100% aux exigences d'une expérience mobile fluide, immersive et robuste.

