<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'game_session_id',
        'user_id',
        'riddle_id',
        'points_earned',
        'points_speed',
        'points_distance',
        'points_hints_bonus',
        'points_difficulty',
        'hints_used',
        'time_taken_seconds',
        'distance_m',
        'is_blocked',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'points_earned'      => 'integer',
            'points_speed'       => 'integer',
            'points_distance'    => 'integer',
            'points_hints_bonus' => 'integer',
            'points_difficulty'  => 'integer',
            'hints_used'         => 'integer',
            'time_taken_seconds' => 'integer',
            'distance_m'         => 'decimal:2',
            'is_blocked'         => 'boolean',
            'resolved_at'        => 'datetime',
            'created_at'         => 'datetime',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function riddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class);
    }
}
