<?php namespace Algolia\AlgoliasearchLaravel;

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

    private function hasAlgoliaTrait(Model $model)
    {
        return (isset(class_uses($model)['Algolia\AlgoliasearchLaravel\AlgoliaEloquentTrait']));
    }

    public function isAutoIndex(Model $model)
    {
        return ($this->hasAlgoliaTrait($model) && (property_exists($model, 'auto_index') == false || $model::$auto_index === true));
    }

    public function isAutoDelete(Model $model)
    {
        return ($this->hasAlgoliaTrait($model) && (property_exists($model, 'auto_delete') == false ||$model::$auto_delete === true));
    }

    public function getKey(Model $model)
    {
        return $model->getKey();
    }

    public function indexOnly(Model $model, $index_name)
    {
        return ! method_exists($model, 'indexOnly') || $model->indexOnly($index_name);
    }

    public function getObjectId(Model $model)
    {
        return $model->{$this->getObjectIdKey($model)};
    }

    public function getObjectIdKey(Model $model)
    {
        return property_exists($model, 'object_id_key') ? $model->object_id_key : $model->getKeyName();
    }

    public function getSettings(Model $model)
    {
        return property_exists($model, 'algolia_settings') ? $model->algolia_settings : [];
    }

    public function getSlavesSettings(Model $model)
    {
        return property_exists($model, 'slaves_settings') ? $model->slaves_settings : [];
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

    public function getIndicesTmp(Model $model)
    {
        $indices_name = [];

        if (property_exists($model, 'indices') && is_array($model->indices))
            $indices_name = $model->indices;
        else
            $indices_name[] = $this->getIndexName($model);

        $env_suffix = property_exists($model, 'per_environment') && $model->per_environment === true ? '_' . \App::environment() : '';

        $indices = array_map(function ($index_name) use($env_suffix) {
            return $this->algolia->initIndex($index_name.$env_suffix."_tmp");
        }, $indices_name);

        return $indices;
    }

    public function getAlgoliaRecord(Model $model)
    {
        return $model->getAlgoliaRecordDefault();
    }
}
