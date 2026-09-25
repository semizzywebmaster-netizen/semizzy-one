<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'timezone' => 'Africa/Lagos',
            'locale' => 'en',
        ]);

        // Assign default user role
        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $user->assignRole('user');
        }

        Auth::login($user);
        AuditService::userCreated($user->id);

        return redirect('/dashboard');
    }
}