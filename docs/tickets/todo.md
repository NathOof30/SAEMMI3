# Backlog & Tickets à faire — L'Expédition Secrète (Lens)

> Ce document centralise et organise les évolutions de l'application PWA **L'Expédition Secrète** (Destination Lens Tourisme & Asmodee) en intégrant les décisions produit/UX, le scénario de [`docs/pdf/storytelling.pdf`](../pdf/storytelling.pdf) et les contraintes de **démonstration**.

---

## Décisions actées (Cadrage Produit & Démo)

- **Scénario & Étapes :** Alignement strict sur [`storytelling.pdf`](../pdf/storytelling.pdf). Le héros s'appelle **Gali** (remplacer *Galibrio*). Le grenier est le point de départ narratif du récit.
- **Gadget du kit physique :** On utilise une **loupe** (et non un filtre optique).
- **Dates de l'événement :** Pas d'intégration fonctionnelle ni de restriction par date dans l'application.
- **Distance terrain :** Le parcours complet (jusqu'aux Terrils 11/19 puis retour Office de Tourisme) est assumé à l'échelle d'une **journée complète** (point logistique connu pour la présentation orale / dossier).
- **Fin de partie (Lot Dobble) :** Pas d'interface back-office staff pour valider le QR code à l'Office de Tourisme — le QR code et le code final affichés dans l'app suffisent pour conclure la démo.
- **Outils Debug (`[•] debug` & simulation scan) :** **Conservés** car indispensables pour la démonstration en intérieur (téléportation GPS + simulation manuelle activable).

---

## Sommaire des priorités

- **P0 — Bloquant Gameplay & Parcours (Terminé)** : ~~Alignement des 6 étapes avec le storytelling, anti-spoiler en Phase A, filets de sécurité GPS (`?` de secours si proche mais hors zone 30 m) & suppression du faux `300 m`.~~
- **P1 — Cœur Expérience, Narration & DA** : Typographies Typekit (`hwt-artz` / `nunito`), inventaire RP en puzzle de carte, narration *histoire de Gali* + *Histoire de Lens*, messages de mauvais scan pédagogiques et humoristiques sans spoil, simplification du Scanner.
- **P2 — Aide, Résilience Réseau & Finition PWA** : Bouton Aide (`?`), détection perte de réseau, correction barre système du bas orange sur Android PWA, vibrations/autonomie capteurs.

---

## P0 — Correctifs Gameplay & Alignement Storytelling (Terminé)

### [P0-1] Aligner les étapes, noms et wording avec `storytelling.pdf`
- [x] **Mettre à jour [`StepSeeder.php`](../../database/seeders/StepSeeder.php), [`test-qr.blade.php`](../../resources/views/test-qr.blade.php) et les textes d'interface**
  - **Constat actuel :** Le seeder démarre sur *Place Jean Jaurès* et termine sur *Église Saint-Léger* (dans un ordre différent du storytelling).
  - **Attendu (ordre du storytelling) :**
    1. **La Gare de Lens** — Architecture en forme de locomotive *(répond à l'indice de départ du grenier : « cherche l'endroit où les trains se reposent… »)*.
    2. **Le Centre-Ville** — La vie dans les corons *(énigme d'observation sur une façade avec la **loupe** du carnet)*.
    3. **Le Stade Bollaert** — La passion Sang et Or ! *(énigme autour des couleurs de la ville)*.
    4. **Le Louvre-Lens** — Quand la mine devient un grand musée *(recherche dans le parc du musée)*.
    5. **Les Terrils de Loos-en-Gohelle** — Ascension des terrils jumeaux.
    6. **Le Final à l'Office de Tourisme** — Dernière étape devant/à l'Office de Tourisme menant au vrai coffre et au code à 4 chiffres.
  - **Point de vigilance scénario / puzzle à trancher lors de la création des assets (`[P1-2]`) :**
    - *Soit* le morceau du grenier est une **pièce d'introduction (0/6)** + **6 morceaux récoltés** sur les étapes 1 à 6 (dans ce cas, reformuler légèrement la mention « tout dernier morceau » aux Terrils ou faire de l'étape 6 l'obtention du verrou final à l'Office de Tourisme).
    - *Soit* le morceau du grenier = **pièce 1/6 déjà débloquée**, étapes 1 à 5 = 5 autres morceaux, et étape 6 = **guidage final pur** vers l'Office de Tourisme sans nouveau morceau de puzzle.
  - **Harmonisation des textes :** Utiliser uniquement le prénom **Gali** (jamais *Galibrio*) et remplacer toute mention de *filtre optique* par **loupe**.

### [P0-2] Anti-spoil strict en Phase A (`GUESSING_LOCATION`)
- [x] **Ne jamais révéler le nom de la destination avant le scan `MAP_LOC_X`**
  - **Constat actuel :** Sur la carte du Hub en Phase A, le titre affiche déjà `Destination : Étape X : [Nom du lieu]` (visible sur les captures actuelles), ce qui annule la recherche sur la carte papier.
  - **Attendu :**
    - En **Phase A** : afficher un titre neutre sans spoil (ex : `Destination : Indice n°X` ou `Destination : Mystère (Étape X/6)`), avec l'énigme de Gali et la consigne d'utiliser la loupe / carte papier.
    - En **Phase B** (une fois `MAP_LOC_X` validé) : révéler le nom du lieu (`Destination : Gare de Lens`) et activer la boussole.

### [P0-3] Déblocage Scanner si GPS imprécis en Phase B & suppression du `300 m` fictif
- [x] **Éviter qu'un joueur sur place reste bloqué hors du rayon des 30 m**
  - **Constat actuel :** Sans barre de navigation inférieure dans [`game-router.blade.php`](../../resources/views/livewire/game-router.blade.php), l'accès au Scanner en Phase B dépend uniquement du bouton `#hub-scanner-action-btn` qui s'affiche si `distanceMeters <= 30` ([`hub.blade.php`](../../resources/views/livewire/game/hub.blade.php#L647-L660)). De plus, si `distanceMeters === null`, la carte affiche `'300 m'` en dur.
  - **Attendu :**
    - **Corriger le placeholder :** Remplacer `'300 m'` par `'Signal GPS...'` (ou `'--- m'`) tant qu'aucune position n'est reçue, et afficher un message clair si la permission GPS est refusée.
    - **Filet de sécurité proximité (`?` GPS) :** Conserver l'effet du gros bouton or `Scanner le spot` sous `30 m`, mais lorsqu'un joueur s'approche dans une zone tampon (ex : entre `31 m` et `100–120 m`, ou si le GPS tarde/échoue), faire apparaître un petit bouton/encart `?` contextuel :
      > *« Vous êtes arrivé mais le GPS n'est pas précis ? »* → **`[ Ouvrir le scanner ]`**
    - Permet aussi de calibrer un rayon légèrement plus large (`50–70 m`) sur les très grands sites (Stade Bollaert, Parc du Louvre-Lens, Terrils).

---

## P1 — Direction Artistique, Narration & Refonte des Écrans

### [P1-1] Intégrer les typographies officielles (Adobe Typekit)
- [ ] **Remplacer les polices système par `hwt-artz` (titres) et `nunito` (corps)**
  - **Ressource à ajouter** dans `<head>` de [`resources/views/components/layouts/app.blade.php`](../../resources/views/components/layouts/app.blade.php) :
    ```html
    <link rel="stylesheet" href="https://use.typekit.net/msd2zjt.css">
    ```
  - **Règles CSS globales :**
    - **Titres / Display / Boutons majeurs / Compteurs :** `font-family: "hwt-artz", "Impact", sans-serif; font-weight: 700; font-style: normal;` (adapter la graisse et les tailles selon les hiérarchies).
    - **Corps de texte / Énigmes / Consignes / Formulaires :** `font-family: "nunito", system-ui, -apple-system, sans-serif; font-weight: 400; font-style: normal;` (utiliser graisses `400`, `600`, `700`, `800`).
  - **Points d'attention :**
    - Remplacer les `font-family: system-ui, -apple-system, sans-serif;` en style inline dans toutes les vues (`app`, `game-router`, `login-register`, `hub`, `scanner`, `inventory`) par des classes/variables CSS centralisées (`--font-display`, `--font-body`).
    - Vérifier le rendu de tous les **accents et ligatures français** (`É, È, À, Ç, Œ` — ex : *L'Expédition Secrète*, *Lampe Cœur*, *Étape*) avec `"hwt-artz"`.
    - Définir `font-display: swap` et des fallbacks propres en cas de coupure réseau.

### [P1-2] Refondre l'Inventaire en « Carte au Trésor Puzzle » RP
- [ ] **Afficher les morceaux de carte comme un puzzle évolutif plutôt qu'une liste**
  - **Constat actuel :** [`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php) liste les fragments dans une simple `<ul>` avec le chiffre brut.
  - **Attendu :**
    - Remplacer la grille/liste par un plateau visuel RP (« Carnet / Carte de Gali ») assemblant les **morceaux de carte** (assets fournis) au bon emplacement exact, façon **puzzle qui se complète progressivement**.
    - **Pièces verrouillées :** Emplacements grisés / silhouette parchemin en pointillés.
    - **Pièces débloquées :** Asset graphique affiché à sa position exacte, révélant petit à petit les éléments visuels de l'énigme finale (**le lieu final, le coffre au trésor, et les chiffres du code secret**).
    - **Climax (6/6) :** Mise en valeur du puzzle complet + **Code final à 4 chiffres** façon cadenas + **QR code de retrait du lot** déjà en place (bonus démo : générer le QR localement en JS/SVG si possible pour ne pas dépendre d'`api.qrserver.com` en cas de Wi-Fi capricieux).

### [P1-3] Raconter « l'histoire » (RP de Gali & Lampe Cœur) et « l'Histoire » (Patrimoine de Lens)
- [ ] **Intégrer le lore de Gali et les explications patrimoniales sans alourdir l'interface**
  - **Architecture UX retenue (séparer marche guidée et lecture culturelle) :**
    1. **L'histoire (RP) dès l'Accueil & le Tutoriel ([`login-register.blade.php`](../../resources/views/livewire/auth/login-register.blade.php)) :**
       - Introduire **Gali**, la légende de la **« Lampe Cœur »** (la gaillette de charbon magique de son grand-père) et la découverte du premier morceau de papier jauni dans le grenier menant au 1er indice.
    2. **Sur le Hub en Phase B — Carte extensible (tap / swipe vers le haut) :**
       - Garder la carte du deck compacte par défaut pour laisser la boussole centrée et lisible pendant la marche.
       - En Phase B (uniquement après identification du lieu, pour éviter tout spoil), permettre d'ouvrir/étendre la carte vers le haut via une animation fluide (couvrant temporairement la boussole) pour afficher :
         - **Le mot de Gali (RP)** : transition narrative liée à ce lieu.
         - **L'Histoire du lieu (Patrimoine)** : courte anecdote culturelle et familiale sur le monument lensois (ex : architecture locomotive de la Gare, vie dans les corons, passion Sang et Or, ancienne fosse minière du Louvre-Lens, terrils UNESCO).
       - Un tap ou swipe vers le bas replie la carte dans le deck.
    3. **Dans l'Inventaire Puzzle — Fiches Souvenirs interactives :**
       - Cliquer sur un morceau de carte déjà débloqué ouvre sa fiche détaillée pour relire à tout moment le récit RP de Gali, les infos culturelles sur le lieu et le fragment de code associé.
  - **À rédiger :** Les 6 courtes fiches *RP Gali* + *Histoire patrimoniale* (à stocker sur la table `steps`).

### [P1-4] Simplifier l'écran Scanner & conditionner la simulation via Debug
- [ ] **Donner la priorité absolue à la caméra et déplacer l'input de simulation PC**
  - **Constat actuel :** [`scanner.blade.php`](../../resources/views/livewire/game/scanner.blade.php) conserve d'anciennes bannières texte `$message`/`$error` redondantes avec la notif plein écran, ainsi que le bloc *"Simuler un scan"* affiché en permanence en bas.
  - **Attendu :**
    - **Vue caméra épurée :** Maximiser la zone vidéo `#qr-reader` avec le viseur néo-brutaliste au centre, le bouton retour flottant et un rappel minimaliste (1 ligne) de ce qui est attendu (*carte* ou *Maître du Jeu*).
    - Supprimer les anciennes bannières inline `$message` / `$error` remplacées par l'overlay plein écran.
    - **Simulation PC dans Debug :** Masquer par défaut le formulaire de simulation manuelle dans le Scanner. Ajouter dans la modale **`[•] debug`** du Hub une case à cocher (sauvegardée en `localStorage` / store Alpine) **`Activer la simulation de scan (PC)`** pour l'afficher uniquement quand on la coche pour les tests/démonstrations sans caméra.
    - **Bonus terrain :** Bouton discret torche/flash dans le viseur si l'appareil photo le supporte.

### [P1-5] Messages d'erreur pédagogiques, humoristiques et anti-spoil sur mauvais QR
- [ ] **Adapter dynamiquement le message selon le type de mauvais QR scanné**
  - **Constat actuel :** [`Scanner.php`](../../app/Livewire/Game/Scanner.php#L122-L185) ne regarde pas à quelle étape appartient un mauvais code `MAP_LOC_Y` ou `MJ_STEP_Y`.
  - **Attendu :**
    1. **Mauvais `MAP_LOC_Y` en Phase A :** Identifier le lieu scanné sur la carte papier et répondre avec pédagogie **sans spoiler le bon lieu** (exemple exact attendu) :
       > *« Ça, c’est le stade Bollaert ! Les supporters Sang et Or y chantent Les Corons… mais aucun train ne s’y repose ! »*
       *(En Phase A, le joueur a lui-même pointé ce lieu sur sa carte : nommer le lieu scanné à tort ne spoile donc pas la cible à trouver).*
    2. **QR `MAP_LOC_Y` d'une étape déjà terminée (`Y < current_step`) :** Rappeler gentiment qu'on est déjà passé par `[Lieu Y]`.
    3. **QR `MJ_STEP_Y` scanné en Phase A ou en avance (`Y > current_step`) :** Message humoristique **sans jamais nommer le futur lieu** pour ne rien spoiler !
       > Ex. : *« Holà, téléportation détectée ! Ce Maître du Jeu ne peut pas encore être devant toi… Trouve d'abord la destination sur la carte papier ! »*
    4. **Mauvais `MJ_STEP_Y` ou `MAP_LOC_Y` rescanné en Phase B :** Distinguer clairement « mauvais badge animateur » (sans spoiler le nom du lieu si `Y > current_step`) vs « carte déjà validée, scanne l'animateur sur place ».
    5. **QR code externe inconnu :** Indiquer avec malice que ce QR code n'appartient pas au carnet de Gali.

---

## P2 — Aide, Résilience Réseau & Finition Mobile / PWA

### [P2-1] Créer le bouton `?` (Aide) sur le Hub et sa carte d'aide
- [ ] **Offrir un point de secours clair aux familles**
  - Ajouter un bouton **`?`** discret en haut du Hub (ex : en symétrie avec `[•] debug`, sans empiéter sur la boussole).
  - Ouvrir une **carte / modale d'aide** regroupant :
    - Rappel rapide des règles (rôle Enfant + loupe/carte vs rôle Parent + boussole/scanner).
    - Comment lire la boussole (pointer la pointe **JAUNE** droit devant soi, faire un "8" pour calibrer le magnétomètre).
    - Bouton pour revoir le tutoriel à 3 slides sans se déconnecter.
    - Que faire en cas de perte réseau, de caméra bloquée ou de GPS imprécis (avec rappel vers le scanner si on est sur place avec l'animateur).

### [P2-2] Notification en cas de perte de réseau & résilience hors-ligne
- [ ] **Éviter les crashs Livewire dans les zones sans 4G (bâtiments, parc, terrils)**
  - Écouter `window` `offline` / `online` + les erreurs réseau Livewire (`Livewire.hook('request', ...)`).
  - Afficher une **notification / bandeau d'avertissement non bloquant** dès la perte de connexion (*« Réseau perdu — La boussole continue de te guider, mais retrouve un peu de connexion pour valider un QR code »*), qui disparaît automatiquement au retour du réseau.
  - Si un scan échoue faute de réseau, afficher une alerte propre avec bouton **`[Réessayer]`** au lieu d'une erreur technique.

### [P2-3] Vérifier/corriger la barre système du bas restée orange en mode PWA (Android)
- [ ] **Finaliser l'habillage noir (`#000000`) haut et bas sur l'application installée**
  - Lors du dernier test réel sur Xiaomi Redmi Note 11, le haut était bien noir mais le bas sous la PWA restait orange : vérifier le comportement du WebAPK Android (`manifest.webmanifest`, `app.blade.php`, `viewport-fit`, et test après désinstallation/réinstallation propre pour purger le cache du WebAPK).

### [P2-4] Confort capteurs, vibrations & tests automatisés
- [ ] **Peaufiner les retours mobiles et maintenir la couverture de tests**
  - Ajouter de légères vibrations (`navigator.vibrate`) sur scan réussi / échoué (avec protection silencieuse pour iOS).
  - Mettre en pause le suivi GPS / gyroscope quand on quitte l'onglet Hub ou que le téléphone passe en veille (`visibilitychange`) pour préserver la batterie.
  - Mettre à jour les tests PHPUnit (`StepSeeder`, anti-spoil Phase A, messages d'erreur pédagogiques/anti-spoil, simulation debug) et exécuter `php artisan test` + `vendor/bin/pint --format agent` après chaque lot.

