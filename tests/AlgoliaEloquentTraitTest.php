<?php namespace Algolia\Tests;

use Algolia\Tests\Models\Model2;
use Algolia\Tests\Models\Model4;
use \Orchestra\Testbench\TestCase;

class AlgoliaEloquentTraitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->config->set('algolia', ['default' => 'main','connections' => ['main' => ['id' => 'your-application-id','key' => 'your-api-key',],'alternative' => ['id' => 'your-application-id','key' => 'your-api-key',],]]);
    }

    public function testGetAlgoliaRecordDefault()
    {
        $this->assertEquals(['id2' => 1, 'objectID' => 1], (new Model2())->getAlgoliaRecordDefault());
        $this->assertEquals(['id2' => 1, 'objectID' => 1, 'id3' => 1, 'name' => 'test'], (new Model4())->getAlgoliaRecordDefault());
    }

    public function testPushToindex()
    {
        /** @var \Algolia\AlgoliasearchLaravel\ModelHelper $real_model_helper */
        $real_model_helper = \App::make('\Algolia\AlgoliasearchLaravel\ModelHelper');

        $model_helper = \Mockery::mock('\Algolia\AlgoliasearchLaravel\ModelHelper');

        $index = \Mockery::mock('\AlgoliaSearch\Index');

        $model_helper->shouldReceive('getIndices')->andReturn([$index, $index]);
        $model_helper->shouldReceive('getObjectId')->andReturn($real_model_helper->getObjectId(new Model4()));
        $model_helper->shouldReceive('indexOnly')->andReturn(true);

        \App::instance('\Algolia\AlgoliasearchLaravel\ModelHelper', $model_helper);

        $index->shouldReceive('addObject')->times(2)->with((new Model4())->getAlgoliaRecordDefault());

        $this->assertEquals(null, (new Model4())->pushToindex());
    }

    public function testRemoveFromIndex()
    {
        /** @var \Algolia\AlgoliasearchLaravel\ModelHelper $real_model_helper */
        $real_model_helper = \App::make('\Algolia\AlgoliasearchLaravel\ModelHelper');

        $model_helper = \Mockery::mock('\Algolia\AlgoliasearchLaravel\ModelHelper');

        $index = \Mockery::mock('\AlgoliaSearch\Index');

        $model_helper->shouldReceive('getIndices')->andReturn([$index, $index]);
        $model_helper->shouldReceive('getObjectId')->andReturn($real_model_helper->getObjectId(new Model4()));

        \App::instance('\Algolia\AlgoliasearchLaravel\ModelHelper', $model_helper);

        $index->shouldReceive('deleteObject')->times(2)->with(1);

        $this->assertEquals(null, (new Model4())->removeFromIndex());
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}