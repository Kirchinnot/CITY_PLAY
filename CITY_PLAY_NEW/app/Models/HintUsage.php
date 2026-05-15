<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HintUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_session_id',
        'riddle_id',
        'hint_id',
        'points_deducted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function riddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class);
    }

    public function hint(): BelongsTo
    {
        return $this->belongsTo(Hint::class);
    }
}
