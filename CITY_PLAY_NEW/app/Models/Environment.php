<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Environment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'country',
        'description',
        'cover_image',
        'retention_days',
        'is_published',
        'conclusion',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published'   => 'boolean',
            'retention_days' => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /** Admin ayant créé cet environnement */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Lieux du parcours */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class)->orderBy('visit_order');
    }

    /** Sessions de jeu sur cet environnement */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }
}
