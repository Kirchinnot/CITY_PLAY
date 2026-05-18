<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityController extends Controller
{
    /**
     * Liste des villes pour le dashboard admin.
     */
    public function index()
    {
        $cities = City::withCount(['places', 'gameSessions'])->get();
        return Inertia::render('Admin/Cities', [
            'cities' => $cities
        ]);
    }

    /**
     * Publie un parcours après vérification des règles métier.
     * Règle 1 : Au moins 2 lieux.
     * Règle 2 : Chaque lieu doit avoir au moins une énigme configurée.
     */
    public function publish(City $city)
    {
        // Vérification Règle 1
        if ($city->places()->count() < 2) {
            return redirect()->back()->withErrors([
                'publish' => "Un parcours doit avoir au moins 2 lieux pour être publié."
            ]);
        }

        // Vérification Règle 2
        $placesWithoutRiddles = $city->places()->whereDoesntHave('riddles')->count();
        if ($placesWithoutRiddles > 0) {
            return redirect()->back()->withErrors([
                'publish' => "Tous les lieux doivent avoir au moins une énigme configurée."
            ]);
        }

        $city->update(['is_published' => true]);

        return redirect()->back()->with('success', "Le parcours '{$city->name}' est maintenant public !");
    }

    /**
     * Dépublie un parcours.
     */
    public function unpublish(City $city)
    {
        $city->update(['is_published' => false]);
        return redirect()->back()->with('success', "Le parcours a été retiré de la publication.");
    }

    /**
     * Enregistre un nouveau parcours.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string|max:500',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['is_published'] = false;

        City::create($validated);

        return redirect()->back()->with('success', 'Parcours créé avec succès.');
    }

    /**
     * Met à jour un parcours existant.
     */
    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'required|string|max:500',
        ]);

        $city->update($validated);

        return redirect()->back()->with('success', 'Parcours mis à jour avec succès.');
    }

    /**
     * Supprime un parcours.
     */
    public function destroy(City $city)
    {
        $city->delete();

        return redirect()->back()->with('success', 'Parcours supprimé avec succès.');
    }
}
