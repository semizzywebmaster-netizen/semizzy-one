<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'remember' => 'boolean',
        ]);

        $throttleKey = 'login|' . $request->ip() . '|' . $request->email;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditService::login($request->email, false, 'rate_limited');

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 900);
            AuditService::login($request->email, false, 'invalid_credentials');

            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if ($user->status !== 'active') {
            Auth::logout();
            RateLimiter::hit($throttleKey, 900);
            AuditService::login($request->email, false, 'account_' . $user->status);

            throw ValidationException::withMessages([
                'email' => 'Your account is currently ' . $user->status . '. Please contact support.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Update login metadata
        $user->update([
            'last_login_ip' => $request->ip(),
            'last_login_at' => now(),
        ]);

        $request->session()->regenerate();
        AuditService::login($request->email, true);

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        AuditService::logout($user?->id);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}