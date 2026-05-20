<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Services\Invitation\InvitationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    protected $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * Affiche le formulaire de création d'invitation (Admin).
     */
    public function create(City $city)
    {
        return Inertia::render('Admin/Invitations/Create', [
            'city' => $city
        ]);
    }

    /**
     * Stocke une nouvelle invitation.
     */
    public function store(Request $request, City $city)
    {
        $validated = $request->validate([
            'mode'             => 'required|in:collectif,mercenaire',
            'difficulty'       => 'required|in:facile,moyen,difficile',
            'locomotion'       => 'required|in:marche,velo,moto,voiture',
            'max_players'      => 'nullable|integer|min:1',
            'duration_minutes' => 'required|integer|min:30',
            'expires_in_hours' => 'nullable|integer|min:1',
        ]);

        $invitation = $this->invitationService->generate($city->id, $request->user(), $validated);

        return redirect()->route('admin.cities.show', $city)
            ->with('success', 'Invitation générée avec succès !');//
    }

    /**
     * Route d'entrée pour les joueurs via le lien d'invitation.
     */
    public function join(string $token)
    {
        $invitation = $this->invitationService->validateToken($token);

        if (!$invitation) {
            return Inertia::render('Game/Error', [
                'message' => 'L\'invitation est invalide ou a expiré.'
            ]);
        }

        return Inertia::render('Game/Join', [
            'invitation' => $invitation->load('city')
        ]);
    }
}
