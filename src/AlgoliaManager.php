<?php namespace AlgoliaSearch\Laravel;

use AlgoliaSearch\Client;
use Illuminate\Support\Facades\Config;

class AlgoliaManager
{
    private $client = null;

    public function __construct()
    {
        $id = Config::get('algolia.id');
        $key = Config::get('algolia.key');

        if ($id === null || $key === null)
            throw new \InvalidArgumentException('The Algolia client requires authentication.');

        $this->client = new Client($id, $key);
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array(array($this->client, $method), $parameters);
    }
}