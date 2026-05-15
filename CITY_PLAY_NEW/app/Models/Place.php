<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'environment_id',
        'name',
        'description',
        'latitude',
        'longitude',
        'validation_radius',
        'estimated_visit_minutes',
        'visit_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude'                => 'decimal:8',
            'longitude'               => 'decimal:8',
            'validation_radius'       => 'integer',
            'estimated_visit_minutes' => 'integer',
            'visit_order'             => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    /** Images du lieu (max 3-4) */
    public function images(): HasMany
    {
        return $this->hasMany(PlaceImage::class)->orderBy('display_order');
    }

    /** Énigmes liées à ce lieu (4 niveaux) */
    public function riddles(): HasMany
    {
        return $this->hasMany(Riddle::class);
    }
}
