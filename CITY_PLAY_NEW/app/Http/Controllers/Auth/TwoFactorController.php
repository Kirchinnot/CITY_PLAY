<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TwoFactorCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    public function index()
    {
        if (! session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactor');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $pendingId = session('2fa:user:id');
        if (! $pendingId) {
            return redirect()->route('login');
        }

        $user = User::find($pendingId);
        if (! $user) {
            session()->forget(['2fa:user:id','2fa:remember']);
            return redirect()->route('login');
        }

        if (! $user->two_factor_code || ! $user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
            return redirect()->back()->withErrors(['code' => 'Le code a expiré. Demande un nouveau code.']);
        }

        if (! hash_equals($user->two_factor_code, $request->code)) {
            return redirect()->back()->withErrors(['code' => 'Code invalide']);
        }

        // Clear 2fa code and log in
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        auth()->loginUsingId($user->id, session('2fa:remember', false));
        session()->forget(['2fa:user:id','2fa:remember']);
        session()->regenerate();

        return redirect()->intended(route('player.dashboard', absolute: false));
    }

    public function resend(Request $request)
    {
        $pendingId = session('2fa:user:id');
        if (! $pendingId) {
            return redirect()->route('login');
        }

        $user = User::find($pendingId);
        if (! $user) {
            session()->forget(['2fa:user:id','2fa:remember']);
            return redirect()->route('login');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            $user->notify(new TwoFactorCode($code));
        } catch (\Throwable $e) {
            Log::error('2FA resend failed: '.$e->getMessage());
        }

        return redirect()->back()->with('status', 'Nouveau code envoyé.');
    }
}
