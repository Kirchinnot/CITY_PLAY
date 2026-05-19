# Temps de jeu — Spécification CITY_PLAY

## Principe directeur

**Le serveur est la source de vérité.** Le chronomètre affiché côté client est une projection du calcul serveur, resynchronisé régulièrement. Toute action de gameplay (énigme, score) est rejetée si le temps est écoulé ou si la session est en pause.

---

## Modèle de données (`game_sessions`)

| Champ | Rôle |
|-------|------|
| `available_minutes` | Budget de jeu accordé (défini au lobby) |
| `started_at` | Début effectif du chrono (passage `pending` → `active`) |
| `paused_at` | Début de la pause en cours (`NULL` si partie en cours) |
| `total_pause_seconds` | Cumul des pauses **terminées** (reprises) |
| `status` | `pending` \| `active` \| `paused` \| `completed` \| `abandoned` |
| `completed_at` | Fin de partie (normale, abandon ou expiration) |

### Formules

```
elapsed = (référence_temps − started_at) − total_pause_seconds
remaining = (available_minutes × 60) − elapsed
référence_temps = paused_at  si status = paused
                 = now()     si status = active
```

Pendant une pause, le chrono est **gelé** : `paused_at` sert de borne supérieure pour le calcul d’elapsed.

---

## Cycle de vie

```
pending ──start()──► active ◄──resume()── paused
                        │                      │
                        │         pause()      │
                        └──────────────────────┘
                        
active/paused ──expire()──► completed  (temps écoulé)
active/paused ──abandon()──► abandoned
active ──completeSession()──► completed  (tous les lieux résolus)
```

---

## Chronomètre

- **Affichage** : `MM:SS` ou `Xh YYm` si > 1 h
- **Niveaux d’alerte** (`warning_level`) :
  - `ok` : > 15 min restantes
  - `warning` : ≤ 15 min (affichage ambre)
  - `critical` : ≤ 5 min (affichage rouge pulsant)
  - `expired` : 0 seconde → fin automatique de session

---

## Pauses

- **Qui** : hôte uniquement (`GameSessionPolicy::manage`)
- **Effet** : `active` → `paused`, enregistre `paused_at`
- **Chrono** : arrêté côté client et serveur
- **Gameplay** : aucune validation d’énigme possible en pause

---

## Reprise

- **Qui** : hôte uniquement
- **Effet** : durée `(now − paused_at)` ajoutée à `total_pause_seconds`, `paused_at` effacé, `paused` → `active`
- **Chrono** : reprend au même elapsed qu’avant la pause

---

## Sauvegardes automatiques

Il n’y a **pas** de sauvegarde locale du timer (pas de `localStorage` pour le temps).

La progression est **persistée en base** à chaque action :
- scores, lieux complétés, tentatives QCM (`riddle_attempts`)
- état session (`current_place_index`, `solved_places`, `status`)
- présence joueur (`game_players.last_seen_at` mis à jour à chaque requête Inertia)

**Reprise après interruption** (fermeture app, perte réseau) :
1. Au prochain chargement, `HandleInertiaRequests` recharge `gameState`
2. `syncTimerState()` recalcule le temps et expire la session si nécessaire
3. Le joueur retrouve la carte / le dashboard avec l’état exact

**Sync API** : `POST /player/game-sessions/{session}/sync` — heartbeat toutes les 30 s en jeu pour réaligner le client.

---

## Abandon

- **Qui** : hôte uniquement
- **Effet** : si en pause, la pause ouverte est comptabilisée dans `total_pause_seconds`, puis `status = abandoned`, `completed_at = now()`
- **Redirection** : dashboard

---

## Expiration du temps

- **Détection** : serveur à chaque `syncTimerState()` et avant toute action d’énigme
- **Effet** : `status = completed`, `completed_at = now()` (fin « temps écoulé »)
- **Client** : alerte + redirection vers le bilan (`summary`)

---

## Sessions interrompues

| Situation | Comportement |
|-----------|--------------|
| App fermée en `active` | Temps continue ; à la reconnexion, remaining diminué |
| App fermée en `paused` | Temps gelé indéfiniment jusqu’à reprise hôte |
| Session `pending` (lobby) | Pas de chrono ; dashboard propose de rejoindre le lobby |
| Multi-appareil | Même `gameState` via DB ; sync API harmonise |

---

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `app/Models/GameSession.php` | `getElapsedSeconds()`, `getRemainingSeconds()`, `isTimeExpired()` |
| `app/Services/Session/GameSessionService.php` | pause, resume, abandon, expire, sync |
| `app/Http/Middleware/HandleInertiaRequests.php` | Partage `gameState.timer` enrichi |
| `resources/js/composables/useGameTimer.js` | Chronomètre client + sync serveur |
| `resources/js/Pages/Gameplay/Map.vue` | HUD timer, pause, alertes |
