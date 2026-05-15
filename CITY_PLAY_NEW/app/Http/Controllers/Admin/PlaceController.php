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
        $places = Place::with('city')
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'validation_radius' => 'required|integer|min:1',
            'order_index' => 'required|integer|min:1',
        ]);

        Place::create($validated);

        return redirect()->back()->with('success', 'Lieu ajouté avec succès.');
    }
}
