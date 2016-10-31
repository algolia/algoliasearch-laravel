<?php

namespace AlgoliaSearch\Laravel;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Algolia\AlgoliaManager;

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

    private function hasAlgoliaTrait(Model $class, $autoload = false)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        $traits = array_unique($traits);

        return (isset($traits['AlgoliaSearch\Laravel\AlgoliaEloquentTrait']));
    }

    public function wouldBeIndexed(Model $model, $index_name)
    {
        if (! method_exists($model, 'indexOnly')) {
            return false;
        }

        $cloned = clone $model;

        $cloned->setRawAttributes($cloned->getOriginal());

        return $cloned->indexOnly($index_name) === true;
    }

    public function isAutoIndex(Model $model)
    {
        return ($this->hasAlgoliaTrait($model) && $model->autoIndex());
    }

    public function isAutoDelete(Model $model)
    {
        return ($this->hasAlgoliaTrait($model) && $model->autoDelete());
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

    public function getReplicasSettings(Model $model)
    {
        $replicas_settings = property_exists($model, 'replicasSettings') ? $model->replicasSettings : [];

        // Backward compatibility
        if ($replicas_settings === [] && property_exists($model, 'slavesSettings')) {
            $replicas_settings = $model->slavesSettings;
        }

        return $replicas_settings;
    }

    public function getSlavesSettings(Model $model)
    {
        trigger_error("getSlavesSettings was renamed to getReplicasSettings", E_USER_DEPRECATED);

        return $this->getReplicasSettings($model);
    }

    public function getFinalIndexName(Model $model, $indexName)
    {
        $env_suffix = property_exists($model, 'perEnvironment') && $model::$perEnvironment === true ? '_'.\App::environment() : '';

        return $indexName.$env_suffix;
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

        $indices = array_map(function ($index_name) use ($model) {
            return $this->algolia->initIndex($this->getFinalIndexName($model, $index_name));
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

        $indices = array_map(function ($index_name) use ($model) {
            return $this->algolia->initIndex($this->getFinalIndexName($model, $index_name).'_tmp');
        }, $indicesName);

        return $indices;
    }

    public function getAlgoliaRecord(Model $model, $indexName)
    {
        return $model->getAlgoliaRecordDefault($indexName);
    }
}
