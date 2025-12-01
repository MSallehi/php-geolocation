<?php

/**
 * Laravel Integration for GeoLocation Package
 * 
 * This file is optional and only needed for Laravel projects.
 * Copy this file to your Laravel project if needed.
 */

namespace MSallehi\GeoLocation\Laravel;

use Illuminate\Support\ServiceProvider;
use MSallehi\GeoLocation\GeoLocation;

class GeoLocationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/geolocation.php',
            'geolocation'
        );

        // Register the main class
        $this->app->singleton('geolocation', function ($app) {
            return new GeoLocation($app['config']['geolocation'] ?? []);
        });

        // Register alias
        $this->app->alias('geolocation', GeoLocation::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../../config/geolocation.php' => config_path('geolocation.php'),
        ], 'geolocation-config');

        // Register middleware alias
        $router = $this->app['router'];
        $router->aliasMiddleware('geolocation', GeoLocationMiddleware::class);
    }
}
