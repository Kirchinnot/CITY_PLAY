<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'two_factor_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'two_factor_enabled'   => 'boolean',
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /** Équipes dont l'utilisateur est propriétaire */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /** Équipes dont l'utilisateur est membre */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    /** Environnements créés par cet admin */
    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class, 'created_by');
    }

    /** Sessions de jeu */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    /** Statistiques agrégées */
    public function statistic(): HasOne
    {
        return $this->hasOne(UserStatistic::class);
    }
}
