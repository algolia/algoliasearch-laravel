<?php namespace AlgoliaSearch\Laravel;

use Illuminate\Support\Facades\App;

Trait AlgoliaEloquentTrait
{
    private static $method_get_name = 'getAlgoliaRecord';

    /**
     * Static calls
     */

    public function _reindex($safe = true)
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $model_helper */
        $model_helper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $model_helper->getIndices($this);
        $indices_tmp = $safe ? $model_helper->getIndicesTmp($this) : $indices;

        static::chunk(100, function ($models) use ($indices_tmp, $model_helper) {
            /** @var \AlgoliaSearch\Index $index */
            foreach ($indices_tmp as $index)
            {
                $records = [];

                foreach ($models as $model)
                    if ($model_helper->indexOnly($model, $index->indexName))
                        $records[] = $model->getAlgoliaRecordDefault();

                $index->addObjects($records);
            }

        });

        if ($safe)
            for ($i = 0; $i < count($indices); $i++)
                $model_helper->algolia->moveIndex($indices_tmp[$i]->indexName, $indices[0]->indexName);
    }

    public function _clearIndices()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $model_helper */
        $model_helper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $model_helper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index)
            $index->clearIndex();
    }

    public function _search($query, $parameters = [])
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $model_helper */
        $model_helper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $index = null;

        if (isset($parameters['index']))
        {
            $index = $model_helper->getIndex($parameters['index']);
            unset($parameters['index']);
        }
        else
            $index = $model_helper->getIndices($this)[0];

        $result = $index->search($query, ['hitsPerPage' => 0]);

        return $result;
    }

    public function _setSettings()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $model_helper */
        $model_helper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $settings = $model_helper->getSettings($this);
        $slaves_settings = $model_helper->getSlavesSettings($this);

        $indices = $model_helper->getIndices($this);

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
                    $index = $model_helper->algolia->initIndex($slave);

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
        /** @var \AlgoliaSearch\Laravel\ModelHelper $model_helper */
        $model_helper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $record = null;

        if (method_exists($this, static::$method_get_name))
            $record = $this->{static::$method_get_name}();
        else
            $record = $this->toArray();

        if (isset($record['objectID']) == false)
            $record['objectID'] = $model_helper->getObjectId($this);

        return $record;
    }

    public function pushToindex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $model_helper */
        $model_helper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $model_helper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index)
            if ($model_helper->indexOnly($this, $index->indexName))
                $index->addObject($this->getAlgoliaRecordDefault());
    }

    public function removeFromIndex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $model_helper */
        $model_helper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $indices = $model_helper->getIndices($this);

        /** @var \AlgoliaSearch\Index $index */
        foreach ($indices as $index)
            $index->deleteObject($model_helper->getObjectId($this));
    }
}