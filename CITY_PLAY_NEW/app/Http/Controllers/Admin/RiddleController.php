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
        $place->load(['riddles.hints', 'riddles.images']);
        
        return Inertia::render('Admin/Riddles', [
            'place' => $place,
            'riddles' => $place->riddles
        ]);
    }

    /**
     * Sauvegarde ou met à jour les 4 énigmes d'un lieu d'un coup (formulaire dynamique).
     */
    public function store(Request $request, Place $place, \App\Services\ImageUploadService $uploader)
    {
        $validated = $request->validate([
            'riddles' => 'required|array',
            'riddles.*.difficulty' => 'required|in:enfant,facile,moyen,difficile',
            'riddles.*.title' => 'nullable|string|max:150',
            'riddles.*.question' => 'nullable|string',
            'riddles.*.options' => 'nullable|array',
            'riddles.*.answer' => 'nullable|string',
            'riddles.*.points_base' => 'nullable|integer|min:0',
            'riddles.*.time_limit_seconds' => 'nullable|integer|min:30',
            'riddles.*.images' => 'nullable|array|max:4',
            'riddles.*.images.*' => 'nullable|image|mimes:jpeg,png|max:2048',
            'riddles.*.hints' => 'nullable|array|max:3',
            'riddles.*.hints.*.content' => 'required|string',
            'riddles.*.hints.*.points_penalty' => 'required|integer|min:0',
        ]);

        foreach ($validated['riddles'] as $index => $riddleData) {
            // On ne sauvegarde que si au moins la question est remplie
            if (empty($riddleData['question'])) {
                continue;
            }

            $riddle = Riddle::updateOrCreate(
                [
                    'place_id' => $place->id,
                    'difficulty' => $riddleData['difficulty']
                ],
                [
                    'title' => $riddleData['title'] ?? ucfirst($riddleData['difficulty']),
                    'question' => $riddleData['question'],
                    'options' => collect($riddleData['options'] ?? [])->filter()->values()->all(),
                    'answer' => $riddleData['answer'] ?? '',
                    'points_base' => $riddleData['points_base'] ?? 100,
                    'time_limit_seconds' => $riddleData['time_limit_seconds'] ?? 300,
                ]
            );

            // Gestion des images (jusqu'à 4)
            if ($request->hasFile("riddles.{$index}.images")) {
                foreach ($request->file("riddles.{$index}.images") as $imgIndex => $image) {
                    if ($image) {
                        $url = $uploader->upload($image, 'riddles');
                        $riddle->images()->updateOrCreate(
                            ['display_order' => $imgIndex + 1],
                            ['image_url' => $url]
                        );
                    }
                }
            }

            // Gestion des indices (Hints)
            if (isset($riddleData['hints'])) {
                $riddle->hints()->delete(); 
                foreach ($riddleData['hints'] as $hIndex => $hintData) {
                    $riddle->hints()->create([
                        'index' => $hIndex + 1,
                        'content' => $hintData['content'],
                        'points_penalty' => $hintData['points_penalty'],
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Énigmes, images et indices mis à jour.');
    }
}
