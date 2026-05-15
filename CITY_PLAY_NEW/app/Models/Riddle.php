<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Riddle extends Model
{
    use HasFactory;

    protected $fillable = [
        'place_id',
        'title',
        'question',
        'difficulty',
        'points_base',
        'time_limit_seconds',
        'options',
        'answer',
    ];

    protected function casts(): array
    {
        return [
            'points_base'        => 'integer',
            'time_limit_seconds' => 'integer',
            'options'            => 'array',
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
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Vérifie la réponse du joueur (insensible à la casse et aux espaces).
     */
    public function isCorrect(string $userAnswer): bool
    {
        return strtolower(trim($userAnswer)) === strtolower(trim($this->answer));
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** Images illustrant l'énigme */
    public function images(): HasMany
    {
        return $this->hasMany(RiddleImage::class)->orderBy('display_order');
    }

    /** Indices progressifs (max 3) */
    public function hints(): HasMany
    {
        return $this->hasMany(Hint::class)->orderBy('index');
    }

    /** Scores liés à cette énigme */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }
}
