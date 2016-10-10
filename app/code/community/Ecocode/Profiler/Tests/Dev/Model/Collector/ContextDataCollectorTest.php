<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_ContextDataCollectorTest
    extends TestHelper
{

    public function testCollect()
    {
        $collector = new Ecocode_Profiler_Model_Collector_ContextDataCollector();

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );


        $this->assertCount(0, $collector->getList());
    }

    public function testLateCollect()
    {
        $collector = $this->getMockedCollector();

        $this->assertNull($collector->getById('xx'));

        $context = new Ecocode_Profiler_Model_Context('block:test');
        $collector->getContextHelper()->open($context);


        $collector->lateCollect();

        $this->assertCount(2, $collector->getList());

        $this->assertEquals($context, $collector->getById($context->getId()));
    }

    /**
     * @return Ecocode_Profiler_Model_Collector_ContextDataCollector
     */
    public function getMockedCollector()
    {
        $collectorMock = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_ContextDataCollector')
            ->setMethods(['getContextHelper'])
            ->getMock();

        $contextHelper = new Ecocode_Profiler_Helper_Context();
        $collectorMock->method('getContextHelper')
            ->willReturn($contextHelper);


        return $collectorMock;
    }

}
