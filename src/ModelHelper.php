<?php namespace Algolia\AlgoliasearchLaravel;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Algolia\AlgoliaManager;

class ModelHelper
{
    private $algolia;

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
        return (! isset(class_uses($model)['Algolia\AlgoliasearchLaravel\AlgoliaEloquentTrait']));
    }

    public function isAutoIndex(Model $model)
    {
        return ($this->hasAlgoliaTrait($model) && $model->auto_index == false);
    }

    public function isAutoDelete(Model $model)
    {
        return ($this->hasAlgoliaTrait($model) || $model->auto_delete == false);
    }

    public function getKey(Model $model)
    {
        return $model->getKey();
    }

    public function indexOnly(Model $model, $index_name)
    {
        return ! method_exists($model, 'indexOnly') || $model->indexOnly($index_name);
    }

    public function getObjectIdKey(Model $model)
    {
        return property_exists($model, 'object_id_key') ? $model->{$model->object_id_key} : 'id';
    }

    /**
     * @return \AlgoliaSearch\Index
     */
    public function getIndices(Model $model)
    {
        $indices_name = [];

        if (property_exists($model, 'indices') && is_array($model->indices))
            $indices_name = $model->indices;
        else
            $indices_name[] = $this->getIndexName($model);

        $env_suffix = property_exists($model, 'per_environment') && $model->per_environment === true ? '_' . \App::environment() : '';

        $indices = array_map(function ($index_name) use($env_suffix) {
            return $this->algolia->initIndex($index_name.$env_suffix);
        }, $indices_name);

        return $indices;
    }

    public function getAlgoliaRecord(Model $model)
    {
        return $model->getAlgoliaRecordDefault();
    }
}