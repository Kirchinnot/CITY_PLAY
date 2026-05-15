<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\GameSession;
use App\Services\Session\GameSessionService;
use App\Services\Invitation\InvitationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameSessionController extends Controller
{
    protected $sessionService;
    protected $invitationService;

    public function __construct(GameSessionService $sessionService, InvitationService $invitationService)
    {
        $this->sessionService = $sessionService;
        $this->invitationService = $invitationService;
    }

    /**
     * Rejoint ou crée une session via une invitation.
     */
    public function join(string $token, Request $request)
    {
        $invitation = $this->invitationService->validateToken($token);

        if (!$invitation) {
            return redirect()->route('game.join', $token)
                ->with('error', 'Invitation invalide.');
        }

        $session = $this->sessionService->joinOrCreateSession($invitation, $request->user());

        return redirect()->route('game.lobby', $session->id);
    }

    /**
     * Affiche le lobby de la session.
     */
    public function lobby(GameSession $session)
    {
        return Inertia::render('Game/Lobby', [
            'session' => $session->load(['city', 'players', 'host']),
            'currentUser' => auth()->user()
        ]);
    }

    /**
     * Démarre la partie (Host seulement).
     */
    public function start(GameSession $session, Request $request)
    {
        if ($session->host_user_id !== $request->user()->id) {
            return back()->with('error', 'Seul l\'hôte peut démarrer la partie.');
        }

        if ($this->sessionService->startSession($session)) {
            // Ici on redirigera vers la carte (Dev 3)
            return redirect()->route('game.map', $session->id);
        }

        return back()->with('error', 'Impossible de démarrer la partie.');
    }
}
