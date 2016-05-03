<!--NO_HTML-->
# Laravel Algolia Search
<!--/NO_HTML-->

This PHP package integrates the Algolia Search API to the Laravel Eloquent ORM. It's based on the [algoliasearch-client-php](https://github.com/algolia/algoliasearch-client-php) package. PHP 5.5.9+ is supported.

[![Build Status](https://img.shields.io/travis/algolia/algoliasearch-laravel/master.svg?style=flat)](https://travis-ci.org/algolia/algoliasearch-laravel)
[![Latest Version](https://img.shields.io/github/release/algolia/algoliasearch-laravel.svg?style=flat)](https://github.com/algolia/algoliasearch-laravel/releases)
[![License](https://img.shields.io/packagist/l/algolia/algoliasearch-laravel.svg?style=flat)](https://packagist.org/packages/algolia/algoliasearch-laravel)

**Note:** If you're using Laravel 4, checkout the [algoliasearch-laravel-4](https://github.com/algolia/algoliasearch-laravel-4) repository.

<!--NO_HTML-->

## Table of Content

1. [Install](#install)
2. [Configuration](#configuration)
3. [Quick Start](#quick-start)
4. [Ranking & Relevance](#ranking--relevance)
5. [Options](#options)
6. [Indexing](#indexing)
7. [Master/Slave](#masterslave)
8. [Target multiple indexes](#target-multiple-indexes)

<!--/NO_HTML-->

# Install

Add `algolia/algoliasearch-laravel` to your `composer.json` file:

```bash
composer require algolia/algoliasearch-laravel
```

Add the service provider to `config/app.php` in the `providers` array.

```php
AlgoliaSearch\Laravel\AlgoliaServiceProvider::class
```

# Configuration

Laravel Algolia requires a connection configuration. To get started, you'll need to publish all vendor assets:

```bash
php artisan vendor:publish
```

You can add the ```--provider="Vinkla\Algolia\AlgoliaServiceProvider"``` option to only publish assets of the Algolia package.

This will create a `config/algolia.php` file in your app that you can modify to set your configuration. Also, make sure you check for changes compared to the original config file after an upgrade.

# Quick Start

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

## Ranking & Relevance

We provide many ways to configure your index settings to tune the overall relevancy but the most important ones are the **searchable attributes** and the attributes reflecting the **record popularity**. You can configure them with the following code:

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
    ];
}
```

You can propagate (save) the settings to algolia using the `setSetting` method:

```php
Contact::setSettings();
```

## Frontend Search (realtime experience)

Traditional search implementations tend to have search logic and functionality on the backend. This made sense when the search experience consisted of a user entering a search query, executing that search, and then being redirected to a search result page.

Implementing search on the backend is no longer necessary. In fact, in most cases it is harmful to performance because of the extra network and processing latency. We highly recommend the usage of our [JavaScript API Client](https://github.com/algolia/algoliasearch-client-js) issuing all search requests directly from the end user's browser, mobile device, or client. It will reduce the overall search latency while offloading your servers at the same time.

In your JavaScript code you can do:

```js
var client = algoliasearch('ApplicationID', 'Search-Only-API-Key');
var index = client.initIndex('YourIndexName');
index.search('something', function(success, hits) {
  console.log(success, hits)
}, { hitsPerPage: 10, page: 0 });
```

## Backend Search

You could also use the `search` method but it's not recommended to implement instant/realtime search experience:

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
```

You can also make a dynamic condition for those two parameters creating an ``autoIndex``` and/or ```autoDelete method```
on your model

```
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

By default the Algolia package will fetch the **loaded** relationships.

If you want to index records that didn't yet load any relations you can do it by loading them in the ```getAlgoliaRecord``` that you can create in your model.

It will look like:

```php
public function getAlgoliaRecord()
{
	/**
	 * Load the categories relation so that it's available
	 * 	in the laravel toArray method
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
	 * 	in the laravel toArray method
	 */
	$extra_data = [];
	$extra_data['categories'] = array_map(function ($data) {
							            return $data['name'];
						        }, $this->categories->toArray();
  
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

## Clearing an Index

To clear an index, use the `clearIndices` class method:

```ruby
Contact::clearIndices();
```

# Master/Slave

You can define slave indexes using the `$algolia_settings` variable:

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

    public $slavesSettings = [
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

To search using a slave use the following code:

```php
Book::search('foo bar', ['index' => 'contacts_desc']);
```

# Target Multiple Indexes

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

Doing :

```
Ad::where('id', $id)->update($attributes);
```

will not trigger anything in the model (so no update will happen in Algolia). This is because this is not an Eloquent call,
it is just a convenient way to generate the query hidden behind the model

To make this query work with Algolia you need to do it like that:

```
Ad::find($id)->update($attributes);
```

<!--NO_HTML-->
# Compatibility

Compatible with 5.x applications

## License

Laravel Algolia Search is licensed under [The MIT License (MIT)](LICENSE).

<!--/NO_HTML-->
