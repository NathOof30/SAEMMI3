# GALI — L'Expédition Secrète (Lens Tourisme × Asmodee)

> **Jeu de piste urbain familial hybride (kit papier physique + application mobile PWA)** au cœur de la ville de Lens.  
> L'application sert de **boussole GPS** pour guider l'équipe et de **serrure numérique** pour valider les étapes, en parfaite synergie avec la grande carte illustrée et la loupe d'observation remises au départ.

---

## Sommaire

1. [Présentation & Contexte du Projet](#1-présentation--contexte-du-projet)
2. [L'Histoire & Les 6 Fragments de Clé](#2-lhistoire--les-6-fragments-de-clé)
3. [Expérience Hybride & Répartition des Rôles](#3-expérience-hybride--répartition-des-rôles)
4. [La Boucle de Gameplay](#4-la-boucle-de-gameplay)
5. [Stack Technique & Architecture](#5-stack-technique--architecture)
6. [Outils de Démonstration & Debug](#6-outils-de-démonstration--debug)
7. [Installation & Lancement en Local](#7-installation--lancement-en-local)
8. [Connexion Smartphone via Tunnel HTTPS](#8-connexion-smartphone-via-tunnel-https)
9. [Déploiement en Production (Render & Neon)](#9-déploiement-en-production-render--neon)
10. [Tests Automatisés & Qualité du Code](#10-tests-automatisés--qualité-du-code)
11. [Arborescence du Projet](#11-arborescence-du-projet)
12. [Crédits & Équipe](#12-crédits--équipe)

---

## 1. Présentation & Contexte du Projet

Dans le cadre d'un partenariat événementiel entre **Destination Lens Tourisme** et l'éditeur de jeux de société **Asmodee**, **GALI** invite les familles à explorer la ville de Lens à travers une quête patrimoniale immersive.

L'application web mobile est accessible directement dans le navigateur du smartphone, sans aucun téléchargement sur les stores d'applications. Elle ne remplace pas le matériel physique : elle sublime la découverte sur le terrain en reliant le jeu sur table et l'exploration urbaine.

---

## 2. L'Histoire & Les 6 Fragments de Clé

L'aventure suit **Gali**, un jeune Lensois fasciné par le passé minier de sa famille :
* Dans le grenier de son grand-père, Gali a retrouvé une **carte de Lens en papier jauni**, une **loupe**, et l'**anneau de départ d'une clé antique**.
* Cette clé est la seule capable de rouvrir le coffre secret et de rallumer la légendaire **Lampe Cœur** des mineurs.
* Mais la clé a été brisée : il manque **6 fragments essentiels**, disséminés dans des lieux emblématiques de la cité minière.

### Les 6 étapes de l'expédition :

| Étape | Monument Lensois | Fragment de Clé | Thème & Patrimoine |
|:---:|---|---|---|
| **1** | **Gare de Lens** | Bord de l'anneau | Silhouette en locomotive à vapeur et passé ferroviaire |
| **2** | **Centre-Ville** | Base de l'anneau | Façades Art déco et mémoire des corons miniers |
| **3** | **Stade Bollaert-Delelis** | Col sculpté | Ferveur populaire et esprit « Sang et Or » |
| **4** | **Musée Louvre-Lens** | Fût gravé | Pavillon de verre contemporain sur l'ancien carreau de mine |
| **5** | **Terrils de Loos-en-Gohelle** | Garde du fût | Les deux géants noirs jumeaux inscrits à l'UNESCO |
| **6** | **Office de Tourisme** | Dents de la clé | Coffre final, rallumage de la Lampe Cœur et trésor |

**Le Trésor Final :**  
Une fois les 6 fragments réunis, la clé complète s'assemble dans l'Inventaire. Devant l'Office de Tourisme, l'équipe débloque son **QR Code Retrait du Trésor** pour récupérer son **véritable trésor surprise** (un lot exclusif issu de la collaboration) et se trouve **automatiquement inscrite au grand tirage au sort** de l'expédition.

---

## 3. Expérience Hybride & Répartition des Rôles

Le jeu favorise l'échange intergénérationnel en attribuant un rôle actif à chacun :

* **L'Enfant (L'Explorateur) :**  
  Il manipule le kit physique. Il observe la **grande carte papier de Lens** avec sa **loupe**, résout les énigmes visuelles de Gali, repère les symboles cachés dans la ville et participe aux défis sur les stands.
* **Le Parent (Le Guide) :**  
  Il tient le smartphone (la PWA). Il s'oriente dans les rues grâce à la **boussole GPS**, confirme l'arrivée sur le spot, et utilise le **scanner** pour flasher les repères de la carte et les badges des animateurs.

---

## 4. La Boucle de Gameplay

Chaque étape du parcours est rythmée par une machine à états rigoureuse :

```
       +-----------------------------------------------------------+
       |                  ÉTAPE EN COURS (1 à 6)                   |
       +-----------------------------------------------------------+
                                     |
                                     v
         [ PHASE A : RECHERCHE SUR LA CARTE (Anti-spoil strict) ]
         * L'application affiche uniquement l'énigme de Gali.
         * L'enfant cherche le symbole sur la carte papier avec sa loupe.
         * Le parent scanne le repère imprimé sur la carte (MAP_LOC_X).
         * Feedback visuel immédiat : validation et révélation du lieu.
                                     |
                                     v
         [ PHASE B : DÉPLACEMENT PHYSIQUE VERS LE MONUMENT ]
         * La boussole interactive s'active :
           - Pointe JAUNE OR (#F4B316) dirigée vers le monument.
           - Cadran rotatif calé en temps réel sur le vrai Nord.
         * Le carnet coulissant détaille l'anecdote historique du site.
                                     |
                                     v
         [ ÉTAPE SUR PLACE : RENCONTRE AVEC L'ANIMATEUR ]
         * Dès l'arrivée, l'équipe confirme : « Vous êtes arrivé ? ».
         * La boussole s'efface au profit du médaillon illustré du monument.
         * Consigne RP : poser le téléphone et relever le mini-jeu du stand.
         * Une fois le jeu réussi, l'équipe appuie sur « Épreuve terminée ? ».
         * Le scanner s'ouvre pour lire le badge de l'animateur (MJ_STEP_X).
                                     |
                                     v
         [ INVENTAIRE : ASSEMBLAGE DE LA CLÉ ]
         * Animation d'emboîtement du fragment d'or sur la silhouette verticale.
         * Consultation de la fiche de l'étape et retour vers la suite.
         * À l'issue de l'étape 6 : Clé complète forgée, coffre ouvert,
           génération locale du QR Code de retrait du trésor surprise.
```

---

## 5. Stack Technique & Architecture

* **Backend & Framework :** [PHP 8.3](https://www.php.net/) / [Laravel 12](https://laravel.com/)
* **Réactivité Frontend :** [Livewire](https://livewire.laravel.com/) & [Alpine.js](https://alpinejs.dev/) (architecture Single Page Application fluide sans rechargement de page).
* **Scanner Caméra QR Code :** `Html5-QRCode` headless avec algorithme intelligent de sélection de la caméra arrière principale (priorité objectif standard, élimination des capteurs ultra-grand-angle, macro ou selfie).
* **Boussole & Géolocalisation :**
  - Formule trigonométrique de relèvement géodésique **Haversine**.
  - Écouteurs d'orientation absolue (`deviceorientationabsolute` sous Android, `DeviceOrientationEvent.requestPermission()` sous iOS Safari).
  - Mémorisation permanente de la permission capteurs dans `localStorage`.
* **Direction Artistique Néo-brutaliste « Sang et Or » :**
  - Palette officielle : Rouge Sang (`#841818`), Jaune Or (`#F4B316`), Vert Sauge (`#9FB8AD`), Parchemin (`#FAF8F5`, `#F4EBD9`), contours noirs francs (2.5px à 3.5px) et ombres déportées 3D (`0 4px 0 #1A1A1A`).
  - Interface contenue dans l'écran en `100dvh` (aucun scroll parasite, fluidité mobile tactile).
* **Résilience Réseau & Keep-Alive Render/Neon :**
  - Route dédiée `GET /keep-alive` effectuant un ping SQL `SELECT 1` et maintenant la session/CSRF active.
  - Heartbeat JavaScript silencieux toutes les 3 min 30 (onglet visible) pour éviter l'endormissement du conteneur Render (15 min) et de la base Neon (5 min).
  - Écran de chargement à **2 niveaux** : pastille discrète d'arrière-plan + voile néo-brutaliste déclenché après 350 ms sur les actions critiques, avec message d'information si le serveur se réveille.
* **PWA (Progressive Web App) :** Installable sur l'écran d'accueil mobile, manifest aux couleurs immersives (`#000000`), fonctionnement de la boussole et des fiches d'histoire 100% hors ligne.

---

## 6. Outils de Démonstration & Debug

Pour faciliter les soutenances, présentations orales et tests sans avoir à arpenter les rues de Lens :

### 1. La Console Technique `[•] debug`
Accessible discrètement en haut à droite sur le Hub (en Phase A, Phase B, et même sur l'écran final) :
* **Sélecteur d'étapes immédiat :** permet de sauter directement à n'importe quelle étape (de l'Étape 1 à l'Étape 6) ou à l'Écran de Fin, en choisissant la phase (Phase A énigme ou Phase B boussole).
* **Bouton `Reset progression (Début 0/6)` :** réinitialise l'équipe au début du parcours en un clic, sans devoir créer un nouveau compte.
* **Téléportation GPS :** boutons `TP sur le spot (0m)` (simule la présence immédiate devant le monument) et `TP approche (100m)` pour observer l'aiguille de la boussole réagir.
* **Coordonnées personnalisées :** formulaire de saisie de latitude, longitude et cap simulés.

### 2. Le Scanner en Mode Simulation
Si vous testez sur un ordinateur sans webcam, la console debug permet d'activer le panneau **Simulation manuelle (Debug PC)** dans l'onglet Scanner pour saisir directement les codes (`MAP_LOC_1`, `MJ_STEP_1`, etc.).

### 3. La Mire de Test des QR Codes (`/test-qr`)
Ouvrez **`http://localhost:8000/test-qr`** sur un écran d'ordinateur :
* Affiche les **12 QR codes officiels** de l'aventure :
  * Les 6 repères carte : `MAP_LOC_1` à `MAP_LOC_6`
  * Les 6 badges animateurs : `MJ_STEP_1` à `MJ_STEP_6`
* Vous pouvez directement pointer la caméra de votre téléphone vers l'écran pour tester l'intégralité du parcours.

---

## 7. Installation & Lancement en Local

### Prérequis
* **PHP** >= 8.2 ou 8.3 (avec extensions `pdo_sqlite`, `curl`, `mbstring`, `openssl`)
* **Composer**
* **Git**

### 1. Cloner le dépôt
```bash
git clone <URL_DU_DEPOT_GITHUB>
cd expedition-lens
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configurer l'environnement
```bash
cp .env.example .env
php artisan key:generate
```
*(Par défaut, la base de données SQLite locale `database/database.sqlite` est utilisée).*

### 4. Initialiser la base de données et les étapes
Cette commande exécute les migrations et charge automatiquement les 6 étapes officielles de Lens avec leurs coordonnées GPS, énigmes et anecdotes :
```bash
php artisan migrate:fresh --seed
```

### 5. Démarrer le serveur local
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
L'application est disponible sur : **[http://localhost:8000](http://localhost:8000)**

---

## 8. Connexion Smartphone via Tunnel HTTPS

> [!IMPORTANT]
> Les navigateurs mobiles (Chrome Android, Safari iOS) **bloquent l'accès à la caméra et aux capteurs boussole / géolocalisation** si la connexion n'est pas sécurisée en `https://`. Une adresse IP locale brute `http://192.168.x.x` empêchera la caméra et le gyroscope de fonctionner.

### Solution recommandée : Cloudflare Tunnel (gratuit, sans compte)
1. Téléchargez [Cloudflare Tunnel (`cloudflared`)](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/).
2. Lancez le tunnel dans un terminal :
   ```bash
   cloudflared tunnel --url http://127.0.0.1:8000
   ```
3. Ouvrez sur votre smartphone l'URL HTTPS temporaire générée (`https://*.trycloudflare.com`) : toutes les permissions (caméra, boussole, localisation) fonctionnent nativement.

*(Alternative possible : `ngrok http 8000`)*.

---

## 9. Déploiement en Production (Render & Neon)

Le projet intègre un conteneur optimisé pour l'hébergement cloud (Render en offre gratuite avec base PostgreSQL Neon) :

* **Multi-stage Dockerfile ([`Dockerfile`](Dockerfile)) :** compilation des assets, PHP 8.3 CLI avec support `pdo_pgsql`, et multi-workers `PHP_CLI_SERVER_WORKERS=4 --no-reload`.
* **Démarrage à froid optimisé :** exécution automatique des migrations (`php artisan migrate --force`) et seeding conditionné pour éviter tout surcoût de redémarrage.
* **Keep-Alive intégré :** le heartbeat client `/keep-alive` empêche la mise en veille de Render et le scale-to-zero de Neon pendant l'utilisation active.

---

## 10. Tests Automatisés & Qualité du Code

L'application bénéficie d'une couverture de tests rigoureuse assurant la robustesse du gameplay, de la boussole, de l'anti-spoil, de l'authentification et de la résilience réseau :

```bash
# Exécuter l'ensemble de la suite de tests (120 tests, 1886 assertions)
php artisan test --compact

# Formater le code PHP selon les standards du projet
vendor/bin/pint --format agent
```

### Bilan de validation :
* **120 tests réussis / 120** (`1886 assertions`, 0 échec).
* Respect strict de la charte (zéro emoji, typographies officielles, conformité anti-spoil).

---

## 11. Arborescence du Projet

```text
expedition-lens/
├── app/
│   ├── Http/Controllers/       # Contrôleurs web (Mire /test-qr)
│   ├── Livewire/               # Composants Livewire réactifs
│   │   ├── Auth/LoginRegister  # Onboarding, tutoriel 5 étapes, auth équipe
│   │   ├── Game/Hub            # Boussole, carte deck, démo debug, étape sur place
│   │   ├── Game/Scanner        # Scanner caméra headless, overlay succès/erreur
│   │   ├── Game/Inventory      # Silhouette de la clé antique & 6 fragments
│   │   └── GameRouter.php      # Routeur SPA Alpine & barre de progression
│   └── Models/                 # Modèles Eloquent (Step, Fragment, User)
├── database/
│   ├── migrations/             # Schéma de base de données
│   └── seeders/StepSeeder.php  # Les 6 étapes officielles de Lens
├── docs/                       # Documentation projet & spécifications
│   ├── fiche_app.md            # Fiche détaillée de l'application
│   ├── guide_demarrage_...md   # Guide pratique de connexion mobile
│   └── tickets/done.md         # Historique des lots validés
├── public/                     # Assets publics, logos officiels, manifest PWA
├── resources/views/            # Vues Blade & composants UI
│   ├── components/layouts/app  # Layout maître PWA, bannières & keep-alive
│   └── livewire/               # Templates des composants de jeu
├── routes/web.php              # Routes principales, /test-qr et /keep-alive
├── tests/Feature/              # Suite de tests fonctionnels automatisés
└── Dockerfile                  # Configuration de déploiement Render
```

---

## 12. Crédits & Équipe

* **Contexte :** Projet universitaire BUT MMI (Métiers du Multimédia et de l'Internet) — MMI3.
* **Partenaires :** Destination Lens Tourisme & Asmodee.
* **Développement & Design :** Nathanaël Leang et l'équipe projet MMI Lens.
