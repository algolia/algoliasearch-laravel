<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Tests\Traits\Trait1;
use Illuminate\Database\Eloquent\Model;

class Model8 extends Model
{
    use Trait1;

    public function autoIndex()
    {
        return true;
    }

    public function autoDelete()
    {
        return false;
    }
}
