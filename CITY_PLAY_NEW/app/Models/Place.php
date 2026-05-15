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
        'city_id',
        'name',
        'description',
        'lat',
        'lng',
        'validation_radius',
        'order_index',
        'estimated_time_min',
    ];

    protected function casts(): array
    {
        return [
            'lat'               => 'decimal:7',
            'lng'               => 'decimal:7',
            'validation_radius' => 'integer',
            'order_index'       => 'integer',
            'estimated_time_min'=> 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** Images du lieu (max 3-4 recommandées) */
    public function images(): HasMany
    {
        return $this->hasMany(PlaceImage::class)->orderBy('display_order');
    }

    /** Énigmes par niveau de difficulté */
    public function riddles(): HasMany
    {
        return $this->hasMany(Riddle::class);
    }

    /** Apparitions dans les sessions */
    public function sessionPlaces(): HasMany
    {
        return $this->hasMany(SessionPlace::class);
    }
}
