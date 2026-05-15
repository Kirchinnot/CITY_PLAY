<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'two_factor_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'two_factor_enabled' => 'boolean',
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

    /** Villes/aventures créées par cet admin */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'created_by');
    }

    /** Invitations créées par cet admin */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'created_by');
    }

    /** Sessions de jeu où l'utilisateur est hôte principal */
    public function hostedSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'host_user_id');
    }

    /** Sessions auxquelles l'utilisateur participe (via game_players) */
    public function gameSessions(): BelongsToMany
    {
        return $this->belongsToMany(GameSession::class, 'game_players', 'user_id', 'game_session_id')
                    ->withPivot(['joined_at', 'current_riddle_id', 'last_lat', 'last_lng', 'last_seen_at', 'is_active'])
                    ->using(GamePlayer::class);
    }

    /** Scores individuels (mode mercenaire) */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /** Badges débloqués */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }
}
