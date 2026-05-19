<?php

namespace App\Models;

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
}
