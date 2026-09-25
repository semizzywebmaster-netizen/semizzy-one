<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = Provider::query();

        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        $providers = $query->orderBy('type')->orderBy('priority', 'desc')->paginate(20);

        return view('admin.providers.index', compact('providers'));
    }

    public function create()
    {
        return view('admin.providers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:providers,slug|alpha_dash',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'priority' => 'required|integer|min:0',
            'timeout' => 'required|integer|min:5|max:120',
            'max_retries' => 'required|integer|min:0|max:10',
        ]);

        Provider::create($request->only([
            'name', 'slug', 'type', 'description', 'priority', 'timeout', 'max_retries',
        ]));

        return redirect()->route('admin.providers.index')
            ->with('success', 'Provider created successfully.');
    }

    public function show(Provider $provider)
    {
        $provider->load('healthLogs');

        return view('admin.providers.show', compact('provider'));
    }

    public function edit(Provider $provider)
    {
        return view('admin.providers.edit', compact('provider'));
    }

    public function update(Request $request, Provider $provider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,error',
            'priority' => 'required|integer|min:0',
            'timeout' => 'required|integer|min:5|max:120',
            'max_retries' => 'required|integer|min:0|max:10',
        ]);

        $provider->update($request->only([
            'name', 'description', 'status', 'priority', 'timeout', 'max_retries',
        ]));

        return redirect()->route('admin.providers.show', $provider)
            ->with('success', 'Provider updated successfully.');
    }

    public function destroy(Provider $provider)
    {
        $provider->delete();

        return redirect()->route('admin.providers.index')
            ->with('success', 'Provider deleted.');
    }
}