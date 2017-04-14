<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model14 extends Model
{
    use AlgoliaEloquentTrait;

    public function indices()
    {
        return ['test'];
    }
}
