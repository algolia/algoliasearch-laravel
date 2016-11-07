<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model13 extends Model
{
    use AlgoliaEloquentTrait;

    public $algoliaSettings = [
        'attributesForFaceting' => [
            'attribute1',
            'attribute2'
        ]
    ];
}
