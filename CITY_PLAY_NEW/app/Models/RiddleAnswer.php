<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiddleAnswer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'riddle_id',
        'answer',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Vérifie si la réponse fournie par l'utilisateur est correcte.
     * La comparaison est insensible à la casse et ignore les espaces en tête/queue.
     */
    public function isCorrect(string $userAnswer): bool
    {
        return strtolower(trim($userAnswer)) === strtolower(trim($this->answer));
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function riddle(): BelongsTo
    {
        return $this->belongsTo(Riddle::class);
    }
}
