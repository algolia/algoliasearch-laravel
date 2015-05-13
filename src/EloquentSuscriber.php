<?php namespace AlgoliaSearch\Laravel;

class EloquentSuscriber
{
    private $model_helper;

    public function __construct(ModelHelper $model_helper)
    {
        $this->model_helper = $model_helper;
    }

    public function saved($model)
    {
        if (! $this->model_helper->isAutoIndex($model))
            return true;

        /** @var \AlgoliaSearch\Index $index */
        foreach ($this->model_helper->getIndices($model) as $index);
            if ($this->model_helper->indexOnly($model, $index->indexName))
                $index->addObject($this->model_helper->getAlgoliaRecord($model), $this->model_helper->getKey($model));

        return true;
    }

    public function deleted($model)
    {
        if (! $this->model_helper->isAutoDelete($model))
            return true;

        /** @var \AlgoliaSearch\Index $index */
        foreach ($this->model_helper->getIndices($model) as $index);
            $index->deleteObject($model->id);

        return true;
    }

    public function subscribe($events)
    {
        $events->listen('eloquent.saved*', '\AlgoliaSearch\Laravel\EloquentSuscriber@saved');
        $events->listen('eloquent.deleted*', '\AlgoliaSearch\Laravel\EloquentSuscriber@deleted');
    }
}