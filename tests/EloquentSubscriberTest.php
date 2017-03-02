<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Laravel\EloquentSubscriber;
use AlgoliaSearch\Tests\Models\Model1;

class EloquentSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $modelHelper;

    /**
     * @var EloquentSubscriber
     */
    private $eloquentSubscriber;

    protected function setUp()
    {
        $this->modelHelper = $this->getMockBuilder('AlgoliaSearch\Laravel\ModelHelper')->disableOriginalConstructor()->getMock();
        $this->eloquentSubscriber = new EloquentSubscriber($this->modelHelper);
    }

    /**
     * @dataProvider listenerDataProvider
     */
    public function testSkipSyncOnSaveIfAutoSyncDisabled($eventName, $payload)
    {
        $this->modelHelper->expects($this->once())
            ->method('isAutoIndex')
            ->with(new Model1())
            ->willReturn(false);

        $this->eloquentSubscriber->saved($eventName, $payload);
    }

    /**
     * @dataProvider listenerDataProvider
     */
    public function testSkipSyncOnDeleteIfAutoSyncDisabled($eventName, $payload)
    {
        $this->modelHelper->expects($this->once())
            ->method('isAutoDelete')
            ->with(new Model1())
            ->willReturn(false);

        $this->eloquentSubscriber->deleted($eventName, $payload);
    }

    public function listenerDataProvider()
    {
        return [
            [new Model1(), null], // Laravel 5.3
            ['eloquent.event', [new Model1()]], // Laravel 5.4
        ];
    }
}
