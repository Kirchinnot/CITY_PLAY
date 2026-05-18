<?php

namespace App\Events;

use App\Models\Score;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiddleResolvedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $score;

    /**
     * Create a new event instance.
     */
    public function __construct(Score $score)
    {
        $this->score = $score;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('session.' . $this->score->game_session_id),
        ];

        // En mode mercenaire, on broadcast aussi sur le canal du joueur
        // En mode collectif, user_id est null donc on n'ajoute pas ce canal
        if ($this->score->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->score->user_id);
        }

        return $channels;
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'score' => $this->score->load(['riddle', 'user']),
            'message' => 'Énigme résolue !',
        ];
    }
}
