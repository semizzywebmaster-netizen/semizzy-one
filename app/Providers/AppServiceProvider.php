<?php

namespace App\Providers;

use App\Models\Addon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerAddonProviders();
    }

    /**
     * Discover and register service providers for active addons.
     *
     * Addon classes live under the Addons\ namespace in addons/<slug>/src/,
     * which is not part of the Composer autoloader (users cannot be expected
     * to run `composer dump-autoload` after installing an addon on shared
     * hosting). The provider file is therefore required explicitly before
     * registration.
     */
    private function registerAddonProviders(): void
    {
        // Only attempt addon discovery if the database is available.
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return;
        }

        try {
            $addonsPath = base_path('addons');

            if (!File::isDirectory($addonsPath)) {
                return;
            }

            $activeAddons = Addon::where('status', 'active')->pluck('slug')->toArray();

            if (empty($activeAddons)) {
                return;
            }

            foreach ($activeAddons as $slug) {
                $srcDir = "{$addonsPath}/{$slug}/src";

                if (!File::isDirectory($srcDir)) {
                    continue;
                }

                foreach (File::files($srcDir) as $file) {
                    $filename = $file->getFilename();

                    if (!str_ends_with($filename, '.php') || !str_contains($filename, 'ServiceProvider')) {
                        continue;
                    }

                    $className = 'Addons\\' . Str::studly($slug) . '\\' . $file->getFilenameWithoutExtension();

                    // Explicitly load the file — the Addons\ namespace is not
                    // in the Composer autoload map.
                    if (!class_exists($className)) {
                        require_once $file->getPathname();
                    }

                    if (class_exists($className)) {
                        $this->app->register($className);
                    }
                }
            }
        } catch (\Exception $e) {
            // Never let a broken addon take down the whole application.
            report($e);
        }
    }
}
