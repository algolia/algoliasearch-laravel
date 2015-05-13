<?php namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;

class Model5 extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;

    public $per_environment = true;
}