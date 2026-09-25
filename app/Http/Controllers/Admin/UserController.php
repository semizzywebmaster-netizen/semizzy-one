<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('slug', $request->role));
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::ordered()->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'status' => 'required|in:active,inactive,suspended',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,slug',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'timezone' => 'Africa/Lagos',
            'locale' => 'en',
        ]);

        $user->syncRoles($request->roles);

        AuditService::userCreated($user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('roles.permissions', 'auditLogs');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::ordered()->get();
        $userRoles = $user->roles->pluck('slug')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'status' => 'required|in:active,inactive,suspended',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,slug',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
        ]);

        $user->syncRoles($request->roles);

        AuditService::userUpdated($user->id);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $userId = $user->id;
        $user->delete();

        AuditService::userDeleted($userId);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}