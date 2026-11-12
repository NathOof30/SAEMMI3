# CONTEXTE DU PROJET : "GALI"

## 1. Vision Globale

Nous développons une PWA (Progressive Web App) pour un jeu de piste hybride (physique + numérique) dans la ville de Lens. Le jeu est le fruit d'une collaboration entre l'éditeur de jeux **Asmodee** et **Destination Lens Tourisme**.
L'application sert de **"Boussole d'orientation"** et de **"Serrure Numérique"** : elle fonctionne toujours en duo avec le kit physique (grande carte illustrée de Lens, loupe d'observation) que les familles ont entre les mains.

---

## 2. Répartition des Rôles (Expérience Familiale Hybride)

* **L'Enfant (L'Explorateur) :** Ne regarde pas en permanence le téléphone. Il utilise la **grande carte papier de Lens** et sa **loupe** pour résoudre les énigmes visuelles de Gali dans la ville et participer aux défis sur les stands.
* **Le Parent (Le Guide) :** Tient le smartphone (la PWA). Il s'oriente avec la **boussole GPS**, confirme l'arrivée sur chaque spot, et utilise le **scanner** pour valider les repères carte et les badges des animateurs.

---

## 3. La Boucle de Gameplay (Machine à États)

Le jeu se compose de **6 étapes patrimoniales**. Chaque étape (`current_step`) suit une boucle immersive :

### Phase A : `GUESSING_LOCATION` (Recherche sur la carte physique)
1. L'application affiche uniquement l'énigme textuelle de Gali (anti-spoil strict : aucun nom de lieu n'est dévoilé).
2. L'enfant cherche le symbole correspondant sur la carte papier avec sa loupe.
3. Le parent scanne le QR code imprimé sur le repère de la carte (`MAP_LOC_{ID}`).
4. *Succès :* La destination est révélée, passage immédiat à la Phase B.

### Phase B : `WALKING_TO_TARGET` (Déplacement physique & Épreuve sur place)
1. La **Boussole interactive** s'active : sa pointe **JAUNE OR** désigne la direction exacte du monument, tandis que le cadran rotatif s'aligne avec le vrai Nord.
2. La famille marche physiquement jusqu'au lieu en suivant la distance et le cap.
3. **Sur place :** À l'approche du lieu (ou via le secours d'aide), le bouton **« Vous êtes arrivé ? »** apparaît.
4. En validant l'arrivée, la boussole laisse place au **médaillon illustré du monument** : l'application invite l'équipe à poser le smartphone pour réaliser le mini-jeu en famille avec l'animateur du spot.
5. Une fois le défi réussi, le bouton **« Épreuve terminée ? »** ouvre le scanner pour flasher le badge officiel de l'animateur (`MJ_STEP_{ID}`).
6. *Succès :* Un nouveau **fragment de clé** est débloqué et vient s'emboîter sur la clé antique dans l'Inventaire. L'équipe passe à l'étape suivante (`current_step + 1`) en Phase A.

---

## 4. Climax : La Clé Complète & La Lampe Cœur

L'histoire suit **Gali**, un jeune Lensois sur les traces de ses aïeux mineurs. Dès le départ, Gali possède la base de la clé retrouvée dans le grenier de son grand-père.
À chaque étape franchie, l'un des **6 fragments cassés** vient s'emboîter sur la silhouette verticale de la clé :
1. Gare de Lens : *Bord de l'anneau*
2. Centre-Ville : *Base de l'anneau*
3. Stade Bollaert-Delelis : *Col sculpté*
4. Musée Louvre-Lens : *Fût gravé*
5. Terrils de Loos-en-Gohelle : *Garde du fût*
6. Office de Tourisme : *Dents de la clé*

À l'issue de la 6ᵉ étape, la **clé complète** est forgée : elle permet de rallumer la légendaire **Lampe Cœur** et d'ouvrir le coffre secret.
L'équipe débloque alors son **QR Code Retrait du Trésor** pour récupérer son **véritable trésor surprise** à l'Office de Tourisme et se retrouve **automatiquement inscrite au grand tirage au sort** de l'expédition.

---

## 5. Architecture & Direction Artistique

L'application est une Single Page Application (SPA) codée en **Laravel + Livewire + Alpine.js**, sans rechargement de page.

* **Direction Artistique Néo-brutaliste « Sang et Or » :**
  - Palette officielle : Rouge Sang (`#841818`), Jaune Or (`#F4B316`), Vert Sauge (`#9FB8AD`), Parchemin (`#FAF8F5`, `#F4EBD9`), contours noirs francs (2.5px à 3.5px), ombres déportées 3D marquées (`0 4px 0 #1A1A1A`), effet tactile de rebond (`btn-bounce`).
  - Fond en pointillés réguliers (`radial-gradient`), interface 100% contenue dans l'écran (`100dvh`, aucun scroll sur la structure principale).
* **Composants Principaux :**
  1. **Hub / Boussole :**
     - Cadran boussole vectoriel géant calé sur le vrai Nord, aiguille bicolore dynamique, point d'interrogation oscillant en Phase A.
     - Tiroir coulissant *« Carnet de Gali & Histoire du lieu »* avec anecdotes patrimoniales.
     - Console de démonstration `[•] debug` discrète avec sélecteur d'étapes (1 à 6, et Fin), reset progression, et téléportation GPS.
  2. **Scanner :** Caméra plein écran via `Html5-QRCode` headless avec filtre intelligent de caméra arrière. Overlay plein écran néo-brutaliste vert (succès) ou rouge (erreur) avec redirection vers le Hub / l'Inventaire.
  3. **Inventaire / Clé de Gali :** Visualisation de la clé antique verticale avec pièces emboîtées en or, consultation des fiches étapes et génération locale SVG du QR code de retrait final.
  4. **Accueil & Authentification :** Création d'équipe ou connexion, slider tutoriel en 5 étapes avec logos officiels Lens Tourisme et Asmodee.
  5. **Mire de test `/test-qr` :** Tableau de bord affichant les 12 QR codes officiels pour test complet sur écran PC.
  6. **Résilience & Keep-Alive :** Endpoint `GET /keep-alive` pour maintenir Render et Neon actifs sans mise en veille, et écran de chargement à 2 niveaux lors des connexions lentes.
