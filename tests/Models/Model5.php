<?php namespace Algolia\Tests\Models;

use Algolia\AlgoliasearchLaravel\AlgoliaEloquentTrait;

class Model5 extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;

    public $per_environment = true;
}