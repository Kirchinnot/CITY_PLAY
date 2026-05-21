<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'accept_cgu' => 'required|accepted',
        ]);

        $user = User::create([
            'name'                       => $request->name,
            'email'                      => $request->email,
            'password'                   => Hash::make($request->password),
            'two_factor_enabled'         => true, // Obligatoire pour tous les joueurs
            'cgu_accepted_at'            => now(),
            'privacy_policy_accepted_at' => now(),
        ]);

        event(new Registered($user));

        // Pas d'autologin : le joueur doit se connecter et recevoir son code 2FA par email
        return redirect()->route('login')->with(
            'status',
            'Profil créé ! Connectez-vous pour recevoir votre code de vérification par email.'
        );
    }
}
