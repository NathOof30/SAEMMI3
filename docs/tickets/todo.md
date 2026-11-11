# Backlog & Tickets à faire — Lot T3 (Retours Parcours & Polissage UX/DA)

> Ce backlog remplace les lots archivés ([`done.md`](./done.md)) et structure les améliorations issues du **retour de test parcours réel** ainsi que les points complémentaires repérés lors de l'audit du code.
> **Statut :** En attente de validation avant implémentation (`tu ne corriges pas tout de suite`).

---

## Sommaire des priorités (Lot T3)

- **P3-A — Onboarding & Pages de départ (`begin` / Accueil + Tutoriel)** : Aérer les marges verticales, clarifier *pourquoi* l'équipe part à l'aventure, expliquer le duo **appli + kit physique** et toute la boucle de jeu (chercher, scanner, rejoindre le point d'étape, réussir une épreuve/jeu avec l'animateur) sans utiliser le terme « MJ ». Synchroniser le tutoriel d'accueil et celui de l'aide du Hub.
- **P3-B — Système visuel néo-brutaliste, Ombres portées, Halo & Modales** : Appliquer une hiérarchie stricte sur les `box-shadow` (éviter l'empilement lourd parent → enfant → enfant d'enfant), corriger le halo coupé sur la notification du Scanner, supprimer le double bouton de fermeture dans la fiche souvenir, et corriger l'espacement entre les boîtes de la modale Aide (`?`).
- **P3-C — Clarté sémantique & Wording joueur** : Remplacer `Fragment` par `Morceau de carte`, rendre explicite le `Bout de code` (ne plus confondre `"4"` avec le n°4), supprimer tous les tirets longs `—` au profit de `  |  `, et retirer le jargon « Maître du Jeu / MJ » des écrans visibles par les enfants/familles.
- **P3-D — Boucle Récompense : Scanner → Inventaire & Animation Puzzle** : Rediriger vers l'onglet **Inventaire** dès la découverte d'un morceau de carte (étapes 1 à 6), jouer une animation de placement sur le puzzle qui se complète, et offrir un bouton clair pour repartir vers l'étape suivante sur le Hub.
- **P3-E — Cohérence Tests PHPUnit & Vérification multi-écrans** : Adapter les tests existants impactés par le nouveau wording/flux et valider sur petit et grand mobile (`360x640`, `375x667`, `412x915`).

---

## P3-A — Onboarding (`begin`), Narration & Tutoriel complet

### [T3-1] Revoir les marges, la respiration verticale et le récit d'accroche sur les pages `begin`
- [x] **Éviter l'effet « tout tassé vers le haut » et clarifier le *pourquoi* de la mission**
  - **Constat actuel ([`login-register.blade.php`](../../resources/views/livewire/auth/login-register.blade.php)) :** Sur l'écran d'accueil (`welcome`) et le tutoriel (`slider`), l'en-tête (`padding-top: 2px`) et les blocs semblent comprimés/collés vers le haut ou déséquilibrés selon la hauteur du téléphone, et l'encart d'intro raconte la découverte du papier jauni mais n'explique pas assez **pourquoi on doit partir maintenant** ni l'enjeu concret pour Gali.
  - **Attendu :**
    - **Aération & proportions responsive :** Revoir le layout flex (`padding` haut/bas, `gap` entre en-tête, carte centrale et boutons bas) pour répartir l'espace harmonieusement sur petit écran (`360x640`) comme sur smartphone Android plus haut (`~390x844` / Redmi Note 11), sans tout coller au plafond ni laisser de vide mal réparti. Idem sur les formulaires `register` et `login`.
    - **Accroche narrative plus claire (`welcome`) :** Formuler explicitement le déclencheur et l'objectif de la sortie en famille :
      - Gali n'a trouvé qu'un **seul morceau** de la carte : impossible de localiser la *Lampe Cœur* et de la rallumer avec un bout incomplet !
      - Pour que l'histoire de Lens brille à nouveau, l'équipe doit **partir en ville avec son kit** retrouver les **6 morceaux éparpillés** et reconstituer la carte complète jusqu'au coffre final.

### [T3-2] Enrichir le tutoriel (boucle de jeu complète + mention du kit + suppression du terme « MJ »)
- [x] **Expliquer concrètement comment on joue du début à la fin avant de lancer l'équipe**
  - **Constat actuel ([`login-register.blade.php`](../../resources/views/livewire/auth/login-register.blade.php) & [`hub.blade.php`](../../resources/views/livewire/game/hub.blade.php)) :** Les 3 slides actuelles sont très courtes (une phrase chacune) : on ne dit pas explicitement que l'app fonctionne **obligatoirement avec le kit physique**, ni ce qu'on fait au point d'étape (rencontrer une personne sur place pour faire un **jeu / une épreuve**), et l'app parle encore du *« Maître du Jeu »* dans un écran lu avec les enfants.
  - **Attendu :**
    - Revoir le découpage et le contenu du tuto (ex. **4 slides courtes et illustrées** ou **3 slides mieux structurées avec pictogrammes/étapes claires** — à garder rapide à lire sur un téléphone sans dépasser `100dvh`) couvrant :
      1. **Votre matériel (Appli + Kit papier) :** L'application s'utilise avec le **kit physique** (la grande carte papier de Lens et la **loupe** de l'enfant).
      2. **Chercher & Scanner :** À chaque étape, l'enfant lit l'indice de Gali, cherche le lieu sur la carte avec la **loupe**, puis vous **scannez le QR code** imprimé sur la carte pour confirmer la piste.
      3. **Marcher & Relever l'épreuve :** La boussole (pointe **JAUNE OR**) vous guide jusqu'au point d'étape. Là-bas, vous retrouvez **l'animateur de l'étape** *(ou « le gardien du lieu » — jamais « MJ » pour les enfants !)* pour participer à un **petit jeu / défi en famille** avant de scanner son badge.
      4. **Reconstituer la carte & Ouvrir le coffre :** Chaque épreuve réussie débloque un **morceau de carte** et un **bout de code** dans l'inventaire. Réunissez les 6 morceaux pour former le code final du vrai coffre à l'Office de Tourisme (jeu **Dobble** à la clé !).
    - **Point de vigilance repéré (anti-duplication) :** Le tutoriel existe à **deux endroits** : sur `begin` ([`login-register.blade.php`](../../resources/views/livewire/auth/login-register.blade.php)) et dans la modale Aide `#hub-replay-tuto-btn` ([`hub.blade.php`](../../resources/views/livewire/game/hub.blade.php)). Factoriser les slides (ou un composant/partial Blade partagé) pour garantir 100 % de cohérence sans maintenir deux textes en parallèle.

---

## P3-B — Hiérarchie visuelle, Ombres portées, Modales & Espacement

### [T3-3] Établir une règle stricte de hiérarchie des ombres portées (`box-shadow`)
- [x] **Alléger l'empilement de boîtes néo-brutalistes en privilégiant le conteneur parent**
  - **Constat actuel :** De nombreux écrans cumulent 3 niveaux d'ombres dures (`box-shadow: 0 Xpx 0 #1A1A1A`) emboîtés les uns dans les autres, ce qui alourdit la lecture visuelle :
    - [`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php) : Carte parente (`0 6px 0`) → Plateau `#gali-treasure-map-puzzle` (`0 4px 0`) → Chaque tuile `#puzzle-piece-*` (`0 3px 0` / `0 2px 0`), et idem sur les blocs *Cadenas*, *chiffres*, *QR Code*, *cadre QR*, et les *boutons liste récapitulative*.
    - [`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php) modale souvenir (`0 8px 0`) → encarts *Le mot de Gali* et *L'Histoire du lieu* (`0 3px 0`).
    - [`hub.blade.php`](../../resources/views/livewire/game/hub.blade.php) : Modale Aide (`0 8px 0`) → 3 boîtes guides (`0 3px 0`) ; Tiroir Phase B → encarts *Le mot de Gali* et *L'Histoire du lieu* (`0 3px 0`).
    - [`login-register.blade.php`](../../resources/views/livewire/auth/login-register.blade.php) : Carte principale (`0 6px 0`) → pastilles icônes décoratives (`0 3px 0` / `0 2px 0`).
  - **Règle DA à appliquer uniformément :**
    - **Niveau 1 — Boîte parente principale / Modale :** conserve sa bordure franche (`3px–3.5px solid #1A1A1A`) et son ombre portée nette (`0 6px 0` ou `0 8px 0 #1A1A1A`).
    - **Niveau 2 — Boîte enfant (sections/encarts internes) :** **outline seul** (`border: 2px solid #1A1A1A` ou bordure colorée/pointillée), **sans `box-shadow`**.
    - **Niveau 3 — Enfant d'enfant / Tuiles de puzzle / Badges décoratifs :** traitement plus léger (`border: 1.5px solid` ou contour teinté/pointillé léger, **sans `box-shadow`**), notamment pour les morceaux de carte `#puzzle-piece-1..6` afin qu'ils forment une carte assemblée fluide plutôt que 6 gros boutons empilés.
    - **Exception ciblée :** Les **boutons d'action autonomes (CTA)** seuls (ex. `Démarrer l'aventure`, `Scanner la carte`, `Fermer la fiche`, `Retour à la boussole`) conservent leur ombre portée tactilo-cliquable (`0 3px 0` / `0 4px 0 #1A1A1A`).
  - **Audit complémentaire :** Vérifier également les 2 cartes grises de l'éventail derrière la carte Hub (`#phase-b-deck-wrapper` / Phase A) et le badge du cadenas final pour que le contraste reste lisible sans surcharge.

### [T3-4] Corriger la notification plein écran du Scanner (supprimer l'ombre coupée sur fond sombre)
- [x] **Supprimer l'effet d'ombre dure sur la carte de notification pour libérer le halo lumineux**
  - **Constat actuel ([`scanner.blade.php`](../../resources/views/livewire/game/scanner.blade.php)) :** `.scan-card-glow-success` et `.scan-card-glow-error` combinent `0 10px 0 #1A1A1A` avec `0 0 0 8px rgba(...)` et `0 0 50px rgba(...)`. Sur l'écran de fond très sombre (`rgba(0,0,0,0.88)` / `rgba(28,4,4,0.92)`), le décroché noir `0 10px 0` se confond avec le fond et tranche visuellement la lueur / le halo autour de la boîte.
  - **Attendu :**
    - Retirer l'ombre portée solide `0 10px 0 #1A1A1A` sur la carte parente de la notification (`#fullscreen-scan-overlay`).
    - Garder un halo lumineux doux et homogène (`box-shadow: 0 0 36px rgba(...)`, éventuellement sur le badge icône ou en lueur diffuse propre non coupée par un anneau/ombre dure), avec la bordure noire nette sur la carte blanche pour rester dans l'univers néo-brutalisme sans artefact visuel.

### [T3-5] Supprimer les boutons de fermeture en double dans la fiche souvenir (et harmoniser les modales)
- [x] **Ne garder que le bouton explicite « Fermer la fiche » dans `#inventory-memory-modal`**
  - **Constat actuel ([`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php)) :** La modale Fiche Souvenir affiche à la fois un bouton `✕` en haut à droite (`#close-memory-modal-btn`) et un bouton pleine largeur `Fermer la fiche` tout en bas.
  - **Attendu :**
    - Supprimer la croix `✕` en haut à droite de la fiche souvenir et conserver uniquement le bouton bas **`Fermer la fiche`** (tout en gardant `x-on:click.self="closeMemory()"` et `Escape` pour la fermeture ergonomique).
    - **Cohérence à vérifier sur les autres modales :** Vérifier `#hub-help-modal` (en vue *tutoriel*, éviter l'empilement de 3 boutons/liens de sortie simultanés : `Compris !` + `Retour à l'aide` + `Revenir au jeu`, et rester propre sur `✕` vs bouton bas).

### [T3-6] Corriger les marges et l'espacement entre les boîtes dans la modale Aide (`?`)
- [x] **Redonner de l'air entre les encarts et la zone de boutons dans `#hub-help-modal`**
  - **Constat actuel ([`hub.blade.php`](../../resources/views/livewire/game/hub.blade.php)) :** Dans `#hub-help-modal`, la combinaison de `box-shadow: 0 3px 0` sur les enfants, du `overflow-y: auto` en flex column et de `gap: 10px` écrase visuellement l'espacement entre les boîtes (et coupe l'ombre du dernier bloc en bas du scroll).
  - **Attendu :**
    - Avec le retrait des ombres sur ces boîtes enfants (`[T3-3]`), passer à un espacement réel généreux (ex. `gap: 14px` ou `margin-bottom: 14px` sur des blocs `flex-shrink: 0`) et ajouter du `padding` en haut/bas de la zone scrollable pour une séparation nette entre chaque bloc (*Rôles*, *Lire la boussole*, *Dépannage*) et le bloc de boutons du bas.

---

## P3-C — Clarté sémantique & Wording adapté aux familles

### [T3-7] Remplacer « Fragment » par « Morceau de carte » et clarifier le « Bout de code »
- [ ] **Lever toute ambiguïté sur la nature de la pièce et du chiffre récolté**
  - **Constat actuel :** L'interface affiche `Fragment` et `Étape 1 : Gare de Lens — Fragment : 4`. Sur l'étape 1, lire `Fragment : 4` (ou voir uniquement un gros `4` sur la pièce 1 du puzzle) fait immédiatement croire que c'est le *fragment n°4* au lieu du chiffre **`4`** du code secret !
  - **Attendu :**
    - Remplacer le terme technique **`Fragment`** par **`Morceau de carte`** (ou **`Morceau`** en libellé court) sur tous les écrans joueurs ([`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php), [`login-register.blade.php`](../../resources/views/livewire/auth/login-register.blade.php), [`hub.blade.php`](../../resources/views/livewire/game/hub.blade.php), [`game-router.blade.php`](../../resources/views/livewire/game-router.blade.php), [`Scanner.php`](../../app/Livewire/Game/Scanner.php)). *(Conserver le modèle Eloquent `Fragment` et la table `fragments` en interne technique pour ne pas casser les migrations).*
    - Rendre le bout de code sans équivoque sur les fiches, listes et tuiles puzzle :
      - Dans la fiche souvenir & le récapitulatif : afficher par exemple `Morceau {{ $id }}/6  |  {{ $lieu }}  |  Bout de code : {{ $codePart }}` (pour les étapes 1–4) et `Indice lieu : {{ $codePart }}` / `Destination finale : {{ $codePart }}` pour les étapes 5–6 *(car `"Office de Tourisme"` / `"Coffre"` ne sont pas des chiffres de cadenas !)*.
      - Sur la tuile débloquée du plateau puzzle (`#puzzle-piece-1..4`), afficher **`Code : 4`** (ou `#1 · Code : 4`) au lieu d'un `4` isolé qui se confond avec le numéro d'étape.

### [T3-8] Supprimer tous les tirets longs (`—`) dans l'interface et utiliser `  |  `
- [ ] **Harmoniser les séparateurs visuels selon la préférence UX**
  - **Constat actuel :** Des tirets cadratins `—` subsistent dans [`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php) (`Carnet de Gali — Étape X/6`, `Étape X : Lieu — Fragment : ...`, `Lot déjà remis — Merci`), [`scanner.blade.php`](../../resources/views/livewire/game/scanner.blade.php) (`Étape X/6 — Scannez...`) et [`test-qr.blade.php`](../../resources/views/test-qr.blade.php).
  - **Attendu :**
    - Remplacer tous les `—` (et `–`) des textes visibles utilisateur par le séparateur **`  |  `** (ex : `Carnet de Gali  |  Étape 1/6`, `Étape 1/6  |  Scannez le repère sur la carte`, `Morceau 1/6  |  Bout de code : 4`) ou par une mise en forme en deux lignes/badges lorsque c'est plus lisible sur petit écran `360px`.

### [T3-9] Remplacer « Maître du Jeu / MJ » dans toute l'interface visible par les joueurs
- [ ] **Adopter un vocabulaire immersif et compréhensible pour les enfants**
  - **Constat actuel :** L'expression `Maître du Jeu` apparaît encore dans le tutoriel ([`login-register.blade.php`](../../resources/views/livewire/auth/login-register.blade.php)), l'aide et les messages du Hub ([`hub.blade.php`](../../resources/views/livewire/game/hub.blade.php)), le rappel du Scanner ([`scanner.blade.php`](../../resources/views/livewire/game/scanner.blade.php)), l'inventaire vide ([`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php)) et les messages de feedback ([`Scanner.php`](../../app/Livewire/Game/Scanner.php)).
  - **Attendu :**
    - Remplacer « Maître du Jeu » côté interface joueur par une formulation narrative claire pour un enfant et ses parents (ex : **`l'animateur de l'étape`** / **`le gardien de l'étape`** / **`le stand d'épreuve sur place`** — libellé exact à garder uniforme sur tous les écrans !).
    - Conserver strictement les codes QR `MJ_STEP_1..6` en base de données, dans [`Step.php`](../../app/Models/Step.php) et dans [`test-qr.blade.php`](../../resources/views/test-qr.blade.php) (outil interne/démo) pour préserver la compatibilité avec les QR codes imprimés.

---

## P3-D — Redirection vers l'Inventaire & Animation Puzzle

### [T3-10] Rediriger vers l'Inventaire après chaque morceau découvert + animer l'assemblage du puzzle
- [ ] **Valoriser immédiatement chaque récompense gagnée au fil de l'expédition**
  - **Constat actuel ([`Scanner.php`](../../app/Livewire/Game/Scanner.php) & [`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php)) :** Actuellement, valider une épreuve sur place (`MJ_STEP_1` à `MJ_STEP_5`) renvoie `scanTargetTab = 'hub'` au lieu d'aller voir le nouveau morceau dans l'inventaire (seule l'étape 6 redirige vers `'inventory'`). De plus, aucune animation ne met en valeur la pièce nouvellement ajoutée sur le puzzle.
  - **Attendu :**
    - **Redirection systématique sur découverte :** Dans [`Scanner.php`](../../app/Livewire/Game/Scanner.php), dès qu'un `MJ_STEP_X` (étapes **1 à 6**) est validé et débloque un morceau de carte, définir `scanTargetTab = 'inventory'` avec un CTA positif adapté (ex : `Placer le morceau dans ma carte →` pour 1–5, et `Voir la carte complète & le coffre →` pour 6/6). *(En Phase A, `MAP_LOC_X` continue bien de rediriger vers `'hub'` pour activer la boussole).*
    - **Animation de complétion du puzzle :** Émettre un événement Livewire ciblé (ex. `fragment-unlocked` avec `stepId`) vers l'Inventaire pour animer visuellement la pièce `#puzzle-piece-X` fraîchement posée (rebond / apparition magnétique + éclat doré temporaire à 1 seule lecture pour ne pas rejouer en boucle si on revient plus tard sur le sac à dos), et célébration renforcée lorsque les 6/6 pièces s'assemblent !
    - **Fluidité du retour vers l'étape suivante (point critique UX) :** Comme les étapes 1 à 5 envoient désormais vers l'Inventaire, ajouter en bas de [`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php) (quand `!$hasWon && $fragments->isNotEmpty()`) un gros bouton d'action clair (ex : **`Continuer vers l'étape suivante →`** / **`Nouvelle énigme sur le Hub →`**) pour que le joueur reparte vers la boussole/énigme en 1 tap sans avoir à chercher le petit bouton retour tout en haut.

---

## P3-E — Tests automatisés & Contrôle qualité mobile

### [T3-11] Mettre à jour les tests PHPUnit existants & valider les nouveautés sur viewports mobiles
- [ ] **Garantir 0 régression sur le parcours complet et l'ensemble des assertions**
  - Adapter les assertions existantes qui testaient explicitement les anciens libellés ou l'ancien comportement de redirection :
    - [`GameFlowTest.php`](../../tests/Feature/GameFlowTest.php) (`0 / 6 fragments`, `5 / 6 fragments`, `Maître du Jeu`).
    - [`P1NarrativeAndDesignTest.php`](../../tests/Feature/P1NarrativeAndDesignTest.php) (`Fragments et indices`, `Mince ! Mauvais Maître du Jeu...`, `#close-memory-modal-btn`).
    - [`ScanOverlayAndCompassPermissionTest.php`](../../tests/Feature/ScanOverlayAndCompassPermissionTest.php) (`scanTargetTab` qui passe de `'hub'` à `'inventory'` sur `MJ_STEP_1`).
    - [`StepSeederAndAuthTest.php`](../../tests/Feature/StepSeederAndAuthTest.php) et [`VisualReworkCompassAndDeckTest.php`](../../tests/Feature/VisualReworkCompassAndDeckTest.php) (`Le Maître du Jeu`, `6 fragments`, `— Fragment :`).
  - Ajouter les vérifications dédiées au lot T3 (absence de `—` / `Fragment` / `Maître du Jeu` côté UI joueur, bouton unique de fermeture dans la modale souvenir, redirection `'inventory'` sur `MJ_STEP_1..6`, attribut/animation de complétion puzzle, synchronisation tuto accueil/aide).
  - Exécuter `vendor/bin/pint --format agent`, `php artisan test --compact` et un contrôle Chrome headless multi-tailles (`360x640`, `375x667`, `412x915`).
