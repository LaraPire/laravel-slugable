<?php

namespace Rayiumir\Slugable\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Rayiumir\Slugable\Slugable;

class SlugableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('Slugable', function() {
            return new Slugable();
        });
    }
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../Traits' => app_path('Traits/')
        ],'LaravelSlugableTraits');
    }
}
