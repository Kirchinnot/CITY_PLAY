<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiddleAttempt extends Model
{
    protected $fillable = [
        'game_session_id',
        'riddle_id',
        'user_id',
        'selected_answer',
        'qcm_validated_at',
    ];

    protected function casts(): array
    {
        return [
            'qcm_validated_at' => 'datetime',
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

    public function isQcmValidated(): bool
    {
        return $this->qcm_validated_at !== null;
    }
}
