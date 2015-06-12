# Laravel Algolia Search

This php package integrate the Algolia Search API to your favorite Laravel Eloquent ORM. It's based on the [algoliasearch-client-php](https://github.com/algolia/algoliasearch-client-php) package. PHP 5.5.9+ is supported.

[![Build Status](https://img.shields.io/travis/algolia/algoliasearch-laravel/master.svg?style=flat)](https://travis-ci.org/algolia/algoliasearch-laravel)
[![Latest Version](https://img.shields.io/github/release/algolia/algoliasearch-laravel.svg?style=flat)](https://github.com/algolia/algoliasearch-laravel/releases)
[![License](https://img.shields.io/packagist/l/algolia/algoliasearch-laravel.svg?style=flat)](https://packagist.org/packages/algolia/algoliasearch-laravel)

## Table of Content

1. [Install](#install)
2. [Configuration](#configuration)
3. [Quick Start](#quick-start)
4. [Ranking & Relevance](#ranking--relevance)
5. [Options](#options)
6. [Indexing](#indexing)
7. [Master/Slave](#masterslave)
8. [Target multiple indexes](#target-multiple-indexes)
9. [Search](#search)

## Install

Add `algolia/algoliasearch-laravel` to your `composer.json` file:

```bash
composer require algolia/algoliasearch-laravel
```

Add the service provider to `config/app.php` in the `providers` array.

```php
AlgoliaSearch\Laravel\AlgoliaServiceProvider::class
```

## Configuration

Laravel Algolia requires connection configuration. To get started, you'll need to publish all vendor assets:

```bash
php artisan vendor:publish
```

This will create a `config/algolia.php` file in your app that you can modify to set your configuration. Also, make sure you check for changes to the original config file in this package between releases.

## Quick Start

The following code will create a `Contact` add search capabilities to your `Contact` model:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;
}
```

By default all your visible attributes will be send

If you want to send specific attributes you can do something like

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

#### Ranking & Relevance

We provide many ways to configure your index allowing you to tune your overall index relevancy. The most important ones are the **searchable attributes** and the attributes reflecting **record popularity**.

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;
    
    public $algoliaSettings = [
    	'attributesToIndex' => [
    		'id', 
    		'name',
    	],
    	'customRanking => [
    		'desc(popularity)', 
    		'asc(name)',
    	],
    ];
}
```

You can then do a save the settings to algolia using the setSetting method

```php
Contact::setSettings();
```

#### Frontend Search (realtime experience)

Traditional search implementations tend to have search logic and functionality on the backend. This made sense when the search experience consisted of a user entering a search query, executing that search, and then being redirected to a search result page.

Implementing search on the backend is no longer necessary. In fact, in most cases it is harmful to performance because of added network and processing latency. We highly recommend the usage of our [JavaScript API Client](https://github.com/algolia/algoliasearch-client-js) issuing all search requests directly from the end user's browser, mobile device, or client. It will reduce the overall search latency while offloading your servers at the same time.

In your JavaScript code you can do:

```js
var client = algoliasearch('ApplicationID', 'Search-Only-API-Key');
var index = client.initIndex('YourIndexName');
index.search('something', function(success, hits) {
  console.log(success, hits)
}, { hitsPerPage: 10, page: 0 });
```

#### Backend Search

You could also use `search` but it's not recommended. This method will search on Algolia.

```php
Contact::search('jon doe');
```

## Options

#### Auto-indexing & Asynchronism

Each time a record is saved; it will be - asynchronously - indexed. On the other hand, each time a record is destroyed, it will be - asynchronously - removed from the index.

You can disable auto-indexing and auto-removing setting the following options:
   
```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
	use AlgoliaEloquentTrait;
    
	public $autoIndex = false;
	public $autoDelete = false;
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
```

#### Custom Index Name

By default, the index name will be the class name pluriazed, e.g. "Contacts". You can customize the index name by using the `$indices` option:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;
    
    public $indices = ['contact_all'];
}
```

#### Per-environment Indexes

You can suffix the index name with the current Rails environment using the following option:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;
    
    public $perEnvironment = true; // Index name will be 'Contacts_{\App::environnement()}';
}
```

#### Custom `objectID`

By default, the `objectID` is based on your record's keyName (`id` by default). You can change this behavior specifying the `object_id_key` option (be sure to use a uniq field).

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use AlgoliaEloquentTrait;
    
	public $objectIdKey = 'new_key';
}
```

#### Restrict Indexing to a Subset of Your Data

You can add constraints controlling if a record must be indexed by defining `indexOnly()` method.

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

## Indexing

#### Manual Indexing

You can trigger indexing using the `pushToindex()` instance method.

```php
$contact = Contact::firstOrCreate(['name' => 'Jean']);
$contact->pushToindex();
```

#### Manual Removal

And trigger index removing using the `removeFromIndex()` instance method.

```php
$contact = Contact::firstOrCreate(['name' => 'Jean']);
$contact->removeFromindex();
```
#### Reindexing

To *safely* reindex all your records (index to a temporary index + move the temporary index to the current one atomically), use the `reindex` class method:

```php
Contact::reindex();
```

To reindex all your records (in place, without deleting out-dated records):

```php
Contact::reindex(false);
```

#### Clearing an Index

To clear an index, use the `clear_index!` class method:

```ruby
Contact::clearIndices();
```

## Master/Slave

You can define slave indexes in the `$algolia_settings` variable:

```php
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
	 use AlgoliaEloquentTrait;
    
	 public $algoliaSettings = [
		'attributesToIndex' => [
			'id', 
			'name',
		],
    	'customRanking' => [
    		'desc(popularity)', 
    		'asc(name)',
    	],
    	'slaves' => [
    		'contacts_desc',
    	],
    ];

    public $slaves_settings = [
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

To search using a slave, use the following code:

```php
Book.search('foo bar', ['index' => 'contacts_desc']);
```

## Target Multiple Indexes

You can index a record in several indexes using the <code>add_index</code> method:

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
		return $indexName === 'contact_public';
	}

}
```

To search using an extra index, use the following code:

```php
Book::search('foo bar', ['index' => 'contacts_desc']);
```

## License

Laravel Algolia Search is licensed under [The MIT License (MIT)](LICENSE).

