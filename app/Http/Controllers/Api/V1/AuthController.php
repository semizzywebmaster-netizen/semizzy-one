<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = 'api_login|' . $request->ip() . '|' . $request->email;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return $this->error('Too many login attempts.', 429);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 900);
            AuditService::login($request->email, false, 'invalid_credentials');
            return $this->error('Invalid credentials.', 401);
        }

        if ($user->status !== 'active') {
            AuditService::login($request->email, false, 'account_' . $user->status);
            return $this->error('Account is ' . $user->status . '.', 403);
        }

        RateLimiter::clear($throttleKey);

        $token = $user->createToken('api-token')->plainTextToken;

        $user->update([
            'last_login_ip' => $request->ip(),
            'last_login_at' => now(),
        ]);

        AuditService::login($request->email, true);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->roles->pluck('slug'),
                'permissions' => $user->getAllPermissions(),
            ],
            'token' => $token,
        ], 'Login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        AuditService::logout($request->user()?->id);

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'status' => $user->status,
            'timezone' => $user->timezone,
            'locale' => $user->locale,
            'roles' => $user->roles->pluck('slug'),
            'permissions' => $user->getAllPermissions(),
            'two_factor_enabled' => $user->two_factor_enabled,
            'created_at' => $user->created_at,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'timezone' => 'sometimes|string|max:64',
            'locale' => 'sometimes|string|max:10',
        ]);

        $user->update($request->only(['name', 'phone', 'timezone', 'locale']));

        return $this->success($user->fresh(), 'Profile updated.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect.', 422);
        }

        $user->update(['password' => Hash::make($request->password)]);
        AuditService::passwordChanged($user->id);

        // Revoke all other tokens
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return $this->success(null, 'Password changed successfully.');
    }
}