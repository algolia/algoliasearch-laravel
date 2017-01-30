<?php

namespace AlgoliaSearch\Laravel;

use Illuminate\Database\Eloquent\Model;

class EloquentSubscriber
{
    private $modelHelper;

    public function __construct(ModelHelper $modelHelper)
    {
        $this->modelHelper = $modelHelper;
    }

    public function saved($eventName, $payload = null)
    {
        $model = $this->getModelFromParams($eventName, $payload);

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

    public function deleted($eventName, $payload = null)
    {
        $model = $this->getModelFromParams($eventName, $payload);

        if (!$this->modelHelper->isAutoDelete($model)) {
            return true;
        }

        /** @var \AlgoliaSearch\Index $index */
        foreach ($this->modelHelper->getIndices($model) as $index) {
            $index->deleteObject($this->modelHelper->getObjectId($model));
        }

        return true;
    }

    /**
     * @param string|Model $eventName
     * @param array|null   $payload
     *
     * @return Model
     */
    private function getModelFromParams($eventName, $payload = null)
    {
        if($eventName instanceof Model) {
            // Laravel < 5.4
            return $eventName;
        }

        // Laravel >= 5.4
        return $payload[0];
    }

    public function subscribe($events)
    {
        $events->listen('eloquent.saved*', '\AlgoliaSearch\Laravel\EloquentSubscriber@saved');
        $events->listen('eloquent.deleted*', '\AlgoliaSearch\Laravel\EloquentSubscriber@deleted');
    }
}
