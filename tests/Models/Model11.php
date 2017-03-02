<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model11 extends Model
{
    use AlgoliaEloquentTrait;

    public function getAlgoliaRecord($indexName)
    {
        if ($indexName == 'model11s') {
            return ["is" => "working"];
        }

        return ["is not" => "working"];
    }
}
