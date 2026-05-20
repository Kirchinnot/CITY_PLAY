# Fonctionnalités et implémentation — CityPlay

Ce document décrit les principales fonctionnalités ajoutées ou modifiées, ainsi que la façon dont elles ont été implémentées dans le projet.

---

## Table des matières

- Vue d'ensemble
- Backend — Seeders (localisation Bénin)
- Frontend — Map (carte), RiddleValidation
- Frontend — Corrections Vue (Dashboard, TextInput, Riddles)
- Tests & vérifications
- Étapes suivantes suggérées

---

## Vue d'ensemble

Les changements récents visent à :

- Localiser les données de test en contexte béninois (villes, lieux, énigmes, invitations).
- Corriger des erreurs runtime Vue (imports manquants, composants dupliqués, autofocus).
- Améliorer l'expérience de jeu sur la carte : basemap claire, affichage de la position du joueur et des lieux, recentrage automatique incluant le joueur.
- Permettre au joueur d'ouvrir la carte centrée sur le lieu courant depuis l'écran de validation d'énigme.

---

## Backend — Seeders

Fichiers modifiés :

- `database/seeders/CitySeeder.php` — villes Bénin créées (ex. Cotonou, Ouidah). Idem pour la structure idempotente.
- `database/seeders/PlaceSeeder.php` — remplacement des lieux de Lyon par des lieux béninois (Marché Dantokpa, Plage des Cocotiers, Musée Historique de Ouidah, Porte du Non-Retour, Palais Royal d'Abomey). Chaque lieu contient `name`, `lat`, `lng`, `description`.
- `database/seeders/RiddleSeeder.php` — énigmes adaptées à chaque lieu (questions, indices, difficulté, hints preservés).
- `database/seeders/UserSeeder.php` — correction du BOM/whitespace avant `<?php` (résout l'erreur de namespace) et mise à jour des comptes admin/joueurs (+229).
- `database/seeders/InvitationSeeder.php` — tokens d'invitation mis à jour pour contexte béninois.
- `database/seeders/GameSessionSeeder.php` & `TestGameSeeder.php` — sessions de test et exemples pointant vers la nouvelle ville et coordonnées.

Notes d'implémentation :

- Les seeders restent idempotents (utilisation de `firstOrCreate` / `updateOrCreate`) pour permettre des exécutions répétées.
- Après modification, exécuter :

```bash
php artisan migrate:fresh --seed
```

 pour valider la création des données.

---

## Frontend — Carte (resources/js/Pages/Gameplay/Map.vue)

Résumé des ajouts/modifications :

- Basemap claire : tile layer changé en Carto Light (`https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png`) pour une lecture diurne et un meilleur contraste des marqueurs.
- Affichage de la position du joueur :
  - `startTracking()` utilise `navigator.geolocation.watchPosition` pour suivre la position GPS.
  - `userLocation` est stockée en `ref` et `userMarker` est rendu avec un `L.divIcon` (ping + point bleu).
- Affichage des lieux :
  - `drawMarkers()` parcourt `gameState.value.city.places`, crée des `L.marker` avec `L.divIcon` personnalisés.
  - Le lieu focalisé (via `?place=<id>` ou `gameState.current_riddle.place`) est mis en évidence (icône plus grande/emerald) et son tooltip est ouvert.
- Recentrement intelligent :
  - `drawMarkers()` collecte les coordonnées des marqueurs et de `userLocation` et appelle `map.fitBounds(bounds, { padding, maxZoom })` si au moins deux points sont présents.
  - Si un seul point est présent, on centre et zoom sur ce lieu.
- Réactivité :
  - `watch(() => gameState.value, drawMarkers, { deep: true })` redessine les marqueurs quand l'état de jeu change.
  - `watch(() => userLocation.value, drawMarkers, { deep: true })` redessine automatiquement quand la position du joueur évolue afin d'ajuster les bounds.

Points d'attention :

- Vérifier que `gameState` contient bien `city.places` (null-safety déjà appliquée dans les guards du code).
- Les icônes sont construites via des `divIcon` et stylées avec Tailwind — revoir le rendu mobile/desktop pour l'alignement et contraste.

---

## Frontend — Validation d'énigme (resources/js/Pages/Gameplay/RiddleValidation.vue)

Modifications clés :

- Ajout d'un lien "VOIR SUR LA CARTE" qui redirige vers la carte du jeu avec le paramètre `?place=<id>` :

```vue
:href="route('player.game.map') + '?place=' + (props.riddle.place?.id ?? '')"
```

- Conservation du mécanisme `VALIDER SUR PLACE` :
  - L'action `validatePresence()` envoie la position lat/lng au backend via `player.riddle.validate-presence`.
  - Le bouton est activé uniquement quand `isInValidationZone` est vrai (logique calculée par la comparaison de distance vs `validation_radius`).

Considération UX :

- Le lien vers la carte laisse le joueur vérifier visuellement la position avant de valider. Optionnellement, on peut implémenter un redirect automatique au premier clic (fonctionnalité non activée par défaut).

---

## Frontend — Corrections Vue mineures

Fichiers modifiés :

- `resources/js/Pages/Dashboard.vue` — import manquant `router` ajouté pour corriger l'avertissement runtime.
- `resources/js/Pages/Admin/Riddles.vue` — suppression d'un composant dupliqué `NotificationDescartes` pour éliminer les warnings et conflits de rendu.
- `resources/js/Components/TextInput.vue` — ajout d'un garde `autofocus` pour éviter les warnings sur l'attribut `autofocus` utilisé conditionnellement dans des components contrôlés.

Ces corrections résolvent des erreurs de console et améliorent la stabilité de l'UI.

---

## Tests & vérifications

Vérifications recommandées :

1. Backend :

```bash
php artisan migrate:fresh --seed
```

  - Confirmer qu'aucune erreur PHP (BOM/namespace) n'apparaît et que les villes/lieux/énigmes sont créés.

2. Frontend :

```bash
npm run dev
# ou pour build production
npm run build
```

  - Ouvrir l'application et accéder à une partie de jeu :
    - Sur la carte : vérifier la basemap claire, la présence de marqueurs pour les lieux, la bulle du lieu focalisé si `?place=<id>` est présent.
    - Vérifier que votre navigateur autorise la géolocalisation et que le marqueur joueur apparaît et bouge quand vous simulez/déplacez la position.
    - Depuis `RiddleValidation.vue`, cliquer sur "VOIR SUR LA CARTE" et vérifier le focus.

3. Endpoints serveur :

  - Tester `player.riddle.validate-presence` en envoyant lat/lng et vérifier la logique de validation côté serveur.

---

## Étapes suivantes suggérées

- Améliorer les popups des marqueurs : distance en temps réel, temps estimé, bouton "Aller ici" qui ouvre la navigation.
- Ajouter un petit tutoriel dans l'UI indiquant l'icône du joueur vs l'icône du lieu.
- Mesurer et ajuster l'accessibilité/contraste des icônes sur la basemap claire.
- Optionnel : rediriger automatiquement vers la carte au premier clic sur "VALIDER SUR PLACE" si la position n'est pas disponible.

---

## Schéma des tables principales et relations

Ci-dessous un résumé des tables les plus utilisées par l'application, leurs colonnes importantes (aperçu) et les relations qui les lient.

- `cities` (modèle `City`)
  - Colonnes clés : `id`, `name`, `lat`, `lng`, `country`, `created_by`.
  - Relations :
    - `hasMany` -> `places` (`places.city_id`).
    - `hasMany` -> `invitations` (`invitations.city_id`).

- `places` (modèle `Place`)
  - Colonnes clés : `id`, `city_id`, `name`, `description`, `lat`, `lng`, `validation_radius`, `order_index`, `estimated_time_min`.
  - Relations :
    - `belongsTo` -> `city`.
    - `hasMany` -> `riddles` (`riddles.place_id`).
    - `hasMany` -> `images` (table `place_images`).
    - `hasMany` -> `session_places` (table `session_places`) — entrée de la place dans une session.

- `riddles` (modèle `Riddle`)
  - Colonnes clés : `id`, `place_id`, `title`, `question`, `difficulty`, `options`, `answer`, `points_base`, `time_limit_seconds`.
  - Relations :
    - `belongsTo` -> `place`.
    - `hasMany` -> `hints` (table `hints`).
    - `hasMany` -> `images` (table `riddle_images`).
    - `hasMany` -> `scores` (table `scores`).

- `users` (modèle `User`)
  - Colonnes clés : `id`, `name`, `email`, `phone`, `role`, `avatar`.
  - Relations :
    - `hasMany` -> `cities` (créées par l'admin via `created_by`).
    - `hasMany` -> `invitations` (créées par l'admin via `created_by`).
    - `hasMany` -> `hosted_sessions` (table `game_sessions`, colonne `host_user_id`).
    - `belongsToMany` -> `game_sessions` via pivot `game_players` (table `game_players`).
    - `hasMany` -> `scores`.

- `invitations` (modèle `Invitation`)
  - Colonnes clés : `id`, `token`, `city_id`, `created_by`, `mode`, `difficulty`, `duration_minutes`, `expires_at`.
  - Relations :
    - `belongsTo` -> `city`.
    - `belongsTo` -> `creator` (`users.created_by`).
    - `hasMany` -> `game_sessions`.

- `game_sessions` (modèle `GameSession`)
  - Colonnes clés : `id`, `invitation_id`, `city_id`, `host_user_id`, `mode`, `difficulty`, `available_minutes`, `status`, `current_place_index`, `total_places`, `solved_places`, `started_at`, `completed_at`.
  - Relations :
    - `belongsTo` -> `invitation`.
    - `belongsTo` -> `city`.
    - `belongsTo` -> `host` (`users.host_user_id`).
    - `belongsToMany` -> `players` via pivot `game_players` (modèle `GamePlayer`).
    - `hasMany` -> `gamePlayers` (entrées pivot), `sessionPlaces` (table `session_places`), `scores`, `achievements`.

- `game_players` (pivot modèle `GamePlayer`)
  - Colonnes clés : `game_session_id`, `user_id`, `joined_at`, `current_riddle_id`, `last_lat`, `last_lng`, `last_seen_at`, `is_active`.
  - Rôle : pivot N:N entre `users` et `game_sessions` contenant l'état en session par joueur (position GPS, riddle courant, timestamps).

- `session_places` (modèle `SessionPlace`)
  - Colonnes clés : `game_session_id`, `place_id`, `order_index`, `is_completed`, `completed_at`.
  - Rôle : définit la séquence des `places` pour une `game_session` (ordonnancement et statut par place dans la session).

- `riddle_attempts` (modèle `RiddleAttempt`)
  - Colonnes clés : `game_session_id`, `riddle_id`, `user_id`, `selected_answer`, `qcm_validated_at`.
  - Rôle : historise les tentatives de réponse QCM d'un joueur pour une énigme donnée.

- `scores` (modèle `Score`)
  - Colonnes clés : `game_session_id`, `user_id`, `riddle_id`, `points_earned`, `points_speed`, `points_distance`, `hints_used`, `time_taken_seconds`, `distance_m`, `resolved_at`.
  - Rôle : stocke les points gagnés par joueur / énigme et les métriques associées.

### Diagramme logique (rapide)

- `users` 1 — N `game_sessions` (hosted_sessions)
- `users` N — N `game_sessions` via `game_players` (pivot `game_players`)
- `cities` 1 — N `places`
- `places` 1 — N `riddles`
- `game_sessions` 1 — N `session_places` -> chaque `session_place` `belongsTo` `place`
- `riddles` 1 — N `riddle_attempts`
- `riddles` 1 — N `scores` ; `users` 1 — N `scores` ; `game_sessions` 1 — N `scores`

Ces relations sont implémentées via les méthodes Eloquent (`hasMany`, `belongsTo`, `belongsToMany`) visibles dans les modèles correspondants (ex. `GameSession::players()`, `Place::riddles()`, `SessionPlace::place()`).

---

Fichier créé automatiquement par l'agent — si tu veux que j'ajoute des captures d'écran, des extraits de code précis (lignes) ou des exemples de requêtes API pour les endpoints, dis-moi lesquelles et je les insère.
