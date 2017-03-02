<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Tests\Models\Model1;
use AlgoliaSearch\Tests\Models\Model2;
use AlgoliaSearch\Tests\Models\Model3;
use AlgoliaSearch\Tests\Models\Model4;
use AlgoliaSearch\Tests\Models\Model5;
use AlgoliaSearch\Tests\Models\Model7;
use AlgoliaSearch\Tests\Models\Model8;
use AlgoliaSearch\Tests\Models\Model9;
use Orchestra\Testbench\TestCase;

class ModelHelperTest extends TestCase
{
    /** @var \AlgoliaSearch\Laravel\ModelHelper modelHelper */
    private $modelHelper;

    public function setUp()
    {
        parent::setUp();

        $this->app->config->set('algolia', ['default' => 'main','connections' => ['main' => ['id' => 'your-application-id','key' => 'your-api-key'],'alternative' => ['id' => 'your-application-id','key' => 'your-api-key']]]);

        $this->modelHelper = $this->app->make('\AlgoliaSearch\Laravel\ModelHelper');
    }

    public function testAutoIndexAndAutoDelete()
    {
        $this->assertEquals(true, $this->modelHelper->isAutoIndex(new Model1()));
        $this->assertEquals(false, $this->modelHelper->isAutoIndex(new Model2()));
        $this->assertEquals(false, $this->modelHelper->isAutoIndex(new Model3()));
        $this->assertEquals(true, $this->modelHelper->isAutoIndex(new Model7()));

        $this->assertEquals(true, $this->modelHelper->isAutoDelete(new Model1()));
        $this->assertEquals(false, $this->modelHelper->isAutoDelete(new Model2()));
        $this->assertEquals(false, $this->modelHelper->isAutoDelete(new Model3()));
        $this->assertEquals(true, $this->modelHelper->isAutoDelete(new Model7()));

        $this->assertEquals(true, $this->modelHelper->isAutoIndex(new Model8()));
        $this->assertEquals(false, $this->modelHelper->isAutoDelete(new Model8()));

        $this->assertEquals(false, $this->modelHelper->isAutoIndex(new Model9()));
        $this->assertEquals(true, $this->modelHelper->isAutoDelete(new Model9()));
    }

    public function testGetKey()
    {
        $this->assertEquals(null, $this->modelHelper->getKey(new Model1()));
        $this->assertEquals(1, $this->modelHelper->getKey(new Model2()));
    }

    public function testIndexOnly()
    {
        $this->assertEquals(true, $this->modelHelper->indexOnly(new Model1(), 'test'));
        $this->assertEquals(true, $this->modelHelper->indexOnly(new Model2(), 'test'));
        $this->assertEquals(false, $this->modelHelper->indexOnly(new Model2(), 'test2'));
    }

    public function testGetObjectIds()
    {
        $this->assertEquals('id', $this->modelHelper->getObjectIdKey(new Model1()));
        $this->assertEquals('id2', $this->modelHelper->getObjectIdKey(new Model2()));
        $this->assertEquals('id3', $this->modelHelper->getObjectIdKey(new Model4()));

        $this->assertEquals(1, $this->modelHelper->getObjectId(new Model2()));
        $this->assertEquals(1, $this->modelHelper->getObjectId(new Model4()));
    }

    public function testGetIndices()
    {
        $this->assertEquals('model1s', $this->modelHelper->getIndices(new Model1())[0]->indexName);
        $this->assertEquals('model5s_testing', $this->modelHelper->getIndices(new Model5())[0]->indexName);
        $this->assertEquals('test', $this->modelHelper->getIndices(new Model1(), 'test')[0]->indexName);
        $this->assertEquals('test_testing', $this->modelHelper->getIndices(new Model5(), 'test')[0]->indexName);
        $this->assertEquals('model4s', $this->modelHelper->getIndices(new Model4())[0]->indexName);

        $indices = $this->modelHelper->getIndices(new Model2());

        $this->assertCount(2, $indices);
        $this->assertEquals('index1', $indices[0]->indexName);
    }
}
