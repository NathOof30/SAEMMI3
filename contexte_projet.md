# CONTEXTE DU PROJET : "L'Expédition Secrète"

## 1. Vision Globale

Nous développons une PWA (Progressive Web App) pour un jeu de piste hybride (physique + numérique) dans la ville de Lens. Le jeu mêle l'univers de la marque Asmodee (jeux de société) et le tourisme local (Destination Lens Tourisme).
L'application sert de "Serrure Numérique" et de "Boussole", elle ne remplace pas le kit de jeu physique (carte papier, carnet) que les joueurs ont entre les mains.

## 2. Répartition des Rôles

* **L'Enfant (L'Explorateur) :** Ne regarde pas le téléphone. Il utilise la carte papier, un carnet et un filtre optique pour résoudre des énigmes visuelles dans la ville.

* **Le Parent (Le Maître du Jeu) :** Tient le téléphone (la PWA). Il valide les étapes, donne le cap géographique, et gère l'inventaire numérique.

## 3. La Boucle de Gameplay (Machine à États)

Le jeu est composé de 6 étapes. Chaque étape (`current_step`) est divisée en 2 phases (`phase`) qui dictent le comportement de l'application :

**Phase A : `GUESSING_LOCATION` (Recherche sur la carte)**

1. L'application affiche une énigme textuelle (ex: "Je suis une locomotive immobile...").

2. L'enfant cherche sur sa carte papier physique quel est ce lieu.

3. Le parent utilise le **Scanner** de l'App pour scanner le QR Code imprimé sur le lieu trouvé sur la carte papier (Code attendu : `MAP_LOC_{ID}`).

4. *Succès :* Passage à la Phase B.

**Phase B : `WALKING_TO_TARGET` (Déplacement physique)**

1. L'application active le **Hub/Boussole** : elle calcule la distance GPS entre le téléphone et les coordonnées du monument.

2. La famille marche physiquement jusqu'au lieu réel.

3. Sur place, ils jouent à un mini-jeu IRL avec un animateur (ou résolvent une énigme physique).

4. Le parent utilise le **Scanner** pour lire le QR Code de victoire donné par l'animateur (Code attendu : `MJ_STEP_{ID}`).

5. *Succès :* Un "Fragment" est ajouté à l'inventaire. Le joueur passe à l'étape suivante (`current_step + 1`) et repasse en Phase A.

## 4. Climax (Fin du jeu)

Quand l'équipe a collecté les 6 fragments, la boucle s'arrête. L'application affiche un écran de victoire contenant un code final à 4 chiffres (constitué des indices récoltés sur chaque fragment). Ce code permet d'ouvrir un véritable cadenas à l'Office de Tourisme pour gagner une récompense physique (édition spéciale Asmodee).

## 5. Architecture & Direction Artistique

L'application est une Single Page Application (SPA) codée en Laravel 11, Livewire 3 et Alpine.js. L'interface ne recharge jamais.

* **Direction Artistique Néo-brutaliste « Sang et Or » :**
  - Palette officielle : Rouge Sang (`#841818`), Jaune Or (`#F4B316`), contours noirs francs (2.5px à 4px), ombres déportées 3D marquées (`0 4px 0 #1A1A1A`), effet tactile de rebond (`btn-bounce`).
  - Fond en pointillés réguliers (`radial-gradient`), interface 100% contenue dans l'écran (`100dvh`, aucun scroll sur la structure principale).
* **Composants Principaux :**
  1. **Hub / Boussole :** 
     - Boussole vectorielle géante au centre : cadran rotatif avec les 4 points cardinaux (N, E, S, O) calé sur le vrai Nord, aiguille bicolore dont la pointe **JAUNE** pointe vers la cible et la queue **ROUGE** vers l'opposé. En Phase A, un `?` doré animé oscille au centre.
     - Deck de cartes façon jeu Asmodee en bas d'écran avec cartes inclinées (-5° / +5°) et coupure nette par le bas de l'écran.
     - Console de debug discrète `[•] debug` avec téléportation instantanée `TP sur le spot (0m)`.
  2. **Scanner :** Caméra plein écran via `Html5-QRCode` headless avec filtre intelligent de caméra arrière. Overlay plein écran néo-brutaliste vert (succès) ou rouge (erreur) avec redirection vers le Hub.
  3. **Inventaire / Sac à Dos :** Affiche les fragments collectés, le code à 4 chiffres du cadenas et le QR code de retrait du lot.
  4. **Accueil & Authentification :** Création d'équipe ou connexion, slider tutoriel 3D épuré sans doublon de boutons.
  5. **Mire de test `/test-qr` :** Tableau de bord affichant les 12 QR codes de test pour validation sur écran d'ordinateur.