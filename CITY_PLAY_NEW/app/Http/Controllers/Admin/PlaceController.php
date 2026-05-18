<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlaceController extends Controller
{
    /**
     * Liste les lieux (éventuellement filtrés par ville).
     */
    public function index(Request $request)
    {
        $cities = City::all();
        $places = Place::with(['city', 'images'])
            ->when($request->city_id, fn($q) => $q->where('city_id', $request->city_id))
            ->orderBy('city_id')
            ->orderBy('order_index')
            ->get();

        return Inertia::render('Admin/Places', [
            'places' => $places,
            'cities' => $cities,
            'filters' => $request->only(['city_id'])
        ]);
    }

    /**
     * Enregistre un nouveau lieu.
     */
    public function store(Request $request, \App\Services\ImageUploadService $uploader)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'validation_radius' => 'required|integer|min:1',
            'order_index' => 'required|integer|min:1',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png|max:2048',
        ]);

        $place = Place::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $url = $uploader->upload($image, 'places');
                $place->images()->create([
                    'image_url' => $url,
                    'display_order' => $index + 1,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Lieu ajouté avec succès.');
    }
    /**
     * Supprime une image d'un lieu.
     */
    public function destroyImage(\App\Models\PlaceImage $image, \App\Services\ImageUploadService $uploader)
    {
        $uploader->delete($image->image_url);
        $image->delete();

        return redirect()->back()->with('success', 'Image supprimée.');
    }

    /**
     * Supprime un lieu et ses ressources associées (images, énigmes).
     */
    public function destroy(Place $place, \App\Services\ImageUploadService $uploader)
    {
        // 1. Supprimer les images physiques du lieu
        foreach ($place->images as $img) {
            $uploader->delete($img->image_url);
        }

        // 2. Supprimer les images physiques des énigmes du lieu
        $place->load('riddles.images');
        foreach ($place->riddles as $riddle) {
            foreach ($riddle->images as $rimg) {
                $uploader->delete($rimg->image_url);
            }
        }

        // 3. Nettoyage des données associées en cascade
        foreach ($place->riddles as $riddle) {
            $riddle->hints()->delete();
            $riddle->images()->delete();
            $riddle->delete();
        }
        $place->images()->delete();
        $place->delete();

        return redirect()->back()->with('success', 'Lieu supprimé avec succès.');
    }
}
