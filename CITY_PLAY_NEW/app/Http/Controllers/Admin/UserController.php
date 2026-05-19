<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\GameSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Affiche la liste des joueurs et des équipes (sessions).
     */
    public function index()
    {
        $players = User::where('role', 'player')
            ->withCount(['gameSessions', 'achievements'])
            ->with(['hostedSessions' => function($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->get();

        $teams = GameSession::with(['city', 'host', 'players'])
            ->withCount('players')
            ->latest()
            ->get()
            ->map(function($session) {
                $session->total_score = $session->scores()->sum('points_earned');
                return $session;
            });

        return Inertia::render('Admin/Users', [
            'players' => $players,
            'teams' => $teams,
        ]);
    }

    /**
     * Supprimer un joueur.
     */
    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Impossible de supprimer un administrateur.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Joueur supprimé avec succès.');
    }
}
