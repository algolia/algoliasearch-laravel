<?php namespace Algolia\AlgoliasearchLaravel;

use Illuminate\Support\ServiceProvider;

class AlgoliaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->setupConfig();
    }

    protected function setupConfig()
    {
     /*   $source = realpath(__DIR__.'/../config/algolia.php');

        $this->publishes([$source => config_path('algolia.php')]);

        $this->mergeConfigFrom($source, 'algolia');*/
    }

    public function register()
    {
        $this->app->singleton('algolia', function ($app) {
            $config = $app['config'];
            $factory = new AlgoliaClientFactory();

            return new AlgoliaManager($config, $factory);
        });

        $this->app->alias('algolia', '\Algolia\AlgoliasearchLaravel\AlgoliaManager');

        \Event::subscribe('\Algolia\AlgoliasearchLaravel\EloquentSuscriber');
    }

    public function provides()
    {
        return [
            'algolia'
        ];
    }
}