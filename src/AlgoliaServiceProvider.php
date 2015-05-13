<?php namespace Algolia\AlgoliasearchLaravel;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AlgoliaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerManager();

        Event::subscribe('\Algolia\AlgoliasearchLaravel\EloquentSuscriber');
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
