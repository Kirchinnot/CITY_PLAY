<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionAnswer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'game_session_id',
        'riddle_id',
        'user_answer',
        'is_correct',
        'attempt_number',
        'response_time_seconds',
        'latitude',
        'longitude',
        'distance_from_target',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct'            => 'boolean',
            'attempt_number'        => 'integer',
            'response_time_seconds' => 'integer',
            'latitude'              => 'decimal:8',
            'longitude'             => 'decimal:8',
            'distance_from_target'  => 'float',
            'answered_at'           => 'datetime',
            'created_at'            => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function riddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class);
    }
}
