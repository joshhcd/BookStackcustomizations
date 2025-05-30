<?php
namespace BookStack\Likeable;

use Illuminate\Support\ServiceProvider;

class LikeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'likeable');
        // Public assets
        $this->publishes([
            __DIR__.'/../public/js' => public_path('vendor/likeable/js'),
        ], 'likeable-assets');
        // Routes
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}