<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GamePlayer extends Pivot
{
    protected $table = 'game_players';

    public $timestamps = false;

    protected $fillable = [
        'game_session_id',
        'user_id',
        'joined_at',
        'current_riddle_id',
        'last_lat',
        'last_lng',
        'last_seen_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'joined_at'    => 'datetime',
            'last_lat'     => 'decimal:7',
            'last_lng'     => 'decimal:7',
            'last_seen_at' => 'datetime',
            'is_active'    => 'boolean',
            'created_at'   => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentRiddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class, 'current_riddle_id');
    }
}
