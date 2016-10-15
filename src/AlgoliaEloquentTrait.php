<?php

namespace AlgoliaSearch\Laravel;

use Illuminate\Support\Facades\App;

trait AlgoliaEloquentTrait
{
    /**
     * @var string
     */
    private static $methodGetName = 'getAlgoliaRecord';

    /**
     * Static calls.
     *
     * @param bool $safe
     * @param bool $setSettings
     */
    public function _reindex($safe = true, $setSettings = true)
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $modelHelper->getIndices($this);
        $indicesTmp = $safe ? $modelHelper->getIndicesTmp($this) : $indices;

        if ($setSettings === true) {
            $setToTmpIndices = ($safe === true);
            $this->_setSettings($setToTmpIndices);
        }

        static::chunk(100, function ($models) use ($indicesTmp, $modelHelper) {
            /** @var \AlgoliaSearch\Index $index */
            foreach ($indicesTmp as $index) {
                $records = [];

                foreach ($models as $model) {
                    if ($modelHelper->indexOnly($model, $index->indexName)) {
                        $records[] = $model->getAlgoliaRecordDefault($index->indexName);
                    }
                }

                $index->addObjects($records);
            }

        });

        if ($safe) {
            for ($i = 0; $i < count($indices); $i++) {
                $modelHelper->algolia->moveIndex($indicesTmp[$i]->indexName, $indices[$i]->indexName);
            }
        }
    }

    public function _clearIndices()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $modelHelper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index) {
            $index->clearIndex();
        }
    }

    /**
     * @param $query
     * @param array $parameters
     * @param $cursor
     *
     * @return mixed
     */
    public function _browseFrom($query, $parameters = [], $cursor = null)
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $index = null;

        if (isset($parameters['index'])) {
            $index = $modelHelper->getIndices($this, $parameters['index'])[0];
            unset($parameters['index']);
        } else {
            $index = $modelHelper->getIndices($this)[0];
        }

        $result = $index->browseFrom($query, $parameters, $cursor);

        return $result;
    }

    /**
     * @param $query
     * @param array $parameters
     *
     * @return mixed
     */
    public function _browse($query, $parameters = [])
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $index = null;

        if (isset($parameters['index'])) {
            $index = $modelHelper->getIndices($this, $parameters['index'])[0];
            unset($parameters['index']);
        } else {
            $index = $modelHelper->getIndices($this)[0];
        }

        $result = $index->browse($query, $parameters);

        return $result;
    }

    /**
     * @param $query
     * @param array $parameters
     *
     * @return mixed
     */
    public function _search($query, $parameters = [])
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $index = null;

        if (isset($parameters['index'])) {
            $index = $modelHelper->getIndices($this, $parameters['index'])[0];
            unset($parameters['index']);
        } else {
            $index = $modelHelper->getIndices($this)[0];
        }

        $result = $index->search($query, $parameters);

        return $result;
    }

    public function _setSettings($setToTmpIndices = false)
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $settings = $modelHelper->getSettings($this);


        $productionIndices = $indices = $modelHelper->getIndices($this);

        if ($setToTmpIndices === true) {
            $indices = $modelHelper->getIndicesTmp($this);
        }

        $slaves_settings = $modelHelper->getSlavesSettings($this);
        $slaves = isset($settings['slaves']) ? $settings['slaves'] : [];

        if (isset($settings['slaves'])) {
            /** @var \AlgoliaSearch\Index $productionIndex */
            foreach ($productionIndices as $productionIndex) {
                $slaves = array_map(function ($indexName) use ($modelHelper) {
                    return $modelHelper->getFinalIndexName($this, $indexName);
                }, $settings['slaves']);

                $productionIndex->setSettings(array('slaves' => $slaves));
            }

            unset($settings['slaves']);
        }

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index) {
            if (isset($settings['synonyms'])) {
                $index->batchSynonyms($settings['synonyms'], true, true);
            }
            else {
                // If no synonyms are passed, clear all synonyms from index
                $index->clearSynonyms(true);
            }

            if (count(array_keys($settings)) > 0) {
                // Synonyms cannot be pushed into "setSettings", it's got rejected from API and throwing exception
                // Synonyms cannot be removed directly from $settings var, because then synonym would not be set to other indices
                $settingsWithoutSynonyms = $settings;
                unset($settingsWithoutSynonyms['synonyms']);

                $index->setSettings($settingsWithoutSynonyms);
            }
        }

        foreach ($slaves as $slave) {
            if (isset($slaves_settings[$slave])) {
                $index = $modelHelper->getIndices($this, $slave)[0];

                $s = array_merge($settings, $slaves_settings[$slave]);
                unset($s['synonyms']);

                if (count(array_keys($s)) > 0)
                    $index->setSettings($s);
            }
        }
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static();
        $overload_method = '_'.$method;

        if (method_exists($instance, $overload_method)) {
            return call_user_func_array([$instance, $overload_method], $parameters);
        }

        return parent::__callStatic($method, $parameters);
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     *
     * Catch static calls call from within a class. Example : static::method();
     */
    public function __call($method, $parameters)
    {
        $overload_method = '_'.$method;

        if (method_exists($this, $overload_method)) {
            return call_user_func_array([$this, $overload_method], $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Methods.
     */
    public function getAlgoliaRecordDefault($indexName)
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $record = null;

        if (method_exists($this, self::$methodGetName)) {
            $record = $this->{self::$methodGetName}($indexName);
        } else {
            $record = $this->toArray();
        }

        if (isset($record['objectID']) == false) {
            $record['objectID'] = $modelHelper->getObjectId($this);
        }

        return $record;
    }

    public function pushToIndex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $modelHelper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index) {
            if ($modelHelper->indexOnly($this, $index->indexName)) {
                $index->addObject($this->getAlgoliaRecordDefault($index->indexName));
            }
        }
    }

    public function removeFromIndex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $modelHelper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index) {
            $index->deleteObject($modelHelper->getObjectId($this));
        }
    }

    public function autoIndex()
    {
        return (property_exists($this, 'autoIndex') == false || $this::$autoIndex === true);
    }

    public function autoDelete()
    {
        return (property_exists($this, 'autoDelete') == false || $this::$autoDelete === true);
    }
}
