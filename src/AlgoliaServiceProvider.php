<?php

namespace AlgoliaSearch\Laravel;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AlgoliaServiceProvider extends ServiceProvider
{
    public function register()
    {
        Event::subscribe('\AlgoliaSearch\Laravel\EloquentSubscriber');
    }
}