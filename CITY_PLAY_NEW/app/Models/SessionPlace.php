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
        'order_index',
        'is_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'order_index'  => 'integer',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
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

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
