<?php namespace Algolia\Tests;

use Algolia\AlgoliasearchLaravel\AlgoliaEloquentTrait;
use Algolia\Tests\Models\Model1;
use Algolia\Tests\Models\Model2;
use Algolia\Tests\Models\Model3;
use Algolia\Tests\Models\Model4;
use Algolia\Tests\Models\Model5;
use Orchestra\Testbench\TestCase;



class AlgoliaManagerTest extends TestCase
{
    /** @var \Algolia\AlgoliasearchLaravel\ModelHelper model_helper */
    private $model_helper;

    public function setUp()
    {
        parent::setUp();

        $this->app->config->set('algolia', ['default' => 'main','connections' => ['main' => ['id' => 'your-application-id','key' => 'your-api-key',],'alternative' => ['id' => 'your-application-id','key' => 'your-api-key',],]]);

        $this->model_helper = $this->app->make('Algolia\AlgoliasearchLaravel\ModelHelper');
    }
    public function testAutoIndexAndAutoDelete()
    {
        $this->assertEquals(true,   $this->model_helper->isAutoIndex(new Model1()));
        $this->assertEquals(false,  $this->model_helper->isAutoIndex(new Model2()));
        $this->assertEquals(false,  $this->model_helper->isAutoIndex(new Model3()));

        $this->assertEquals(true,   $this->model_helper->isAutoDelete(new Model1()));
        $this->assertEquals(false,  $this->model_helper->isAutoDelete(new Model2()));
        $this->assertEquals(false,  $this->model_helper->isAutoDelete(new Model3()));
    }

    public function testGetKey()
    {
        $this->assertEquals(null, $this->model_helper->getKey(new Model1()));
        $this->assertEquals(1, $this->model_helper->getKey(new Model2()));
    }

    public function testIndexOnly()
    {
        $this->assertEquals(true, $this->model_helper->indexOnly(new Model1(), "test"));
        $this->assertEquals(true, $this->model_helper->indexOnly(new Model2(), "test"));
        $this->assertEquals(false, $this->model_helper->indexOnly(new Model2(), "test2"));
    }

    public function testGetObjectIds()
    {
        $this->assertEquals('id', $this->model_helper->getObjectIdKey(new Model1()));
        $this->assertEquals('id2', $this->model_helper->getObjectIdKey(new Model2()));
        $this->assertEquals('id3', $this->model_helper->getObjectIdKey(new Model4()));

        $this->assertEquals(1, $this->model_helper->getObjectId(new Model2()));
        $this->assertEquals(1, $this->model_helper->getObjectId(new Model4()));
    }

    public function testGetIndices()
    {
        $this->assertEquals('model1s', $this->model_helper->getIndices(new Model1())[0]->indexName);
        $this->assertEquals('model5s_testing', $this->model_helper->getIndices(new Model5())[0]->indexName);
        $this->assertEquals('model4s', $this->model_helper->getIndices(new Model4())[0]->indexName);

        $indices = $this->model_helper->getIndices(new Model2());

        $this->assertEquals(2, count($indices));
        $this->assertEquals('index1', $indices[0]->indexName);
    }
}