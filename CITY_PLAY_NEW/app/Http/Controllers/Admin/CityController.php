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
        $cities = City::withCount(['places', 'gameSessions'])->get(); //récupères toutes les villes de la table cities
        return Inertia::render('Admin/Cities', [
            'cities' => $cities
        ]); //rend la vue Inertia située dans resources/js/Pages/Admin/Cities.vue et lui passe les données des villes
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
        $placesWithoutRiddles = $city->places()->whereDoesntHave('riddles')->count(); //Compter tous les lieux (places) de cette ville (city) qui n’ont aucune énigme (riddles)
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
            'retention_days' => 'required|integer|min:1',
            'outro_config' => 'nullable|array',
        ]);

        $validated['created_by'] = $request->user()->id; // Associer le parcours à l'admin qui le crée
        $validated['is_published'] = false; //Ce contenu n’est pas encore publié/visible aux joueurs

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
            'retention_days' => 'required|integer|min:1',
            'outro_config' => 'nullable|array',
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
