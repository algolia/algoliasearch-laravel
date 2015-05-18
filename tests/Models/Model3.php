<?php

namespace AlgoliaSearch\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Model3 extends Model
{
    public static $autoIndex = true;
    public static $autoDelete = true;
}
