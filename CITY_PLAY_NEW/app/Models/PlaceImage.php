<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'place_id',
        'image_url',
        'display_order',
        'caption',
    ];

    protected $appends = ['image_path'];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'created_at'    => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** Alias utilisé côté frontend (image_path = image_url). */
    public function getImagePathAttribute(): ?string
    {
        return $this->image_url;
    }
}
