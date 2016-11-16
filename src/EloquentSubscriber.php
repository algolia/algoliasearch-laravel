<?php

namespace AlgoliaSearch\Laravel;

class EloquentSubscriber
{
    private $modelHelper;

    public function __construct(ModelHelper $modelHelper)
    {
        $this->modelHelper = $modelHelper;
    }

    public function saved($model)
    {
        if (!$this->modelHelper->isAutoIndex($model)) {
            return true;
        }

        /** @var \AlgoliaSearch\Index $index */
        foreach ($this->modelHelper->getIndices($model) as $index) {
            if ($this->modelHelper->indexOnly($model, $index->indexName)) {
                $index->addObject($this->modelHelper->getAlgoliaRecord($model, $index->indexName), $this->modelHelper->getObjectId($model));
            } elseif ($this->modelHelper->wouldBeIndexed($model, $index->indexName)) {
                $index->deleteObject($this->modelHelper->getObjectId($model));
            }
        }

        return true;
    }

    public function deleted($model)
    {
        if (!$this->modelHelper->isAutoDelete($model)) {
            return true;
        }

        /** @var \AlgoliaSearch\Index $index */
        foreach ($this->modelHelper->getIndices($model) as $index) {
            $index->deleteObject($this->modelHelper->getObjectId($model));
        }

        return true;
    }

    public function subscribe($events)
    {
        $events->listen('eloquent.saved*', '\AlgoliaSearch\Laravel\EloquentSubscriber@saved');
        $events->listen('eloquent.deleted*', '\AlgoliaSearch\Laravel\EloquentSubscriber@deleted');
    }
}
