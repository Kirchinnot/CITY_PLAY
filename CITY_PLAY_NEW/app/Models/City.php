<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'country',
        'banner_image',
        'lat',
        'lng',
        'avg_duration_minutes',
        'retention_days',
        'is_published',
        'outro_config',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'lat'                  => 'decimal:7',
            'lng'                  => 'decimal:7',
            'avg_duration_minutes' => 'integer',
            'retention_days'       => 'integer',
            'is_published'         => 'boolean',
            'outro_config'         => 'array', // JSON auto-décodé
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /** Admin ayant créé cette ville */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Lieux du parcours, triés par order_index */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class)->orderBy('order_index');
    }

    /** Invitations liées à cette ville */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /** Sessions de jeu sur cette ville */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }
}
