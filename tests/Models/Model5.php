<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model5 extends Model
{
    use AlgoliaEloquentTrait;

    public static $perEnvironment = true;
}
