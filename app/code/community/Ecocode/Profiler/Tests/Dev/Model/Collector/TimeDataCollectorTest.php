<?php

use Symfony\Component\Stopwatch\Stopwatch;

class Ecocode_Profiler_Tests_Dev_Model_Collector_TimeDataCollectorTest
    extends TestHelper
{

    public function testCollect()
    {
        $this->checkCanRunTest();

        $collector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_TimeDataCollector')
            ->setMethods(['getEventsFromProfiler'])
            ->getMock();

        $stopWatch = new Stopwatch();
        $stopWatch->openSection();
        $stopWatch->start('test-event');
        usleep(1000);
        $stopWatch->stop('test-event');
        $stopWatch->stopSection('test');

        $collector->method('getEventsFromProfiler')
            ->willReturn($stopWatch->getSectionEvents('test'));

        /** @var Ecocode_Profiler_Model_Collector_TimeDataCollector $collector */

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $this->assertEquals(0, $collector->getDuration());
        return $collector;
    }

    /**
     * @depends testCollect
     */
    public function testLateCollect(Ecocode_Profiler_Model_Collector_TimeDataCollector $collector)
    {
        $this->checkCanUseDepends();
        
        $collector->lateCollect();
        $this->assertGreaterThan(0, $collector->getStartTime());
        $this->assertGreaterThan(0, $collector->getDuration());
        $this->assertCount(2, $collector->getEvents());
    }


    protected function checkCanRunTest()
    {
        if (defined('Varien_Profiler::CATEGORY_SECTION') && @class_exists('Symfony\Component\Stopwatch\Stopwatch')) {
            return true;
        } else {
            $this->markTestSkipped('symfony stopwatch is not installed');
        }
    }

}
