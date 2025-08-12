<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class BladeComponentsProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register map component
        Blade::component('components.map', 'osm-map');

        // Register UI components
        Blade::component('components.floating-button', 'floating-btn');
        Blade::component('components.map-card', 'map-card');
        Blade::component('components.image-slideshow', 'image-slideshow');
    }
}
