<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_LayoutDataCollectorTest
    extends TestHelper
{
    public function testBeforeToHtmlNoParent()
    {
        $start     = microtime(true);
        $observer  = new Varien_Event_Observer();
        $event     = new Varien_Event();
        $testBlock = new Mage_Core_Block_Template();

        $observer->setData('event', $event);
        $event->setData('block', $testBlock);

        $collector = new Ecocode_Profiler_Model_Collector_LayoutDataCollector();

        $collector->beforeToHtml($observer);

        $id = $testBlock->getData('profiler_id');
        $this->assertNotNull($id);

        $renderLogProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_LayoutDataCollector', 'renderLog');
        $renderLogProperty->setAccessible(true);
        $log = $renderLogProperty->getValue($collector);

        $this->assertTrue(isset($log[$id]));

        $logEntry = $log[$id];
        $this->assertFalse($logEntry['parent_id']);
        $this->assertGreaterThan($start, $logEntry['start_render']);
        $this->assertEquals(spl_object_hash($testBlock), $logEntry['hash']);
        $this->assertCount(0, $logEntry['children']);
        $this->assertFalse($logEntry['parent_id']);
    }

    public function testBeforeToHtmlWithParent()
    {
        $observer    = new Varien_Event_Observer();
        $event       = new Varien_Event();
        $childBlock  = new Mage_Core_Block_Template();
        $parentBlock = new Mage_Core_Block_Template();

        $parentBlock->setChild('test-child', $childBlock);

        $observer->setData('event', $event);
        $event->setData('block', $parentBlock);

        $collector = new Ecocode_Profiler_Model_Collector_LayoutDataCollector();

        $collector->beforeToHtml($observer);

        $event->setData('block', $childBlock);
        $collector->beforeToHtml($observer);

        $parentId = $parentBlock->getData('profiler_id');
        $childId  = $childBlock->getData('profiler_id');

        $this->assertNotNull($parentId);
        $this->assertNotNull($childId);

        $renderLogProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_LayoutDataCollector', 'renderLog');
        $renderLogProperty->setAccessible(true);
        $log = $renderLogProperty->getValue($collector);


        $this->assertCount(2, $log);
        $this->assertTrue(isset($log[$parentId]));
        $this->assertTrue(isset($log[$childId]));

        $parentLogEntry = $log[$parentId];
        $childLogEntry  = $log[$childId];

        $this->assertFalse($parentLogEntry['parent_id']);
        $this->assertEquals($parentId, $childLogEntry['parent_id']);

        $this->assertCount(1, $parentLogEntry['children']);
    }

    public function testBeforeToHtmlWidget()
    {
        $observer = new Varien_Event_Observer();
        $event    = new Varien_Event();

        $parentBlock      = new Mage_Core_Block_Template();
        $widgetBlock      = new Mage_Catalog_Block_Product_Widget_New();
        $widgetChildBlock = new Mage_Core_Block_Template();

        $widgetBlock->setChild('widget-child', $widgetChildBlock);

        $observer->setData('event', $event);


        $collector = new Ecocode_Profiler_Model_Collector_LayoutDataCollector();

        $blocks = [$parentBlock, $widgetBlock, $widgetChildBlock];

        foreach ($blocks as $block) {
            $event->setData('block', $block);
            $collector->beforeToHtml($observer);
        }

        $renderLogProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_LayoutDataCollector', 'renderLog');
        $renderLogProperty->setAccessible(true);
        $log = $renderLogProperty->getValue($collector);


        $this->assertCount(3, $log);

        $this->assertEquals(
            $parentBlock->getData('profiler_id'),
            $log[$widgetBlock->getData('profiler_id')]['parent_id']
        );
    }


    public function testAfterToHtml()
    {
        $collector = new Ecocode_Profiler_Model_Collector_LayoutDataCollector();

        $observer  = new Varien_Event_Observer();
        $event     = new Varien_Event();
        $testBlock = new Mage_Core_Block_Template();

        $testBlock->setTemplate('test.html.php');

        $observer->setData('event', $event);
        $event->setData('block', $testBlock);


        $collector->beforeToHtml($observer);
        $collector->afterToHtml($observer);

        $id = $testBlock->getData('profiler_id');
        $this->assertNotNull($id);

        $renderLogProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_LayoutDataCollector', 'renderLog');
        $renderLogProperty->setAccessible(true);
        $log = $renderLogProperty->getValue($collector);

        $blockData = $log[$id];


        $this->assertNotNull($blockData['stop_render']);
        $this->assertEquals($blockData['stop_render'] - $blockData['start_render'], $blockData['render_time_incl']);
        $this->assertEquals('test.html.php', $blockData['template']);

    }


    public function testCollect()
    {
        /** @var Ecocode_Profiler_Model_Collector_LayoutDataCollector $collector */
        $collector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_LayoutDataCollector')
            ->setMethods(['getLayout'])
            ->getMock();

        /** @var Mage_Core_Model_Layout $layout */
        $layout = new Mage_Core_Model_Layout();

        $collector->method('getLayout')
            ->willReturn($layout);

        $observer = new Varien_Event_Observer();
        $event    = new Varien_Event();

        $childBlock       = new Mage_Core_Block_Template();
        $parentBlock      = new Mage_Core_Block_Template();
        $notRenderedBlock = new Mage_Core_Block_Template();

        $layout->getUpdate()->addHandle('default');
        $layout->getUpdate()->addHandle('test');
        $layout->addBlock($childBlock, 'root');
        $layout->addBlock($parentBlock, 'block-2');
        $layout->addBlock($notRenderedBlock, 'block-3');

        $layout->addOutputBlock('root');
        $blocks = [$parentBlock, $childBlock];

        $observer->setData('event', $event);

        $parentBlock->setChild('test-child', $childBlock);

        foreach ($blocks as $block) {
            $event->setData('block', $block);
            $collector->beforeToHtml($observer);
        }

        foreach (array_reverse($blocks) as $block) {
            $event->setData('block', $block);
            $collector->afterToHtml($observer);
        }

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $this->assertCount(2, $collector->getLayoutHandles());
        $this->assertEquals(3, $collector->getBlocksCreatedCount());
        $this->assertEquals(2, $collector->getBlocksRenderedCount());

        $blocksNotRendered = $collector->getBlocksNotRendered();
        $this->assertCount(1, $blocksNotRendered);
        $this->assertEquals('block-3', $blocksNotRendered[0]['name']);
        $this->assertGreaterThan(0, $collector->getTotalRenderTime());
    }

    /**
     * @return Ecocode_Profiler_Model_Collector_LayoutDataCollector
     */
    public function getMockedCollector()
    {
        $collectorMock = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_LayoutDataCollector')
            ->setMethods(['getLayout'])
            ->getMock();


        return $collectorMock;
    }
}
