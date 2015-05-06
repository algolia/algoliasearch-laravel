<?php namespace Algolia\AlgoliasearchLaravel;

use Illuminate\Support\ServiceProvider;
use Vinkla\Algolia\AlgoliaManager;
use Vinkla\Algolia\Factories\AlgoliaFactory;

class AlgoliaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->setupConfig();
    }

    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/algolia.php');

        $this->publishes([$source => config_path('algolia.php')]);

        $this->mergeConfigFrom($source, 'algolia');
    }

    public function register()
    {
        $this->registerVinklaExtension();

        \Event::subscribe('\Algolia\AlgoliasearchLaravel\EloquentSuscriber');
    }

    private function registerVinklaExtension()
    {
        $this->app->singleton('algolia.factory', function () {
            return new AlgoliaFactory();
        });

        $this->app->alias('algolia.factory', 'Vinkla\Algolia\Factories\AlgoliaFactory');

        $this->app->singleton('algolia', function ($app) {
            $config = $app['config'];
            $factory = $app['algolia.factory'];

            return new AlgoliaManager($config, $factory);
        });

        $this->app->alias('algolia', 'Vinkla\Algolia\AlgoliaManager');
    }

    public function provides()
    {
        return [
            'algolia',
            'algolia.factory'
        ];
    }
}