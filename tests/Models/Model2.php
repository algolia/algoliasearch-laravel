<?php namespace Algolia\Tests\Models;

use Algolia\AlgoliasearchLaravel\AlgoliaEloquentTrait;

class Model2 extends \Illuminate\Database\Eloquent\Model
{
    use AlgoliaEloquentTrait;

    public static $auto_index = false;
    public static $auto_delete = false;

    protected $primaryKey = 'id2';

    public $indices = ["index1", "index2"];

    public function __construct()
    {
        $this->id2 = 1;
    }

    public function indexOnly($index_name)
    {
        if ($index_name == "test")
            return true;
        return $this->id2 != 1;
    }
}