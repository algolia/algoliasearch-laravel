# Algolia Search API Client for Laravel

[Algolia Search](https://www.algolia.com) is a hosted full-text, numerical, and faceted search engine capable of delivering realtime results from the first keystroke.

[![Build Status](https://img.shields.io/travis/algolia/algoliasearch-laravel/master.svg?style=flat)](https://travis-ci.org/algolia/algoliasearch-laravel)
[![Latest Version](https://img.shields.io/github/release/algolia/algoliasearch-laravel.svg?style=flat)](https://github.com/algolia/algoliasearch-laravel/releases)
[![License](https://img.shields.io/packagist/l/algolia/algoliasearch-laravel.svg?style=flat)](https://packagist.org/packages/algolia/algoliasearch-laravel)


This PHP package integrates the Algolia Search API to the Laravel Eloquent ORM. It's based on the [algoliasearch-client-php](https://github.com/algolia/algoliasearch-client-php) package.

**Note:** If you're using Laravel 4, checkout the [algoliasearch-laravel-4](https://github.com/algolia/algoliasearch-laravel-4) repository.




## API Documentation

You can find the full reference on [Algolia's website](https://www.algolia.com/doc/api-client/laravel/).


## Table of Contents


1. **[Algolia and Laravel Scout](#algolia-and-laravel-scout)**

    * [Introducing Laravel Scout](#introducing-laravel-scout)
    * [Regarding documentation](#regarding-documentation)
    * [The client side of Laravel](#the-client-side-of-laravel)
    * [Looking for the legacy package?](#looking-for-the-legacy-package?)

1. **[Install](#install)**

    * [Install the algolia/scout package](#install-the-algoliascout-package)
    * [Enabling scout](#enabling-scout)
    * [Configuring scout](#configuring-scout)
    * [Configure API Keys](#configure-api-keys)

1. **[Indexing](#indexing)**

    * [Indexing](#indexing)
    * [Manual indexing](#manual-indexing)
    * [Customizing records](#customizing-records)

1. **[Options](#options)**

    * [Custom index name](#custom-index-name)
    * [Custom `objectID`](#custom-objectid)
    * [Pause indexing](#pause-indexing)

1. **[Relationships](#relationships)**

    * [Relationships](#relationships)

1. **[Managing Settings](#managing-settings)**

    * [Settings](#settings)

1. **[Managing Replicas](#managing-replicas)**

    * [Settings replicas](#settings-replicas)
    * [Using replicas](#using-replicas)

1. **[Eloquent compatibility](#eloquent-compatibility)**

    * [Eloquent compatibility](#eloquent-compatibility)

1. **[Extending Laravel Scout](#extending-laravel-scout)**

    * [Introduction](#introduction)
    * [Using the search callback](#using-the-search-callback)
    * [Using macros](#using-macros)
    * [Extending Algolia’s driver](#extending-algolia’s-driver)





# Algolia and Laravel Scout



## Regarding documentation

Laravel has written [excellent online documentation](https://laravel.com/docs/5.4/scout)
to go along with its release of Scout. They go into great detail on how to use the
Scout interface. Additionally, there are [Laracasts](https://laracasts.com/series/search-as-a-service)
that can help you get started with Scout and Algolia. These resources should be
sufficient not only to help you get started, but to implement Algolia in the best
possible way within the Laravel framework.

One of our goals here is to complement Laravel's documentation by anticipating
questions you might have after reading Laravel's documentation. We touch on some
of the same material, adding our point of view to such subjects as
[installation](/doc/api-client/laravel/install/), [managing indices](/doc/api-client/laravel/indexing/),
and others.

Of equal interest, we show you how to [extend Scout](/doc/api-client/laravel/extending-scout/)
with callbacks and extended devices, and how to use the [Macros](#using-macros)
we have written to simplify your code. We also provide [tutorials](/doc/tutorials/getting-started/getting-started-with-laravel-scout-vuejs/) that showcase
special use cases, like geo-awareness.

## The client side of Laravel

Putting aside data indexing operations, which are done on your servers, we recommend
that your search operations be done directly on the client-side, using JavaScript.
This will significantly improve your search response times, and reduce the load and traffic on your servers.

Both Laravel and Algolia make working with JavaScript easy, with or without a JS framework.
We will show you how to quickly build rich UIs, by taking you through our
[instant search package](https://community.algolia.com/instantsearch.js/v2/),
which is a full UI solution with versions for Vue JS, React JS, and simple native JS.
Once plugged in, instant search will give your website immediate access to such
features (or [widgets](https://community.algolia.com/instantsearch.js/v2/widgets.html))
as formatted results, filtering & faceting, pagination, infinite scrolling, and many other UI components.

``

# Install



## Enabling scout

If you use Laravel 5.5, the package will be discovered automatically.

If you use a version <5.5, the service provider should be added to the `providers` array in the `config/app.php` file.

```php
Laravel\Scout\ScoutServiceProvider::class,
```

## Configuring scout

Finally, you will need to publish the configuration file. This command will create a `scout.php` configuration file in your config directory.

```php
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```


# Indexing



## Manual indexing

The following section only works with classes using the `Laravel\Scout\Searchable` trait.

### Indexing

When setting up Laravel Scout, you probably have existing data that you would like to import. There is an artisan command to import data. The command takes a model as parameter, so it has to be launched for each model class.

```
php artisan scout:import "App\Contact"
```

### Flushing and clearing

A similar command exists to flush the data from Algolia's index. It's important to note that the `flush` command only deletes data existing in your local database. It doesn't clear the index.

For instance, if you indexed data that you manually deleted from your local database, the flush command will not be able to delete them. In this case it's better to clear the index from your Algolia dashboad.

```
php artisan scout:flush "App\Contact"
```


# Options



## Custom `objectID`

Scout needs the `objectID` to be the primary key of your model. This is required because Laravel use the `objectID` to build a model collection when retrieving data.

If you want to modify the primary key of your model, you can refer to the [official docs](https://laravel.com/docs/5.4/eloquent#eloquent-model-conventions).

In the following example we define the `username` of a Contact as the primary key, so it will become the `objectID` in Algolia's index.

```php
<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use Searchable;

    public $primaryKey = 'username';

    public $incrementing = false;
}

```


# Relationships



## Relationships

By default the scout package will fetch the **loaded** relationships.

If you want to index records that haven't yet loaded any relations you can do it by loading them in
the ```toSearchableArray``` which you can override in your model.

It will look like:

```php
public function toSearchableArray()
{
  /**
   * Load the categories relation so that it's available
   *  in the laravel toArray method
   */
  $this->categories;

  return $this->toArray();
}
```

In the resulting object, you will have categories converted to an array by Laravel.
If you want a custom relation structure, you will instead do something like this:

```php
public function toSearchableArray()
{
    $array = $this->toArray();

    $array['categories'] = $this->categories->map(function ($data) {
                             return $data['name'];
                           })->toArray();

   return $array;
}
```


# Managing Settings



## Settings

If you are using Laravel Scout, we recommend changing all settings through the dashboard
and to use this package to backup and restore your settings.

The package will export all your settings for each index into a separate JSON file.
The data are saved in `resources/settings/<indice_name>.json`, which make it easy
to version.

**Warning:** Export all your [Algolia settings](https://github.com/algolia/laravel-scout-algolia-macros) into your project and push them back.

### Install

The `algolia/laravel-scout-settings` package requires the Algolia PHP client and
Laravel Scout. Note that Laravel Scout is only required to retrieve the Algolia
configuration.

```
composer require algolia/laravel-scout-settings
```

If you use Laravel 5.5, the package will be discovered automatically.
If you don't, the service provider should be added to the `providers` array in
`config/app.php` file.

```php
Algolia\Settings\ServiceProvider::class,
```

### How to use

The package provides two new commands, one to export the settings from Algolia API and the other
to restore them.

Just like the Laravel Scout import command, you have to provide a model for both commands

```
php artisan algolia:settings:backup App\Contact
// will save settings to resources/algolia-settings/contacts.json
```

```
php artisan algolia:settings:push App\Contact
// will push settings from resources/algolia-settings/contacts.json to Algolia contacts index
```


# Managing Replicas




# Eloquent compatibility



## Eloquent compatibility

Doing:

```php
MyModel::where('id', $id)->update($attributes);
```

will not trigger anything in the model (so no update will happen in Algolia). This is because it is not an Eloquent call,
it is just a convenient way to generate the query hidden behind the model.

To make this query work with Algolia you need to transform the query like this:

```php
MyModel::find($id)->update($attributes);
```


# Extending Laravel Scout



## Using the search callback

When searching, you can pass a callback function as a second parameter to the `search()` method.

Thus, instead of executing a regular query to Algolia, the callback is executed. This is useful if you want to pass more parameters,
or override the query string.

The `AlgoliaEngine` class defines a `performSearch()` method which is reponsible for calling
Algolia. This is where the callback becomes useful.

In the following example we add the user location to sort results per proximity.

```php
$lat, $lng = Auth::user()->someMethodToGetUsersLocation();

Airport::search('', function ($algolia, $query, $options) use ($lat, $lng) {

  $options['aroundLatLng'] = (float) $lat . ',' . (float) $lng;

  return $algolia->search($query, $options);
});
```

| Variable name 	| Class               	| Description                                                                                                                                      	|
|-	|	|------	|
| `$algolia`    	| AlgoliaSearch\Index 	| The Indexer from Algolia's client, used to contact the API.                                                                                      	|
| `$query`      	| String              	| The search query string                                                                                                                          	|
| `$options`    	| Array               	| The options that will be passed along with the query (see [search parameters documentation](https://www.algolia.com/doc/api-client/php/search/#search-parameters)) 	|

In the following example we search in the facet values instead of the records.

```php
// Expecting to find a Chipotle restaurant in the facets
Airport::search('dining:chip', function ($algolia, $query, $options) {

  $facetParams = explode(':', $query);

  return $algolia->searchForFacetValues($facetParams[0], $facetParams[1]);
});
```

## Using macros

The `Builder` class uses the `Macroable` trait from the Laravel framework, which allows
you to add methods to the class, without extending it. Macros often take advantage of the callback functionality described earlier. They help avoiding code duplication.

We maintain a package with a few [algolia-specific macros](https://github.com/algolia/laravel-scout-algolia-macros).

Macros are typically defined in a ServiceProvider class. If you have only a few, you can add them to your
`App\Providers\AppServiceProvider`. Otherwise you can create a `App\Providers\ScoutMacrosServiceProvider`
and add it to the `providers` array in `config/app.php`.

### Example

In this section, we will add a `count` method to the Scout builder. It will allow you to get the number
of hits directly from Algolia's raw result, before it's converted to a collection of models.

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      if (! Builder::hasMacro('count')) {

          Builder::macro('count', function () {
              $raw = $this->engine()->search($this);

              return (int) $raw['nbHits'];
          });

        }
    }
}

```

Then, you will be able to chain the `count` method directly after `search`.

```php
Contact::search('jeff')->count();
```



