<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_TimeDataCollectorTest
    extends TestHelper
{

    public function testCollect()
    {
        /** @var Ecocode_Profiler_Model_Collector_TimeDataCollector $collector */
        $collector = new Ecocode_Profiler_Model_Collector_TimeDataCollector();

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $this->assertEquals(0, $collector->getTotalTime());
        return $collector;
    }

    /**
     * @depends testCollect
     */
    public function testLateCollect(Ecocode_Profiler_Model_Collector_TimeDataCollector $collector)
    {
        $collector->lateCollect();
        $this->assertGreaterThan(0, $collector->getTotalTime());
    }

}
