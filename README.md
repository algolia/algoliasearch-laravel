# Algolia Search API Client for Laravel

[Algolia Search](https://www.algolia.com) is a hosted full-text, numerical, and faceted search engine capable of delivering realtime results from the first keystroke.

This PHP package integrates the Algolia Search API to the Laravel Eloquent ORM. It's based on the [algoliasearch-client-php](https://github.com/algolia/algoliasearch-client-php) package. PHP 5.5.9+ is supported.

[![Build Status](https://img.shields.io/travis/algolia/algoliasearch-laravel/master.svg?style=flat)](https://travis-ci.org/algolia/algoliasearch-laravel)
[![Latest Version](https://img.shields.io/github/release/algolia/algoliasearch-laravel.svg?style=flat)](https://github.com/algolia/algoliasearch-laravel/releases)
[![License](https://img.shields.io/packagist/l/algolia/algoliasearch-laravel.svg?style=flat)](https://packagist.org/packages/algolia/algoliasearch-laravel)

**Note:** If you're using Laravel 4, checkout the [algoliasearch-laravel-4](https://github.com/algolia/algoliasearch-laravel-4) repository.


**Note:** An easier-to-read version of this documentation is available on
[Algolia's website](https://www.algolia.com/doc/api-client/laravel/).

# Table of Contents


**Install**

1. [Install via composer](#install-via-composer)
1. [Service provider](#service-provider)
1. [Publish vendor](#publish-vendor)

**Quick Start**

1. [Quick Start](#quick-start)
1. [Ranking & Relevance](#ranking--relevance)
1. [Frontend Search (realtime experience)](#frontend-search-realtime-experience)
1. [Backend Search](#backend-search)

**Options**

1. [Auto-indexing & Asynchronism](#auto-indexing--asynchronism)
1. [Custom Index Name](#custom-index-name)
1. [Per-environment Indexes](#per-environment-indexes)
1. [Custom `objectID`](#custom-objectid)
1. [Restrict Indexing to a Subset of Your Data](#restrict-indexing-to-a-subset-of-your-data)

**Relationships**

1. [Relationships](#relationships)

**Indexing**

1. [Manual Indexing](#manual-indexing)
1. [Manual Removal](#manual-removal)
1. [Reindexing](#reindexing)
1. [Clearing an Index](#clearing-an-index)

**Manage indices**

1. [Primary/Replica](#primaryreplica)
1. [Target Multiple Indexes](#target-multiple-indexes)

**Eloquent compatibility**

1. [Eloquent compatibility](#eloquent-compatibility)
1. [Compatibility](#compatibility)


# Guides & Tutorials

Check our [online guides](https://www.algolia.com/doc):

* [Data Formatting](https://www.algolia.com/doc/indexing/formatting-your-data)
* [Import and Synchronize data](https://www.algolia.com/doc/indexing/import-synchronize-data/php)
* [Autocomplete](https://www.algolia.com/doc/search/auto-complete)
* [Instant search page](https://www.algolia.com/doc/search/instant-search)
* [Filtering and Faceting](https://www.algolia.com/doc/search/filtering-faceting)
* [Sorting](https://www.algolia.com/doc/relevance/sorting)
* [Ranking Formula](https://www.algolia.com/doc/relevance/ranking)
* [Typo-Tolerance](https://www.algolia.com/doc/relevance/typo-tolerance)
* [Geo-Search](https://www.algolia.com/doc/geo-search/geo-search-overview)
* [Security](https://www.algolia.com/doc/security/best-security-practices)
* [API-Keys](https://www.algolia.com/doc/security/api-keys)
* [REST API](https://www.algolia.com/doc/rest)


# Install



## Install via composer
Add `algolia/algoliasearch-laravel` to your `composer.json` file:

```bash
composer require algolia/algoliasearch-laravel
```

## Service provider
Add the service provider to `config/app.php` in the `providers` array.

```php
AlgoliaSearch\Laravel\AlgoliaServiceProvider::class
```

## Publish vendor

Laravel Algolia requires a connection configuration. To get started, you'll need to publish all vendor assets:

```bash
php artisan vendor:publish
```

You can add the ```--provider="Vinkla\Algolia\AlgoliaServiceProvider"``` option to only publish assets of the Algolia package.

This will create a `config/algolia.php` file in your app that you can modify to set your configuration. Also, make sure you check for changes compared to the original config file after an upgrade.


# Quick Start



## Quick Start

The following code adds search capabilities to your `Contact` model creating a `Contact` index:

```php
use Illuminate\Database\Eloquent\Model;
use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;

class Contact extends Model
{
    use AlgoliaEloquentTrait;
}
```

By default all visible attributes are sent. If you want to send specific attributes you can do something like:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public function getAlgoliaRecord()
    {
        return array_merge($this->toArray(), [
            'custom_name' => 'Custom Name'
        ]);
    }
}
```

After setting up your model, you need to manually do the initial import of your data. You can do this by calling `reindex` on your model class. Using our previous example, this would be:

```php
Contact::reindex();
```

## Ranking & Relevance

We provide many ways to configure your index settings to tune the overall relevancy but the most important ones are the **searchable attributes** and the attributes reflecting the **record popularity**. You can configure them with the following code:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public $algoliaSettings = [
        'searchableAttributes' => [
            'id',
            'name',
        ],
        'customRanking' => [
            'desc(popularity)',
            'asc(name)',
        ],
    ];
}
```

You can propagate (save) the settings to algolia using the `setSetting` method:

```php
Contact::setSettings();
```

#### Synonyms

Synonyms are used to tell the engine about words or expressions that should be considered equal in regard to the textual relevance.

Our [synonyms API](https://www.algolia.com/doc/relevance/synonyms) has been designed to manage as easily as possible a large set of synonyms for an index and its replicas.

You can use the synonyms API by adding a `synonyms` in `$algoliaSettings` class property like this:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public $algoliaSettings = [
        'synonyms' => [
            [
                'objectID' => 'red-color',
                'type'     => 'synonym',
                'synonyms' => ['red', 'another red', 'yet another red']
            ]
        ]
    ];
}
```

You can propagate (save) the settings to algolia using the `setSetting` method:

```php
Contact::setSettings();
```

## Frontend Search (realtime experience)

Traditional search implementations tend to have search logic and functionality on the backend. This made sense when the search experience consisted of a user entering a search query, executing that search, and then being redirected to a search result page.

Implementing search on the backend is no longer necessary. In fact, in most cases it is harmful to performance because of the extra network and processing latency. We highly recommend the usage of our [JavaScript API Client](https://github.com/algolia/algoliasearch-client-javascript) issuing all search requests directly from the end user's browser, mobile device, or client. It will reduce the overall search latency while offloading your servers at the same time.

In your JavaScript code you can do:

```js
var client = algoliasearch('ApplicationID', 'Search-Only-API-Key');
var index = client.initIndex('YourIndexName');
index.search('something', function(success, hits) {
  console.log(success, hits)
}, { hitsPerPage: 10, page: 0 });
```

## Backend Search

You could also use the `search` method but it's not recommended to implement a instant/realtime search experience from the backend (having a frontend search gives a better user experience):

```php
Contact::search('jon doe');
```


# Options



## Auto-indexing & Asynchronism

Each time a record is saved; it will be - asynchronously - indexed. On the other hand, each time a record is destroyed, it will be - asynchronously - removed from the index.

You can disable the auto-indexing and auto-removing setting the following options:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public static $autoIndex = false;
    public static $autoDelete = false;
}
```

You can temporary disable auto-indexing. This is often used for performance reason.

```php
Contact::$autoIndex = false;
Contact::clearIndices();

for ($i = 0; $i < 10000; $i++) {
    $contact = Contact::firstOrCreate(['name' => 'Jean']);
}

Contact::reindex(); // Will use batch operations.
Contact::$autoIndex = true;
```

You can also make a dynamic condition for those two parameters creating an `autoIndex` and/or `autoDelete method`
on your model

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public function autoIndex()
    {
        if (\App::environment() === 'test') {
            return false;
        }

        return true;
    }

    public static autoDelete()
    {
        if (\App::environment() === 'test') {
            return false;
        }

        return true;
    }
}
```

Be careful those two methods are defined in AlgoliaEloquentTrait.
When putting those methods in a parent class they will be "erased" by AlgoliaEloquentTrait if used in a child class
(because of php inheritance)

## Custom Index Name

By default, the index name will be the pluralized class name, e.g. "Contacts". You can customize the index name by using the `$indices` option:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public $indices = ['contact_all'];
}
```

## Per-environment Indexes

You can suffix the index name with the current App environment using the following option:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public static $perEnvironment = true; // Index name will be 'Contacts_{\App::environnement()}';
}
```

## Custom `objectID`

By default, the `objectID` is based on your record's `keyName` (`id` by default). You can change this behavior specifying the `objectIdKey` option (be sure to use a uniq field).

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public static $objectIdKey = 'new_key';
}
```

## Restrict Indexing to a Subset of Your Data

You can add constraints controlling if a record must be indexed by defining the `indexOnly()` method.

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public function indexOnly($index_name)
    {
        return (bool) $condition;
    }
}
```


# Relationships



## Relationships

By default the Algolia package will fetch the **loaded** relationships.

If you want to index records that didn't yet load any relations you can do it by loading them in the ```getAlgoliaRecord``` that you can create in your model.

It will look like:

```php
public function getAlgoliaRecord()
{
    /**
     * Load the categories relation so that it's available
     *  in the laravel toArray method
     */
    $this->categories;

   return $this->toArray();
}
```

In the resulted object you will have categories converted to array by Laravel. If you want a custom relation structure you will instead do something like :

```php
public function getAlgoliaRecord()
{
    /**
     * Load the categories relation so that it's available
     *  in the laravel toArray method
     */
    $extra_data = [];
    $extra_data['categories'] = array_map(function ($data) {
                                        return $data['name'];
                                }, $this->categories->toArray());

   return array_merge($this->toArray(), $extra_data);
}
```


# Indexing



## Manual Indexing

You can trigger indexing using the `pushToIndex` instance method.

```php
$contact = Contact::firstOrCreate(['name' => 'Jean']);
$contact->pushToIndex();
```

## Manual Removal

And trigger the removing using the `removeFromIndex` instance method.

```php
$contact = Contact::firstOrCreate(['name' => 'Jean']);
$contact->removeFromIndex();
```

## Reindexing

To *safely* reindex all your records (index to a temporary index + move the temporary index to the current one atomically), use the `reindex` class method:

```php
Contact::reindex();
```

To reindex all your records (in place, without deleting out-dated records):

```php
Contact::reindex(false);
```

To set settings during the reindexing process:

```php
Contact::reindex(true, true);
```

To keep settings that you set on the Algolia dashboard when reindexing and setting settings:

```php
Contact::reindex(true, true, true);
```

To implement a callback that gets called everytime a batch of entities is indexed:

```php
Contact::reindex(true, true, false, function ($entities)
{
    foreach ($entities as $entity)
    {
        var_dump($entity->id); // Contact::$id
    }
});
```

## Clearing an Index

To clear an index, use the `clearIndices` class method:

```ruby
Contact::clearIndices();
```


# Manage indices



## Primary/Replica

You can define replica indexes using the `$algolia_settings` variable:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
     use AlgoliaEloquentTrait;

     public $algoliaSettings = [
        'searchableAttributes' => [
            'id',
            'name',
        ],
        'customRanking' => [
            'desc(popularity)',
            'asc(name)',
        ],
        'replicas' => [
            'contacts_desc',
        ],
    ];

    public $replicasSettings = [
        'contacts_desc' => [
            'ranking' => [
                'desc(name)',
                'typo',
                'geo',
                'words',
                'proximity',
                'attribute',
                'exact',
                'custom'
            ]
        ]
    ];
}
```

To search using a replica use the following code:

```php
Book::search('foo bar', ['index' => 'contacts_desc']);
```

## Target Multiple Indexes

You can index a record in several indexes using the <code>$indices</code> property:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;

    public $indices = [
        'contact_public',
        'contact_private',
    ];

    public function indexOnly($indexName)
    {
        if ($indexName == 'contact_public')
            return true;

        return $this->private;
    }

}
```

To search using an extra index, use the following code:

```php
Book::search('foo bar', ['index' => 'contacts_private']);
```


# Eloquent compatibility



## Eloquent compatibility

Doing :

```php
Ad::where('id', $id)->update($attributes);
```

will not trigger anything in the model (so no update will happen in Algolia). This is because this is not an Eloquent call,
it is just a convenient way to generate the query hidden behind the model

To make this query work with Algolia you need to do it like that:

```php
Ad::find($id)->update($attributes);
```

## Compatibility

Compatible with 5.x applications


