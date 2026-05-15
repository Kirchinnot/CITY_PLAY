<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'environment_id',
        'current_place_id',
        'current_riddle_id',
        'transport_mode',
        'difficulty',
        'available_minutes',
        'score',
        'total_riddles',
        'solved_riddles',
        'failed_riddles',
        'status',
        'started_at',
        'paused_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'available_minutes' => 'integer',
            'score'             => 'integer',
            'total_riddles'     => 'integer',
            'solved_riddles'    => 'integer',
            'failed_riddles'    => 'integer',
            'started_at'        => 'datetime',
            'paused_at'         => 'datetime',
            'ended_at'          => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isActive(): bool   { return $this->status === 'active'; }
    public function isPaused(): bool   { return $this->status === 'paused'; }
    public function isFinished(): bool { return in_array($this->status, ['completed', 'abandoned']); }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function currentPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'current_place_id');
    }

    public function currentRiddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class, 'current_riddle_id');
    }

    /** Lieux assignés à cette session */
    public function sessionPlaces(): HasMany
    {
        return $this->hasMany(SessionPlace::class);
    }

    /** Historique de toutes les réponses */
    public function sessionAnswers(): HasMany
    {
        return $this->hasMany(SessionAnswer::class);
    }
}
