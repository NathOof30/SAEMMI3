# Archives des Tickets Terminés — L'Expédition Secrète (Lens)

> Ce document archive l'ensemble des lots déjà implémentés, testés et validés (**P0, P1 et P2** — vagues T1 à T2).

---

## Décisions actées (Cadrage Produit & Démo)

- **Scénario & Étapes :** Alignement strict sur [`storytelling.pdf`](../pdf/storytelling.pdf). Le héros s'appelle **Gali** (remplacement définitif de *Galibrio*). Le grenier est le point de départ narratif du récit.
- **Gadget du kit physique :** Utilisation d'une **loupe** (et non d'un filtre optique).
- **Dates de l'événement :** Pas d'intégration fonctionnelle ni de restriction par date dans l'application.
- **Distance terrain :** Le parcours complet (jusqu'aux Terrils 11/19 puis retour à l'Office de Tourisme) est assumé à l'échelle d'une **journée complète**.
- **Fin de partie (Lot Dobble) :** Pas d'interface back-office staff pour valider le QR code à l'Office de Tourisme — le QR code généré localement et le code final affichés dans l'app suffisent pour conclure la démo.
- **Outils Debug (`[•] debug` & simulation scan) :** Conservés car indispensables pour la démonstration en intérieur (téléportation GPS + simulation manuelle activable).

---

## P0 — Correctifs Gameplay & Alignement Storytelling (Terminé)

### [P0-1] Aligner les étapes, noms et wording avec `storytelling.pdf`
- [x] Mise à jour de [`StepSeeder.php`](../../database/seeders/StepSeeder.php), [`test-qr.blade.php`](../../resources/views/test-qr.blade.php) et des textes d'interface selon l'ordre du storytelling :
  1. **La Gare de Lens**
  2. **Le Centre-Ville**
  3. **Le Stade Bollaert-Delelis**
  4. **Le Musée Louvre-Lens**
  5. **Les Terrils de Loos-en-Gohelle**
  6. **L'Office de Tourisme**
- [x] Harmonisation **Gali** et remplacement de toute mention de *filtre optique* par **loupe**.

### [P0-2] Anti-spoil strict en Phase A (`GUESSING_LOCATION`)
- [x] Masquage du nom de la destination avant validation de `MAP_LOC_X` : affichage de `Destination : Mystère (Étape X/6)` avec l'énigme de Gali et la consigne d'observation de la carte papier.
- [x] Révélation du nom du lieu et activation de la boussole uniquement en Phase B (`WALKING_TO_TARGET`).

### [P0-3] Déblocage Scanner si GPS imprécis en Phase B & suppression du `300 m` fictif
- [x] Remplacement du placeholder `'300 m'` par `'Signal GPS...'` (et messages clairs en cas de refus/indisponibilité GPS).
- [x] Ajout du filet de sécurité contextuel **`?`** entre zone d'approche et délai GPS long (*« Vous êtes arrivé mais le point GPS n'est pas précis ? » → Ouvrir le scanner*).
- [x] Calibration des rayons de validation sur les grands sites (`65 m` Bollaert, `60 m` Louvre-Lens, `70 m` Terrils).

---

## P1 — Direction Artistique, Narration & Refonte des Écrans (Terminé)

### [P1-1] Intégrer les typographies officielles (Adobe Typekit)
- [x] Intégration de `https://use.typekit.net/msd2zjt.css` dans [`app.blade.php`](../../resources/views/components/layouts/app.blade.php).
- [x] Variables CSS `--font-display` (`"hwt-artz"`) et `--font-body` (`"nunito"`) déployées dans l'ensemble des vues sans `font-family: system-ui`.

### [P1-2] Refondre l'Inventaire en « Carte au Trésor Puzzle » RP
- [x] Refonte de [`inventory.blade.php`](../../resources/views/livewire/game/inventory.blade.php) avec grille puzzle 6 pièces (`public/images/puzzle/piece-1..6.*` supportés avec SVG placeholders).
- [x] Révélation progressive des morceaux débloqués, cadenas final à 4 chiffres (`4819`) et QR code de retrait généré localement en SVG hors-ligne (`window.generateExpeditionQrSvg`) avec repli API.

### [P1-3] Raconter « l'histoire » (RP de Gali & Lampe Cœur) et « l'Histoire » (Patrimoine de Lens)
- [x] Introduction de **Gali** et de la **Lampe Cœur** dès [`login-register.blade.php`](../../resources/views/livewire/auth/login-register.blade.php).
- [x] Ajout du tiroir extensible (*Le mot de Gali* + *L'Histoire du lieu*) sur le Hub en Phase B et des fiches souvenirs cliquables sur chaque pièce débloquée dans l'Inventaire.

### [P1-4] Simplifier l'écran Scanner & conditionner la simulation via Debug
- [x] Suppression des anciennes bannières inline `$message`/`$error`, maximisation de `#qr-reader`, rappel compact de phase et bouton torche si supporté.
- [x] Masquage par défaut de `#scanner-manual-sim-panel`, activable via `#debug-manual-scan-toggle` dans la modale `[•] debug` (`localStorage`).

### [P1-5] Messages d'erreur pédagogiques, humoristiques et anti-spoil sur mauvais QR
- [x] Adaptation contextuelle dans [`Scanner.php`](../../app/Livewire/Game/Scanner.php) et [`Step.php`](../../app/Models/Step.php) selon : mauvais `MAP_LOC_Y` (pédagogique sans spoil), `MAP_LOC_Y` déjà complété, `MJ_STEP_Y` scanné en avance (*« Holà, téléportation détectée ! »* sans nommer les futures étapes), erreur Phase B et QR externe inconnu.

---

## P2 — Aide, Résilience Réseau & Finition Mobile / PWA (Terminé)

### [P2-1] Créer le bouton `?` (Aide) sur le Hub et sa carte d'aide
- [x] Bouton `#hub-help-btn` (`?`) en symétrie avec `[•] debug` et modale `#hub-help-modal` regroupant rôles Enfant/Parent, lecture de la pointe **JAUNE OR** + calibration en « 8 », dépannage (réseau, caméra, GPS + raccourci scanner) et relecture du tutoriel en 3 slides sans déconnexion.

### [P2-2] Notification en cas de perte de réseau & résilience hors-ligne
- [x] Bandeau non bloquant `#offline-network-banner` / `#online-restored-banner` sur `offline`/`online`, interception des pannes Livewire 4 via `Livewire.interceptRequest` (`onFailure`, `onError`), et garde hors-ligne en phase capture sur le scanner avec alerte `#scanner-network-error-alert` + bouton **Réessayer**.

### [P2-3] Vérifier/corriger la barre système du bas restée orange en mode PWA (Android)
- [x] Ajout de `"id": "/"` dans [`manifest.webmanifest`](../../public/manifest.webmanifest), balises `msapplication-navbutton-color` / `msapplication-TileColor` à `#000000` et suppression de `max-height: 100dvh` sur `html` dans [`app.blade.php`](../../resources/views/components/layouts/app.blade.php).
- [x] Diagnostic confirmé : sur Android Chrome WebAPK, l'ancien `#d97706` reste en cache côté OS sur la barre basse tant que la PWA n'est pas désinstallée/réinstallée.

### [P2-4] Confort capteurs, vibrations & tests automatisés
- [x] Vibrations `navigator.vibrate` (protégées pour iOS) via `window.expeditionVibrate` sur validation/échec scan et arrivée dans la zone cible.
- [x] Pause/reprise GPS (`watchPosition`) et boussole (`deviceorientation` / `deviceorientationabsolute`) sur `@tab-switched.window` et `visibilitychange`, plus lecture dynamique des données de phase sur `#hub-compass-root`.
- [x] Suite PHPUnit complète verte (`106 tests`, `1301 assertions`), `vendor/bin/pint` vert, et vérification E2E Chrome headless.

