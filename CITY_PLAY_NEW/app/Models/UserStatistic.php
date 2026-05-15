<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_games',
        'total_score',
        'total_riddles_solved',
        'total_riddles_failed',
        'total_distance_walked',
        'average_response_time',
        'success_rate',
    ];

    protected function casts(): array
    {
        return [
            'total_games'           => 'integer',
            'total_score'           => 'integer',
            'total_riddles_solved'  => 'integer',
            'total_riddles_failed'  => 'integer',
            'total_distance_walked' => 'float',
            'average_response_time' => 'float',
            'success_rate'          => 'float',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
