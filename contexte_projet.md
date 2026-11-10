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

## 5. Architecture de l'Application (SPA Livewire)

L'application est une Single Page Application (SPA) codée en Laravel / Livewire. L'interface ne doit jamais recharger.
Une barre de navigation persistante en bas d'écran permet de basculer entre 3 onglets (composants) :

1. **Hub / Boussole :** Affiche soit l'énigme textuelle (Phase A), soit la distance GPS dynamique (Phase B).

2. **Scanner :** Utilise la caméra du téléphone (`html5-qrcode`) pour scanner les QR Codes et interagir avec le back-end Livewire. *Doit inclure un input manuel pour le debug sur PC.*

3. **Inventaire :** Affiche les fragments gagnés et l'écran de victoire final.

*(Note pour le développeur AI : Le design et le CSS sont totalement exclus du périmètre actuel. L'objectif est un MVP 100% fonctionnel en HTML brut).*