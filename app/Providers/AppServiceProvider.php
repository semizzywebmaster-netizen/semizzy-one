<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

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

    private function registerAddonProviders(): void
    {
        // Only attempt addon discovery if the database is available
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            return;
        }

        try {
            $addonsPath = base_path('addons');

            if (!File::isDirectory($addonsPath)) {
                return;
            }

            $activeAddons = \App\Models\Addon::where('status', 'active')->pluck('slug')->toArray();

            if (empty($activeAddons)) {
                return;
            }

            foreach ($activeAddons as $slug) {
                $dir = "{$addonsPath}/{$slug}";
                $srcDir = "{$dir}/src";

                if (!File::isDirectory($srcDir)) {
                    continue;
                }

                $files = File::files($srcDir);

                foreach ($files as $file) {
                    if (str_contains($file->getFilename(), 'ServiceProvider')) {
                        $className = 'Addons\\' . studly_case($slug) . '\\' . $file->getFilenameWithoutExtension();
                        if (class_exists($className)) {
                            $this->app->register($className);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail — don't break the app
            report($e);
        }
    }
}