# Guide de Démarrage Rapide & Tunnel Mobile — Équipe Projet

> **Ce guide est destiné aux membres de l'équipe MMI.**  
> Il explique comment installer l'application sur votre PC, la connecter à votre smartphone via un tunnel HTTPS sécurisé, et tester l'intégralité du parcours d'escape game sans bouger de votre chaise.

---

## 1. Installation en 3 minutes sur PC

### Étape 1 : Récupérer le projet
Dans votre terminal (PowerShell, Git Bash ou Terminal Mac) :
```bash
git clone <URL_DU_DEPOT>
cd expedition-lens
```

### Étape 2 : Installer les paquets PHP
```bash
composer install
```
*(Si vous n'avez pas Composer, installez-le depuis [getcomposer.org](https://getcomposer.org/)).*

### Étape 3 : Créer le fichier d'environnement et la base de données
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```
*Le seed génère directement les 6 étapes de Lens (Place Jean Jaurès, Gare de Lens, Louvre-Lens, Bollaert, Terrils, etc.) avec leurs coordonnées GPS et leurs codes QR officiels.*

---

## 2. Lancer l'Application sur PC

Dans votre premier terminal :
```bash
php artisan serve
```
L'application tourne sur **`http://localhost:8000`**.  
Vous pouvez l'ouvrir dans votre navigateur pour créer une équipe de test.

---

## 3. Connecter son Smartphone via Tunnel HTTPS (Cloudflare Tunnel)

### Pourquoi un tunnel HTTPS est-il indispensable ?
Pour que le smartphone puisse **activer la caméra** (pour le scanner) et **utiliser le gyroscope / GPS** (pour la boussole), les navigateurs mobiles comme Chrome ou Safari exigent une connexion chiffrée **HTTPS (`https://`)**.  
Une adresse locale normale `http://192.168.x.x` bloque systématiquement les capteurs.

### Méthode la plus simple : Cloudflare Tunnel (100% gratuit, aucun compte nécessaire)

#### Sous Windows :
1. Téléchargez l'exécutable officiel : [cloudflared-windows-amd64.exe](https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe).
2. Renommez-le en `cloudflared.exe` et placez-le soit à la racine du projet, soit dans un dossier de votre PATH.
3. Ouvrez un **deuxième terminal** dans le dossier du projet et lancez :
   ```powershell
   ./cloudflared.exe tunnel --url http://127.0.0.1:8000
   ```

#### Sous macOS :
```bash
brew install cloudflared
cloudflared tunnel --url http://127.0.0.1:8000
```

#### Ce qui s'affiche à l'écran :
Cloudflare va générer une ligne comme celle-ci :
```text
INF +--------------------------------------------------------------------------------------------+
INF |  Your quick Tunnel has been created! Visit it at (it may take some time to be reachable):  |
INF |  https://votre-nom-aleatoire.trycloudflare.com                                             |
INF +--------------------------------------------------------------------------------------------+
```

👉 **Copiez l'adresse `https://...trycloudflare.com` et ouvrez-la sur votre smartphone !**

---

## 4. Installer l'Application en mode "App Réelle" (PWA)

Sur votre smartphone (dans Google Chrome sur Android ou Safari sur iPhone) :
1. Ouvrez l'URL HTTPS Cloudflare.
2. Appuyez sur le menu du navigateur (3 petits points sur Chrome ou bouton Partager sur Safari).
3. Cliquez sur **"Installer l'application"** ou **"Sur l'écran d'accueil"**.
4. L'application apparaît alors sur votre téléphone comme une véritable application native :
   - Icône sur l'écran d'accueil.
   - Fond et barre système noirs (`#000000`).
   - Plein écran sans barre d'adresse (`standalone`).

---

## 5. Comment Tester Tout le Jeu en 5 Minutes Chrono (Mode Développeur)

Vous n'avez pas besoin de vous déplacer dans Lens pour valider le jeu :

### Étape 1 : Ouvrir la mire de QR Codes sur l'écran du PC
Sur votre ordinateur, ouvrez :  
👉 **`http://localhost:8000/test-qr`**  
*(Cette page regroupe les 6 QR codes Carte et les 6 QR codes Animateur).*

### Étape 2 : Sur le smartphone, créer une équipe
1. Cliquez sur **"Démarrer l'aventure"**.
2. Parcourez ou passez le tutoriel de présentation des rôles.
3. Créez votre équipe (ex: "Les Explorateurs", mot de passe au choix).

### Étape 3 : Tester une étape complète
1. **Phase A (Recherche Carte)** :
   - L'écran vous affiche la destination et l'énigme.
   - Cliquez sur le gros bouton jaune **"Scanner la carte"**.
   - Pointez la caméra de votre téléphone vers le QR Code **`MAP_LOC_1`** affiché sur votre écran de PC.
   - Une notification plein écran avec **checkmark vert** s'affiche ("Bravo ! Emplacement trouvé !").
   - Touchez l'écran ou appuyez sur le bouton : vous êtes automatiquement redirigé vers la boussole !
2. **Phase B (Boussole & Déplacement)** :
   - L'aiguille **JAUNE** pointe vers le lieu cible.
   - Comme vous êtes chez vous, l'application affiche une distance (ex: 300 m ou 15 km).
   - **Pour simuler que vous êtes arrivé sur place** :
     - Cliquez sur le bouton discret **`[•] debug`** en haut à droite.
     - Cliquez sur **`TP sur le spot (0m)`**.
     - Fermez la console : la distance passe instantanément à `0 m` et le bouton devient jaune : **`Scanner le spot`** !
3. **Validation de l'épreuve (Animateur / MJ)** :
   - Cliquez sur **"Scanner le spot"**.
   - Pointez votre téléphone vers le QR Code **`MJ_STEP_1`** sur votre PC.
   - Notification de victoire plein écran ("Bravo ! Épreuve réussie !").
   - Le fragment n°1 est débloqué et ajouté dans votre sac à dos (onglet Inventaire).
   - Vous passez automatiquement à l'étape 2 !

Répétez l'opération jusqu'à l'étape 6 pour débloquer l'écran de victoire finale et le code secret du coffre Asmodee !

---

## 6. Problèmes Fréquents & Solutions

### La caméra ne démarre pas sur le téléphone
* **Cause :** Vous n'êtes pas en HTTPS.
* **Solution :** N'utilisez pas `http://192.168.x.x:8000`. Utilisez impérativement le lien `https://...trycloudflare.com` généré par Cloudflare Tunnel.

### La boussole ne tourne pas
* **Cause :** Sur smartphone, l'accès aux capteurs de mouvement nécessite une autorisation.
* **Solution :** Cliquez sur le bouton "Activer la boussole" si la bannière apparaît. Une fois accordée, l'autorisation est mémorisée de manière permanente.

### L'app installée sur Android a des bandes orange au lancement
* **Cause :** Android a gardé en mémoire l'ancienne version du manifest lors d'une précédente installation.
* **Solution :** Supprimez l'application de votre écran d'accueil, fermez Chrome, puis réinstallez l'application depuis la nouvelle URL.

---

## 7. Commandes Utiles pour les Développeurs

```bash
# Lancer les tests unitaires et fonctionnels (84 tests)
php artisan test

# Formater le code PHP avec Laravel Pint
vendor/bin/pint --format agent

# Réinitialiser la base de données et re-créer les 6 étapes
php artisan migrate:fresh --seed
```

