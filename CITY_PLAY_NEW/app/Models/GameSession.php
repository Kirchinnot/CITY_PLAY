<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'city_id',
        'start_place_id',
        'host_user_id',
        'mode',
        'team_size',
        'difficulty',
        'locomotion',
        'available_minutes',
        'status',
        'current_place_index',
        'total_places',
        'solved_places',
        'started_at',
        'paused_at',
        'total_pause_seconds',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'available_minutes'   => 'integer',
            'current_place_index' => 'integer',
            'total_places'        => 'integer',
            'solved_places'       => 'integer',
            'total_pause_seconds' => 'integer',
            'started_at'          => 'datetime',
            'paused_at'           => 'datetime',
            'completed_at'        => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isActive(): bool    { return $this->status === 'active'; }
    public function isPaused(): bool    { return $this->status === 'paused'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isAbandoned(): bool { return $this->status === 'abandoned'; }
    public function isFinished(): bool  { return in_array($this->status, ['completed', 'abandoned']); }

    public function isInProgress(): bool
    {
        return in_array($this->status, ['active', 'paused']);
    }

    /**
     * Instant de référence pour le calcul du temps joué (gelé en pause).
     */
    public function timerReference(): Carbon
    {
        if ($this->isPaused() && $this->paused_at) {
            return $this->paused_at;
        }

        return now();
    }

    /**
     * Secondes de jeu effectives (hors pauses cumulées).
     */
    public function getElapsedSeconds(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        $elapsed = $this->started_at->diffInSeconds($this->timerReference());

        return max(0, $elapsed - ($this->total_pause_seconds ?? 0));
    }

    /**
     * Secondes restantes dans le budget.
     */
    public function getRemainingSeconds(): int
    {
        if (!$this->started_at || $this->isFinished()) {
            return 0;
        }

        $budget = ($this->available_minutes ?? 0) * 60;

        return max(0, $budget - $this->getElapsedSeconds());
    }

    public function isTimeExpired(): bool
    {
        return $this->started_at
            && !$this->isFinished()
            && $this->getRemainingSeconds() <= 0;
    }

    /**
     * Niveau d'alerte : ok | warning | critical | expired
     */
    public function getTimeWarningLevel(): string
    {
        $remaining = $this->getRemainingSeconds();

        if ($remaining <= 0) {
            return 'expired';
        }
        if ($remaining <= 300) {
            return 'critical';
        }
        if ($remaining <= 900) {
            return 'warning';
        }

        return 'ok';
    }

    /**
     * Comptabilise la pause en cours dans total_pause_seconds (sans changer le status).
     */
    public function accumulateOpenPause(): void
    {
        if (!$this->isPaused() || !$this->paused_at) {
            return;
        }

        $this->total_pause_seconds = ($this->total_pause_seconds ?? 0)
            + $this->paused_at->diffInSeconds(now());
        $this->paused_at = null;
    }

    /**
     * Snapshot timer pour API / Inertia.
     */
    public function toTimerArray(): array
    {
        return [
            'started_at' => $this->started_at,
            'paused_at' => $this->paused_at,
            'total_pause_seconds' => $this->total_pause_seconds ?? 0,
            'available_minutes' => $this->available_minutes,
            'elapsed_seconds' => $this->getElapsedSeconds(),
            'remaining_seconds' => $this->getRemainingSeconds(),
            'warning_level' => $this->getTimeWarningLevel(),
            'is_expired' => $this->isTimeExpired(),
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /** Joueurs participant à cette session */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_players', 'game_session_id', 'user_id')
                    ->withPivot(['joined_at', 'current_riddle_id', 'last_lat', 'last_lng', 'last_seen_at', 'is_active'])
                    ->using(GamePlayer::class);
    }

    /** Entrées game_players directes */
    public function gamePlayers(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    /** Lieux sélectionnés pour cette session */
    public function sessionPlaces(): HasMany
    {
        return $this->hasMany(SessionPlace::class)->orderBy('order_index');
    }

    /** Scores de cette session */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /** Badges débloqués durant cette session */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    /**
     * Récupère l'énigme actuelle pour cette session.
     */
    public function getCurrentRiddleAttribute()
    {
        // On récupère le lieu actuel basé sur l'index de progression
        $currentPlace = $this->sessionPlaces()
            ->where('order_index', $this->current_place_index)
            ->first();
        
        if (!$currentPlace) return null;

        // On cherche l'énigme correspondante au lieu ET à la difficulté de la session
        return Riddle::where('place_id', $currentPlace->place_id)
            ->where('difficulty', $this->difficulty)
            ->with(['hints', 'images'])
            ->first();
    }

    /**
     * Calcule le score total de la session.
     */
    public function getTotalScoreAttribute(): int
    {
        return $this->scores()->sum('points_earned');
    }
}
