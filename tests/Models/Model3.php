<?php namespace Algolia\Tests\Models;

use Algolia\AlgoliasearchLaravel\AlgoliaEloquentTrait;

class Model3 extends \Illuminate\Database\Eloquent\Model
{
    public $auto_index = true;
    public $auto_delete = true;
}