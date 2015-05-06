<?php namespace Algolia\AlgoliasearchLaravel;

Trait AlgoliaEloquentTrait
{
    public $auto_index = true;
    public $auto_delete = true;

    public function reindex()
    {
        $this::chunck(100, function ($models) {
           // reindex
        });
    }
}