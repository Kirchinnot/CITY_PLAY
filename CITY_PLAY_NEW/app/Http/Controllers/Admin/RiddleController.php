<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\Riddle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiddleController extends Controller
{
    /**
     * Affiche la liste des énigmes pour un lieu spécifique.
     */
    public function index(Place $place)
    {
        $place->load('riddles.hints');
        
        return Inertia::render('Admin/Riddles', [
            'place' => $place,
            'riddles' => $place->riddles
        ]);
    }

    /**
     * Sauvegarde ou met à jour les 4 énigmes d'un lieu d'un coup (formulaire dynamique).
     */
    public function store(Request $request, Place $place)
    {
        $validated = $request->validate([
            'riddles' => 'required|array',
            'riddles.*.difficulty' => 'required|in:enfant,facile,moyen,difficile',
            'riddles.*.title' => 'nullable|string|max:150',
            'riddles.*.question' => 'required|string',
            'riddles.*.options' => 'required|array|min:2',
            'riddles.*.answer' => 'required|string',
            'riddles.*.points_base' => 'required|integer|min:0',
            'riddles.*.time_limit_seconds' => 'required|integer|min:30',
        ]);

        foreach ($validated['riddles'] as $riddleData) {
            Riddle::updateOrCreate(
                [
                    'place_id' => $place->id,
                    'difficulty' => $riddleData['difficulty']
                ],
                [
                    'title' => $riddleData['title'],
                    'question' => $riddleData['question'],
                    'options' => $riddleData['options'],
                    'answer' => $riddleData['answer'],
                    'points_base' => $riddleData['points_base'],
                    'time_limit_seconds' => $riddleData['time_limit_seconds'],
                ]
            );
        }

        return redirect()->back()->with('success', 'Énigmes mises à jour avec succès.');
    }
}
