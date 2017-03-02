<?php

namespace AlgoliaSearch\Tests\Models;
use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model10 extends Model
{
    use AlgoliaEloquentTrait;

    public $algoliaSettings = [
        'synonyms' => [
            [
                'objectID' => 'red-color',
                'type'     => 'synonym',
                'synonyms' => [
                    'red',
                    'really red',
                    'much red'
                ]
            ]
        ]
    ];
}