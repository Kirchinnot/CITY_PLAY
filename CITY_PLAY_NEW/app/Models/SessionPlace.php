<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionPlace extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'game_session_id',
        'place_id',
        'riddle_id',
        'is_completed',
        'attempts_count',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_completed'   => 'boolean',
            'attempts_count' => 'integer',
            'completed_at'   => 'datetime',
            'created_at'     => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function riddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class);
    }
}
