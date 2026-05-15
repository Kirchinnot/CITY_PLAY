<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiddleImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'riddle_id',
        'image_url',
        'display_order',
        'caption',
    ];

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

    public function riddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class);
    }
}
