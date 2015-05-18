<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model2 extends Model
{
    use AlgoliaEloquentTrait;

    public static $autoIndex = false;
    public static $autoDelete = false;

    protected $primaryKey = 'id2';

    public $indices = ['index1', 'index2'];

    public function __construct()
    {
        $this->id2 = 1;
    }

    public function indexOnly($indexName)
    {
        if ($indexName === 'test') {
            return true;
        }

        return $this->id2 !== 1;
    }
}
