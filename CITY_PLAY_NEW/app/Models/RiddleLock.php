<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiddleLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'riddle_id',
        'user_id',
        'locked_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function riddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
