<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AddonController extends Controller
{
    public function index()
    {
        $installed = Addon::ordered()->get();
        $available = $this->discoverAvailable();

        return view('admin.addons.index', compact('installed', 'available'));
    }

    public function show(Addon $addon)
    {
        return view('admin.addons.show', compact('addon'));
    }

    public function install(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
        ]);

        $slug = $request->slug;
        $manifestPath = base_path("addons/{$slug}/addon.json");

        if (!File::exists($manifestPath)) {
            return back()->with('error', 'Addon manifest not found.');
        }

        $manifest = json_decode(File::get($manifestPath), true);

        if (!$this->validateManifest($manifest)) {
            return back()->with('error', 'Invalid addon manifest.');
        }

        $addon = Addon::create([
            'name' => $manifest['name'],
            'slug' => $slug,
            'description' => $manifest['description'] ?? null,
            'version' => $manifest['version'],
            'author' => $manifest['author'] ?? null,
            'author_email' => $manifest['author_email'] ?? null,
            'homepage' => $manifest['homepage'] ?? null,
            'license' => $manifest['license'] ?? 'proprietary',
            'requires' => $manifest['requires'] ?? null,
            'permissions' => $manifest['permissions'] ?? null,
            'settings' => $manifest['settings'] ?? null,
            'status' => 'installed',
            'installed_at' => now(),
        ]);

        AuditService::addonInstalled($slug);

        return back()->with('success', "Addon '{$manifest['name']}' installed successfully.");
    }

    public function activate(Addon $addon)
    {
        if ($addon->status !== 'installed' && $addon->status !== 'inactive') {
            return back()->with('error', 'Addon cannot be activated from its current state.');
        }

        $addon->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);

        AuditService::addonActivated($addon->slug);

        return back()->with('success', "Addon '{$addon->name}' activated.");
    }

    public function deactivate(Addon $addon)
    {
        if ($addon->status !== 'active') {
            return back()->with('error', 'Addon is not active.');
        }

        $addon->update(['status' => 'inactive']);

        AuditService::addonDeactivated($addon->slug);

        return back()->with('success', "Addon '{$addon->name}' deactivated.");
    }

    public function uninstall(Addon $addon)
    {
        if ($addon->status === 'active') {
            return back()->with('error', 'Deactivate the addon before uninstalling.');
        }

        $addon->update(['status' => 'uninstalled']);

        AuditService::addonUninstalled($addon->slug);

        return back()->with('success', "Addon '{$addon->name}' uninstalled.");
    }

    private function discoverAvailable(): array
    {
        $addonsPath = base_path('addons');

        if (!File::isDirectory($addonsPath)) {
            return [];
        }

        $available = [];
        $directories = File::directories($addonsPath);

        foreach ($directories as $dir) {
            $slug = basename($dir);
            $manifestPath = "{$dir}/addon.json";

            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);
                if ($this->validateManifest($manifest)) {
                    $manifest['slug'] = $slug;
                    $manifest['is_installed'] = Addon::where('slug', $slug)
                        ->whereIn('status', ['installed', 'active', 'inactive'])
                        ->exists();
                    $available[] = $manifest;
                }
            }
        }

        return $available;
    }

    private function validateManifest(?array $manifest): bool
    {
        if (!$manifest) return false;

        return isset($manifest['name'], $manifest['version']);
    }
}