<?php

namespace AlgoliaSearch\Laravel;

use AlgoliaSearch\Version;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AlgoliaServiceProvider extends ServiceProvider
{
    public function register()
    {
        Version::addPrefixUserAgentSegment('Laravel integration', '1.7.1');
        Version::addSuffixUserAgentSegment('PHP', phpversion());
        $laravel = app();
        Version::addSuffixUserAgentSegment('Laravel', $laravel::VERSION);

        $this->registerManager();

        Event::subscribe('\AlgoliaSearch\Laravel\EloquentSubscriber');
    }

    private function registerManager()
    {
        $this->app->register('Vinkla\Algolia\AlgoliaServiceProvider');
    }
}
