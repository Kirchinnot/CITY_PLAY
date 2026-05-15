<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'members_numbers',
    ];

    protected function casts(): array
    {
        return [
            'members_numbers' => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /** Propriétaire de l'équipe */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Membres de l'équipe (pivot) */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members', 'team_id', 'user_id')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    /** Sessions de jeu de l'équipe */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }
}
