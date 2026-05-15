<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'image'])]
class City extends Model
{
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }
}
