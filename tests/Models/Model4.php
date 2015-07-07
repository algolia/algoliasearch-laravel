<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model4 extends Model
{
    use AlgoliaEloquentTrait;

    public static $objectIdKey = 'id3';

    protected $primaryKey = 'id2';

    public static $perEnvironment = false;

    public function __construct()
    {
        $this->id2 = 1;
        $this->id3 = 1;
    }

    public function getAlgoliaRecord()
    {
        $extraData = [
            'name' => 'test',
        ];

        return array_merge($this->toArray(), $extraData);
    }
}
