Algolia Search for Laravel
==================

This php package integrate the Algolia Search API to your favorite Laravel Eloquent ORM. It's based on the [algoliasearch-client-php](https://github.com/algolia/algoliasearch-client-php) package. Php 5.4+ is supported.

[![Build Status](https://travis-ci.org/algolia/algoliasearch-laravel.png?branch=master)](https://travis-ci.org/algolia/algoliasearch-rails)
![ActiveRecord](https://img.shields.io/badge/ActiveRecord-yes-blue.svg?style=flat-square)

Table of Content
-------------
**Get started**

1. [Install](#install)
1. [Configuration](#configuration)
1. [Quick Start](#quick-start)
1. [Ranking & Relevance](#ranking--relevance)
1. [Options](#options)
1. [Indexing](#indexing)
1. [Master/Slave](#masterslave)
1. [Target multiple indexes](#target-multiple-indexes)
1. [Search](#search)

Install
-------------

Add `algolia/algoliasearch-laravel` to your `composer.json` file:

```json
  "require": {
      "algolia/algoliasearch-laravel": "master"
  }
```

Add the service provider to ```config/app.php``` in the `providers` array.

```php
'Algolia\AlgoliasearchLaravel\AlgoliaServiceProvider'
```

Configuration
-------------

Laravel Algolia requires connection configuration. To get started, you'll need to publish all vendor assets:

```bash
php artisan vendor:publish
```

This will create a `config/algolia.php` file in your app that you can modify to set your configuration. Also, make sure you check for changes to the original config file in this package between releases.



Quick Start
-------------

The following code will create a <code>Contact</code> add search capabilities to your <code>Contact</code> model:

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;
}
```

By default all your visible attributes will be send

If you want to send specific attributes you can do something like

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;
    
    public function getAlgoliaRecord()
    {
        $extra_data = [
            'custom_name' => 'Custom Name'
        ];

        return array_merge($this->toArray(), $extra_data);
    }
}
```

#### Ranking & Relevance

We provide many ways to configure your index allowing you to tune your overall index relevancy. The most important ones are the **searchable attributes** and the attributes reflecting **record popularity**.

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;
    
    public $algolia_settings = [
    	'attributesToIndex => ['id', 'name'],
    	'customRanking => ['desc(popularity)', 'asc('name')]
    ]
}
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

You could also use `search` but it's not recommended. This method will search on Algolia

```php
Contact::search("jon doe")
```

Options
----------

#### Auto-indexing & asynchronism

Each time a record is saved; it will be - asynchronously - indexed. On the other hand, each time a record is destroyed, it will be - asynchronously - removed from the index.

You can disable auto-indexing and auto-removing setting the following options:
   
```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;
    
    public $auto_index = false;
	 public $auto_delete = false;
}
```
 
You can temporary disable auto-indexing using the <code>without_auto_index</code> scope. This is often used for performance reason.

```php
Contact::$auto_index = false;
Contact.clearIndices();

for ($i = 0; $i < 10000; $i++)
	$contact = Contact::firstOrCreate(['name' => 'Jean']);

Contact::reindex()! # will use batch operations
```

#### Custom index name

By default, the index name will be the class name pluriazed, e.g. "Contacts". You can customize the index name by using the `indices` option:

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;
    
    public $indices = ["contact_all"];
}
```

#### Per-environment indexes

You can suffix the index name with the current Rails environment using the following option:

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;
    
    public $per_environment = true; # index name will be "Contacts_{\App::environnement()}"
}
```

#### Custom ```objectID```

By default, the `objectID` is based on your record's keyName (`id` by default). You can change this behavior specifying the `object_id_key` option (be sure to use a uniq field).

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;
    
	public $object_id_key = 'new key';
}
```

#### Restrict indexing to a subset of your data

You can add constraints controlling if a record must be indexed by defining indexOnly function.

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
   	use AlgoliaEloquentTrait;
    
	public function indexOnly($index_name)
	{
		if ($index_name == "contact_all")
   			return $this->isActive == 1
   			
   	   	return 1;
	}
}
```

Indexing
---------

#### Manual indexing

You can trigger indexing using the <code>pushToindex</code> instance method.

```php
$c = Contact::firstOrCreate!(['name' => 'Jean']);
$c->pushToindex();
```

#### Manual removal

And trigger index removing using the <code>removeFromIndex</code> instance method.

```php
$c = Contact::firstOrCreate!(['name' => 'Jean']);
$c->removeFromindex();
```
#### Reindexing

To *safely* reindex all your records (index to a temporary index + move the temporary index to the current one atomically), use the <code>reindex</code> class method:

```php
Contact::reindex()
```

To reindex all your records (in place, without deleting out-dated records):

```php
Contact.reindex(false)
```

#### Clearing an index

To clear an index, use the <code>clear_index!</code> class method:

```ruby
Contact::clearIndices()
```

Master/slave
---------

You can define slave indexes in the `$algolia_settings` variable:

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
	 use AlgoliaEloquentTrait;
    
	 public $algolia_settings = [
        'attributesToIndex' => ['id', 'name'],
    	'customRanking' => ['desc(popularity)', 'asc(name)'],
    	'slaves' => ['contacts_desc']
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

Target multiple indexes
---------

You can index a record in several indexes using the <code>add_index</code> method:

```php
class Contact extends \Illuminate\Database\Eloquent\Model
{
	use AlgoliaEloquentTrait;
    
	public $indices = ["contact_public", "contact_private"];
    
	public function indexOnly($index_name)
	{
		if ($index_name == "contact_public")
   			return $this->isPublic == 1;
   			
   	   	return $this->isPublic == 0;
	}

}
```

To search using an extra index, use the following code:

```php
Book.search('foo bar', ['index' => 'contacts_desc']);
```