<?php namespace AlgoliaSearch\Laravel;

use Illuminate\Support\Facades\App;

Trait AlgoliaEloquentTrait
{
    private static $methodGetName = 'getAlgoliaRecord';

    /**
     * Static calls
     */

    public function _reindex($safe = true)
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
        $modelHelper = \App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $modelHelper->getIndices($this);
        $indicesTmp = $safe ? $modelHelper->getIndicesTmp($this) : $indices;

        static::chunk(100, function ($models) use ($indicesTmp, $modelHelper) {
            /** @var \AlgoliaSearch\Index $index */
            foreach ($indicesTmp as $index)
            {
                $records = [];

                foreach ($models as $model)
                    if ($modelHelper->indexOnly($model, $index->indexName))
                        $records[] = $model->getAlgoliaRecordDefault();

                $index->addObjects($records);
            }

        });

        if ($safe)
            for ($i = 0; $i < count($indices); $i++)
                $modelHelper->algolia->moveIndex($indicesTmp[$i]->indexName, $indices[0]->indexName);
    }

    public function _clearIndices()
    {
    	/** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
    	$modelHelper = \App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $modelHelper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index)
            $index->clearIndex();
    }

    public function _search($query, $parameters = [])
    {
    	/** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
    	$modelHelper = \App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $index = null;

        if (isset($parameters['index']))
        {
            $index = $modelHelper->getIndex($parameters['index']);
            unset($parameters['index']);
        }
        else
            $index = $modelHelper->getIndices($this)[0];

        $result = $index->search($query, ['hitsPerPage' => 0]);

        return $result;
    }

    public function _setSettings()
    {
    	/** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
    	$modelHelper = \App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $settings = $modelHelper->getSettings($this);
        $slaves_settings = $modelHelper->getSlavesSettings($this);

        $indices = $modelHelper->getIndices($this);

        $slaves = isset($settings['slaves']) ? $settings['slaves'] : [];

        $b = true;

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index)
        {
            $index->setSettings($settings);

            if ($b)
            {
                $b = false;
                unset($settings['slaves']);
            }
        }

        if (count($slaves) > 0)
        {
            foreach ($slaves as $slave)
            {
                if (isset($slaves_settings[$slave]))
                {
                    $index = $modelHelper->algolia->initIndex($slave);

                    $s = array_merge($settings, $slaves_settings[$slave]);

                    $index->setSettings($s);
                }
            }
        }
    }


    public static function __callStatic($method, $parameters)
    {
        $instance = new static();

        $method = '_'.$method;

        if (method_exists($instance, $method));
            return call_user_func_array([$instance, $method], $parameters);

        return parent::__callStatic($method, $parameters);
    }

    /**
     * Methods
     */

    public function getAlgoliaRecordDefault()
    {
    	/** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
    	$modelHelper = \App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $record = null;

        if (method_exists($this, static::$methodGetName))
            $record = $this->{static::$methodGetName}();
        else
            $record = $this->toArray();

        if (isset($record['objectID']) == false)
            $record['objectID'] = $modelHelper->getObjectId($this);

        return $record;
    }

    public function pushToindex()
    {
    	/** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
    	$modelHelper = \App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $modelHelper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index)
            if ($modelHelper->indexOnly($this, $index->indexName))
                $index->addObject($this->getAlgoliaRecordDefault());
    }

    public function removeFromIndex()
    {
    	/** @var \AlgoliaSearch\Laravel\ModelHelper $modelHelper */
    	$modelHelper = \App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $modelHelper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index)
            $index->deleteObject($modelHelper->getObjectId($this));
    }
}
