<?php

namespace AlgoliaSearch\Laravel;

use Illuminate\Database\Eloquent\Model;

class ModelHelper
{
    public $algolia;

    public function __construct(AlgoliaManager $algolia)
    {
        $this->algolia = $algolia;
    }

    private function getIndexName(Model $model)
    {
        return $model->getTable();
    }

    private function hasAlgoliaTrait(Model $model)
    {
        return (isset(class_uses($model)['AlgoliaSearch\Laravel\AlgoliaEloquentTrait']));
    }

    public function isAutoIndex(Model $model)
    {
        return ($this->hasAlgoliaTrait($model) && (property_exists($model, 'autoIndex') == false || $model::$autoIndex === true));
    }

    public function isAutoDelete(Model $model)
    {
        return ($this->hasAlgoliaTrait($model) && (property_exists($model, 'autoDelete') == false || $model::$autoDelete === true));
    }

    public function getKey(Model $model)
    {
        return $model->getKey();
    }

    public function indexOnly(Model $model, $index_name)
    {
        return !method_exists($model, 'indexOnly') || $model->indexOnly($index_name);
    }

    public function getObjectId(Model $model)
    {
        return $model->{$this->getObjectIdKey($model)};
    }

    public function getObjectIdKey(Model $model)
    {
        return property_exists($model, 'objectIdKey') ? $model::$objectIdKey : $model->getKeyName();
    }

    public function getSettings(Model $model)
    {
        return property_exists($model, 'algoliaSettings') ? $model->algoliaSettings : [];
    }

    public function getSlavesSettings(Model $model)
    {
        return property_exists($model, 'slavesSettings') ? $model->slavesSettings : [];
    }

    /**
     * @return \AlgoliaSearch\Index
     */
    public function getIndices(Model $model, $indexName = null)
    {
        $indicesName = [];

        if ($indexName !== null) {
            $indicesName[] = $indexName;
        } elseif (property_exists($model, 'indices') && is_array($model->indices)) {
            $indicesName = $model->indices;
        } else {
            $indicesName[] = $this->getIndexName($model);
        }

        $env_suffix = property_exists($model, 'perEnvironment') && $model::$perEnvironment === true ? '_'.\App::environment() : '';

        $indices = array_map(function ($index_name) use ($env_suffix) {
            return $this->algolia->initIndex($index_name.$env_suffix);
        }, $indicesName);

        return $indices;
    }

    public function getIndicesTmp(Model $model)
    {
        $indicesName = [];

        if (property_exists($model, 'indices') && is_array($model->indices)) {
            $indicesName = $model->indices;
        } else {
            $indicesName[] = $this->getIndexName($model);
        }

        $env_suffix = property_exists($model, 'perEnvironment') && $model::$perEnvironment === true ? '_'.\App::environment() : '';

        $indices = array_map(function ($index_name) use ($env_suffix) {
            return $this->algolia->initIndex($index_name.$env_suffix.'_tmp');
        }, $indicesName);

        return $indices;
    }

    public function getAlgoliaRecord(Model $model)
    {
        return $model->getAlgoliaRecordDefault();
    }
}
