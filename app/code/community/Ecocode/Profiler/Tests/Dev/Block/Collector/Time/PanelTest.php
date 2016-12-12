<?php

class Ecocode_Profiler_Tests_Dev_Block_Collector_Time_PanelTest
    extends TestHelper
{
    protected $block;

    public function setUp()
    {
        $timeDataCollector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_TimeDataCollector')
            ->setMethods(['isVarienProfiler', 'getProfile', 'getEvents'])
            ->getMock();

        $memoryDataCollector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_MemoryDataCollector')
            ->setMethods(['getMemory'])
            ->getMock();

        $profile = new Ecocode_Profiler_Model_Profile('xxx');
        $profile->addCollector($timeDataCollector);
        $profile->addCollector($memoryDataCollector);

        $memoryDataCollector->method('getMemory')->willReturn(100);

        $block = $this->getMockBuilder('Ecocode_Profiler_Block_Collector_Time_Panel')
            ->setMethods(['getProfile'])
            ->getMock();

        $block->method('getProfile')->willReturn($profile);

        /** @var Ecocode_Profiler_Block_Collector_Time_Panel $block */
        $block
            ->setCollector($timeDataCollector)
            ->setTemplate('ecocode_profiler/collector/time/panel.phtml');

        $this->block = $block;
    }

    public function testRenderVarienProfiler()
    {
        $block = $this->block;

        $block->getCollector()->method('isVarienProfiler')->willReturn(true);


        $html = $block->toHtml();

        $this->assertContains(
            'No detailed performance data is available as the "symfony/stopwatch" is not installed please check the composer.json',
            $html
        );
    }

    public function testRenderEcocodeProfiler()
    {
        $block = $this->block;

        $block->getCollector()->method('getEvents')->willReturn([]);
        $block->getCollector()->method('isVarienProfiler')->willReturn(false);


        $html = $block->toHtml();

        $this->assertContains(
            'No detailed performance data is available. Maybe "symfony/stopwatch" was not available during this run',
            $html
        );
    }

    public function testRender()
    {
        if (!defined('Varien_Profiler::CATEGORY_SECTION') || @!class_exists('Symfony\Component\Stopwatch\Stopwatch')) {
            $this->markTestSkipped('symfony stopwatch is not installed');
        }

        $block = $this->block;

        $stopwatch = new Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->openSection();
        $stopwatch->start('test');
        $stopwatch->stop('test');
        $stopwatch->stopSection('mage');

        $block->getCollector()->method('getEvents')->willReturn($stopwatch->getSectionEvents('mage'));
        $block->getCollector()->method('isVarienProfiler')->willReturn(false);


        $html = $block->toHtml();

        $this->assertContains(
            'Execution timeline',
            $html
        );
    }
}
