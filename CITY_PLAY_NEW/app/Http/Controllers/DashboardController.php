<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'player' => [
                'name' => auth()->user()->name,
                'score' => 1250,
                'badges' => [
                    ['id' => 1, 'name' => 'Explorateur Urbain', 'icon' => '🏙️'],
                    ['id' => 2, 'name' => 'Maître des Énigmes', 'icon' => '🧩'],
                    ['id' => 3, 'name' => 'Rapide comme l\'éclair', 'icon' => '⚡'],
                ],
                'avatar' => auth()->user()->avatar ?? 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . auth()->user()->name,
            ],
            'activeSession' => [
                'city' => 'Paris',
                'progress' => 65,
                'timeElapsed' => '1h 20m',
                'mode' => 'Mercenaire',
                'difficulty' => 'Difficile',
            ],
            'stats' => [
                'enigmasSolved' => 42,
                'citiesExplored' => 5,
                'rank' => 'Bronze III',
                'totalPoints' => 12500,
            ],
        ]);
    }
}
