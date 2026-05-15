<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Riddle extends Model
{
    use HasFactory;

    protected $fillable = [
        'place_id',
        'title',
        'question',
        'difficulty',
        'points',
        'time_limit_seconds',
    ];

    protected function casts(): array
    {
        return [
            'points'             => 'integer',
            'time_limit_seconds' => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** Images de l'énigme */
    public function images(): HasMany
    {
        return $this->hasMany(RiddleImage::class)->orderBy('display_order');
    }

    /** Réponse correcte (relation 1-1) */
    public function answer(): HasOne
    {
        return $this->hasOne(RiddleAnswer::class);
    }

    /** Réponses des joueurs */
    public function sessionAnswers(): HasMany
    {
        return $this->hasMany(SessionAnswer::class);
    }
}
