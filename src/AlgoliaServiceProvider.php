<?php namespace AlgoliaSearch\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AlgoliaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerManager();

        Event::subscribe('\AlgoliaSearch\Laravel\EloquentSuscriber');
    }

    private function registerManager()
    {
        $this->app->register('Vinkla\Algolia\AlgoliaServiceProvider');
    }

    public function provides()
    {
        return [
            'algolia',
            'algolia.factory'
        ];
    }
}