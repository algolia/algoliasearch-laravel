<?php

namespace AlgoliaSearch\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Model3 extends Model
{
    public static $auto_index = true;
    public static $auto_delete = true;
}
