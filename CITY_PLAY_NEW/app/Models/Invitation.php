<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'city_id',
        'created_by',
        'mode',
        'difficulty',
        'locomotion',
        'max_players',
        'duration_minutes',
        'expires_at',
        'used_count',
    ];

    protected function casts(): array
    {
        return [
            'max_players'      => 'integer',
            'duration_minutes' => 'integer',
            'used_count'       => 'integer',
            'expires_at'       => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Sessions créées depuis cette invitation */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }
}
