<?php

namespace App\Providers;

use App\Models\Addon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerAddonProviders();
    }

    public function boot(): void
    {
        //
    }

    /**
     * Auto-discover and register active addon service providers.
     */
    private function registerAddonProviders(): void
    {
        try {
            // Check if database is available
            if (!$this->app->runningInConsole() || $this->app->environment() !== 'testing') {
                // Only register addon providers if DB is ready
                try {
                    \DB::connection()->getPdo();
                } catch (\Exception $e) {
                    return;
                }
            }

            $addonsPath = base_path('addons');

            if (!File::isDirectory($addonsPath)) {
                return;
            }

            $directories = File::directories($addonsPath);

            foreach ($directories as $dir) {
                $slug = basename($dir);
                $manifestPath = "{$dir}/addon.json";

                if (!File::exists($manifestPath)) {
                    continue;
                }

                $manifest = json_decode(File::get($manifestPath), true);

                if (!$manifest || !isset($manifest['name'])) {
                    continue;
                }

                // Check if addon is active
                $addon = Addon::where('slug', $slug)->where('status', 'active')->first();

                if (!$addon) {
                    continue;
                }

                // Find and register service provider
                $providerClass = $this->resolveProviderClass($dir, $slug);

                if ($providerClass && class_exists($providerClass)) {
                    $this->app->register($providerClass);
                }
            }
        } catch (\Exception $e) {
            // Silently fail — don't break the app if addon registration fails
            report($e);
        }
    }

    private function resolveProviderClass(string $dir, string $slug): ?string
    {
        // Convention: look for src/{Name}AddonServiceProvider.php
        $srcDir = "{$dir}/src";

        if (!File::isDirectory($srcDir)) {
            return null;
        }

        $files = File::files($srcDir);

        foreach ($files as $file) {
            if (str_contains($file->getFilename(), 'ServiceProvider')) {
                $className = 'Addons\\' . studly_case($slug) . '\\' . $file->getFilenameWithoutExtension();
                return $className;
            }
        }

        return null;
    }
}