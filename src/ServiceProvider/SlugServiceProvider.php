<?php

namespace Rayiumir\HasSlug\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Rayiumir\HasSlug\HasSlug;

class SlugServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('HasSlug', function() {
            return new HasSlug();
        });
    }
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->_loadPublished();
    }

    private function _loadPublished(): void
    {
        $this->publishes([
            __DIR__.'/../Traits' => app_path('Traits/')
        ],'LaravelHasSlug');
    }
}
