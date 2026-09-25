<?php

namespace Addons\ExampleAddon;

use Illuminate\Support\ServiceProvider;

class ExampleAddonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register views
        $this->loadViewsFrom(__DIR__ . '/../resources', 'example-addon');

        // Register routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }

    public function register(): void
    {
        //
    }
}