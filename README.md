# GALI — Lens Tourisme & Asmodee

> **Jeu de piste urbain familial hybride (physique + numérique) au cœur de la ville de Lens.**  
> Application mobile PWA servant de boussole d'orientation GPS et de serrure numérique pour valider les étapes d'exploration.

---

## Sommaire

1. [Présentation du Projet](#1-présentation-du-projet)
2. [Stack Technique](#2-stack-technique)
3. [Prérequis](#3-prérequis)
4. [Installation Pas à Pas](#4-installation-pas-à-pas)
5. [Lancement du Serveur de Développement](#5-lancement-du-serveur-de-développement)
6. [Connexion Smartphone via Tunnel HTTPS (Essentiel)](#6-connexion-smartphone-via-tunnel-https-essentiel)
7. [Tester le Jeu sans se Déplacer (Outils de Debug)](#7-tester-le-jeu-sans-se-déplacer-outils-de-debug)
8. [Boucle de Gameplay & Rôles](#8-boucle-de-gameplay--rôles)
9. [Architecture des Composants](#9-architecture-des-composants)
10. [Exécution des Tests et Qualité du Code](#10-exécution-des-tests-et-qualité-du-code)

---

## 1. Présentation du Projet

Dans le cadre d'un partenariat entre **Destination Lens Tourisme** et l'éditeur de jeux de société **Asmodee**, *GALI* propose une aventure interactive dans la ville de Lens pour les familles.

* **L'Enfant (L'Explorateur)** utilise une carte papier physique, un filtre optique et des indices imprimés pour résoudre les énigmes.
* **Le Parent (Le Maître du Jeu)** tient le smartphone : la PWA fait office de **boussole GPS** pour guider la famille à travers la ville et de **scanner de validation** (carte papier et QR code animateur sur place) jusqu'au déblocage du jeu Dobble exclusif à l'Office de Tourisme.

---

## 2. Stack Technique

* **Framework backend** : PHP 8.2+ / [Laravel 11](https://laravel.com/)
* **Réactivité Frontend** : [Livewire 3](https://livewire.laravel.com/) & [Alpine.js](https://alpinejs.dev/) (architecture SPA sans rechargement de page)
* **Design & Identité Visuelle** : Charte **Néo-brutaliste** « Sang et Or » (`#841818` / `#F4B316`), typographie Impact/Inter, bordures 3.5px noires, ombres franches déportées, deck de cartes façon jeu de société Asmodee, 100% contenue dans l'écran (`100dvh`, zéro scroll parasite).
* **Scanner QR Code** : `Html5-QRCode` headless avec sélection automatique intelligente de la meilleure caméra arrière (hors ultra-grand-angle/macro).
* **Boussole & Capteurs** : Formule de relèvement géodésique Haversine, détection d'orientation absolue (`deviceorientationabsolute` / iOS Safari `DeviceOrientationEvent`), et mémorisation permanente des permissions dans `localStorage`.
* **PWA** : Application web progressive installable (WebAPK Android / iOS Add to Home Screen), bandeau et barres système configurés en noir profond (`#000000`).
* **Base de données** : SQLite par défaut (compatible MySQL / PostgreSQL).
* **Suite de tests** : PHPUnit (84 tests automatisés, couverture complète du flux, du scanner et des capteurs).

---

## 3. Prérequis

Avant de cloner le projet, assurez-vous d'avoir installé sur votre machine :
* **PHP** >= 8.2 (avec extensions `pdo_sqlite`, `curl`, `mbstring`, `openssl`)
* **Composer** (gestionnaire de dépendances PHP)
* **Git**
* Un outil de tunnel HTTPS pour tester sur mobile : **Cloudflare Tunnel (`cloudflared`)** *(recommandé)* ou **Ngrok**.

---

## 4. Installation Pas à Pas

### 1. Cloner le dépôt Git
```bash
git clone <URL_DU_DEPOT_GIT>
cd expedition-lens
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Configurer l'environnement `.env`
Copiez le fichier d'exemple et générez la clé de sécurité de l'application :
```bash
cp .env.example .env
php artisan key:generate
```

*(Par défaut, la base de données est configurée en SQLite `database/database.sqlite`. Le fichier est créé automatiquement s'il n'existe pas).*

### 4. Exécuter les migrations et les données de test (Seed)
Cette étape crée la base de données et peuple automatiquement **les 6 étapes de Lens** avec leurs coordonnées GPS, énigmes et codes QR associés :
```bash
php artisan migrate:fresh --seed
```

---

## 5. Lancement du Serveur de Développement

Lancez le serveur Laravel en l'autorisant à écouter sur toutes les interfaces :
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
L'application est immédiatement accessible sur votre ordinateur à l'adresse :  
👉 **[http://localhost:8000](http://localhost:8000)**

---

## 6. Connexion Smartphone via Tunnel HTTPS (Essentiel)

> [!IMPORTANT]
> **Pourquoi le HTTPS est-il obligatoire pour tester sur smartphone ?**  
> Les navigateurs mobiles (Chrome Android, Safari iOS) **bloquent l'accès à la caméra (`navigator.mediaDevices.getUserMedia`) et aux capteurs d'orientation / géolocalisation** si la connexion n'est pas sécurisée en `https://`. Une simple adresse IP locale `http://192.168.x.x` ne permettra pas de scanner ou de faire tourner la boussole.

### Option Recommandée : Cloudflare Tunnel (`cloudflared`)
Cloudflare Tunnel est gratuit, rapide et ne nécessite aucune création de compte pour tester en local.

1. **Télécharger Cloudflare Tunnel** si vous ne l'avez pas :
   * **Windows** : Téléchargez `cloudflared-windows-amd64.exe` depuis [le site officiel Cloudflare](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/) et placez-le dans votre PATH (ou renommez-le en `cloudflared.exe`).
   * **macOS** : `brew install cloudflared`
   * **Linux** : `sudo apt install cloudflared`
2. **Lancer le tunnel** (dans un terminal séparé) :
   ```bash
   cloudflared tunnel --url http://127.0.0.1:8000
   ```
3. Cloudflare va vous afficher une URL publique sécurisée de type :  
   `https://random-subdomain.trycloudflare.com`
4. **Ouvrez cette URL sur votre smartphone** (ou scannez-la avec votre appareil photo) : l'application fonctionnera avec toutes les autorisations actives (caméra, boussole, géolocalisation) !

### Option Alternative : Ngrok
```bash
ngrok http 8000
```
Puis ouvrez l'URL HTTPS fournie par Ngrok sur votre smartphone.

---

## 7. Tester le Jeu sans se Déplacer (Outils de Debug)

Le projet intègre des fonctionnalités dédiées pour permettre aux développeurs et testeurs de valider l'ensemble du parcours sans avoir à marcher dans Lens ni imprimer les QR codes :

### 1. La Mire de Test des QR Codes (`/test-qr`)
Accédez à **`http://localhost:8000/test-qr`** (ou sur votre URL tunnel) depuis votre écran d'ordinateur :
* Affiche les **12 QR codes officiels** de l'aventure :
  * Les 6 repères carte : `MAP_LOC_1` à `MAP_LOC_6`
  * Les 6 victoires animateur : `MJ_STEP_1` à `MJ_STEP_6`
* Vous pouvez directement pointer la caméra de votre smartphone vers l'écran d'ordinateur pour scanner chaque QR code !

### 2. Téléportation GPS Instantanée (Console de Debug)
Sur l'écran principal (Hub), cliquez sur le bouton discret **`[•] debug`** en haut à droite :
* **Bouton `TP sur le spot (0m)`** : simule instantanément votre position sur le lieu exact de l'étape. La distance passe à `0 m` et le bouton d'action se transforme immédiatement en **`Scanner le spot`** !
* **Bouton `TP approche (100m)`** : simule une position d'approche pour observer la rotation de l'aiguille de la boussole.
* **Coordonnées personnalisées** : vous pouvez saisir n'importe quelle latitude, longitude et cap simulé.

### 3. Simulation manuelle dans le Scanner
Si vous n'avez pas de caméra, l'onglet Scanner contient un formulaire de secours en bas : saisissez simplement le code (`MAP_LOC_1`, `MJ_STEP_1`, etc.) et validez.

---

## 8. Boucle de Gameplay & Rôles

```
+-------------------------------------------------------------+
|                  ÉTAPE EN COURS (1 à 6)                     |
+-------------------------------------------------------------+
                              |
                              v
    [ PHASE A : RECHERCHE SUR LA CARTE PHYSIQUE ]
    * L'enfant résout l'énigme textuelle sur la carte papier.
    * Le parent ouvre le scanner et flashe le repère (MAP_LOC_X).
    * Notification succès plein écran -> Redirection automatique.
                              |
                              v
    [ PHASE B : DÉPLACEMENT PHYSIQUE EN VILLE ]
    * La boussole géante s'active sur le Hub.
    * L'aiguille JAUNE (#F4B316) pointe vers le monument cible.
    * Le cadran rotatif (N, E, S, O) s'oriente avec le vrai Nord.
    * À moins de 30 mètres du lieu, le bouton "Scanner le spot" apparaît.
                              |
                              v
    [ VALIDATION ANIMATEUR / SERRURE ]
    * La famille participe à l'épreuve avec l'animateur sur place.
    * Le parent flashe le QR Code de victoire (MJ_STEP_X).
    * Fragment secret débloqué et ajouté au sac à dos (Inventaire).
                              |
                              v
            Étape suivante (ou Écran de Victoire final)
```

---

## 9. Architecture des Composants

```
resources/views/
├── components/layouts/
│   └── app.blade.php           # Layout maître PWA, theme-color #000, 100dvh, styles néo-brutalistes
├── livewire/
│   ├── auth/
│   │   └── login-register.blade.php # Onboarding réparti, tutoriel slider 3D, création équipe & login
│   ├── game/
│   │   ├── hub.blade.php        # Boussole vectorielle géante, deck de cartes Asmodee, console debug
│   │   ├── scanner.blade.php    # Scanner headless Html5-QRCode, overlay succès/erreur, auto-redirect
│   │   └── inventory.blade.php  # Sac à dos, 6 fragments débloqués, code secret et QR récompense
│   └── game-router.blade.php    # Routeur SPA Alpine, header d'équipe Gali, progression x/6
└── test-qr.blade.php            # Tableau de bord des 12 QR codes de test
```

---

## 10. Exécution des Tests et Qualité du Code

L'application dispose d'une couverture de tests rigoureuse vérifiant le flux de jeu, les calculs de boussole, l'absence d'émojis, le respect de la charte graphique et la résilience mobile :

```bash
# Lancer l'ensemble de la suite de tests (84 tests)
php artisan test

# Formater le code PHP selon les standards du projet
vendor/bin/pint --format agent
```

---

## Membres de l'Équipe & Contact

Projet développé dans le cadre du cursus BUT MMI (MMI3) pour **Lens Tourisme** & **Asmodee**.
Pour toute question d'installation ou d'accès, contactez Nathanaël ou consultez le guide pratique dans [`docs/guide_demarrage_equipe_tunnel.md`](docs/guide_demarrage_equipe_tunnel.md).
