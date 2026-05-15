<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hint extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'riddle_id',
        'index',
        'content',
        'points_penalty',
    ];

    protected function casts(): array
    {
        return [
            'index'          => 'integer',
            'points_penalty' => 'integer',
            'created_at'     => 'datetime',
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
