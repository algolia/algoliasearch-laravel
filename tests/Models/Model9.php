<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Tests\Traits\Trait1;
use Illuminate\Database\Eloquent\Model;

class Model9 extends Model
{
    use Trait1;

    public function autoIndex()
    {
        return false;
    }

    public function autoDelete()
    {
        return true;
    }
}
