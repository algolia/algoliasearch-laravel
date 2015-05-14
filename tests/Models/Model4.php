<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model4 extends Model
{
    use AlgoliaEloquentTrait;

    public $object_id_key = 'id3';

    protected $primaryKey = 'id2';

    public $per_environment = false;

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
