<?php namespace Algolia\AlgoliasearchLaravel;

use Vinkla\Algolia\AlgoliaManager;

class EloquentSuscriber
{
    private static $trait_name = 'Algolia\AlgoliasearchLaravel\AlgoliaEloquentTrait';

    private $algolia;

    public function __construct(AlgoliaManager $algolia)
    {
        $this->algolia = $algolia;
    }

    private function getIndexName($model)
    {
        return $model->getTable();
    }


    public function saved($model)
    {
        if (! isset(class_uses($model)[static::$trait_name]) && $this->auto_index == false)
            return true;

        /** @var \AlgoliaSearch\Index $index */
        $index = $this->algolia->initIndex($this->getIndexName($model));

        $index->addObject($model->toArray(), $model->getKey());
    }

    public function deleted($model)
    {
        if (! isset(class_uses($model)[static::$trait_name]) && $this->auto_delete == false)
            return true;

        /** @var \AlgoliaSearch\Index $index */
        $index = $this->algolia->initIndex($this->getIndexName($model));

        $index->deleteObject($model->id);

        return true;
    }

    public function subscribe($events)
    {
        $events->listen('eloquent.saved*', '\Algolia\AlgoliasearchLaravel\EloquentSuscriber@saved');
        $events->listen('eloquent.deleted*', '\Algolia\AlgoliasearchLaravel\EloquentSuscriber@deleted');
    }
}