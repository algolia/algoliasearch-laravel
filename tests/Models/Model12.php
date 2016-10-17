<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model12 extends Model
{
    use AlgoliaEloquentTrait;

    public static $perEnvironment = true;

    public $algoliaSettings = [
        'slaves' => [
            'model_6_desc',
        ],
    ];

    public $slavesSettings = [
        'model_6_desc' => [
            'ranking' => [
                'desc(name)'
            ]
        ]
    ];
}
