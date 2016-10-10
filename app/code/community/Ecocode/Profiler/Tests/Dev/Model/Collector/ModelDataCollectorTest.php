<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_ModelDataCollectorTest
    extends TestHelper
{
    public function testCollect()
    {
        /** @var Ecocode_Profiler_Model_Collector_ModelDataCollector $collector */
        $collector = new Ecocode_Profiler_Model_Collector_ModelDataCollector();

        $callLog = [
            ['action' => 'load', 'trace_hash' => 'hash-1', 'time' => 10],
            ['action' => 'load', 'trace_hash' => 'hash-2', 'time' => 10],
            ['action' => 'load', 'trace_hash' => 'hash-3', 'time' => 10],
            ['action' => 'load', 'trace_hash' => 'hash-4'],
            ['action' => 'load', 'trace_hash' => 'hash-1', 'time' => 10],
            ['action' => 'load', 'trace_hash' => 'hash-1', 'time' => 10],
            ['action' => 'load', 'trace_hash' => 'hash-2', 'time' => 11],
            ['action' => 'save', 'time' => 10],
            ['action' => 'delete', 'time' => 10],
        ];

        $callLogProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_ModelDataCollector', 'callLog');
        $callLogProperty->setAccessible(true);
        $callLogProperty->setValue($collector, $callLog);

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $this->assertEquals(81, $collector->getTotalTime());
        $stats = $collector->getMetric();
        $this->assertEquals(5, $collector->getMetric('loop_load'));
        $this->assertEquals(7, $stats['load']);
        $this->assertEquals(1, $stats['save']);
        $this->assertEquals(1, $stats['delete']);

        return $collector;
    }

    /**
     * @param Ecocode_Profiler_Model_Collector_ModelDataCollector $collector
     *
     * @depends testCollect
     */
    public function testGetLoopCalls(Ecocode_Profiler_Model_Collector_ModelDataCollector $collector)
    {
        $loopCalls = $collector->getLoadLoopCalls();

        $this->assertCount(2, $loopCalls);
        $loopCall = reset($loopCalls);

        $this->assertEquals(3, $loopCall['count']);
        $this->assertEquals(30, $loopCall['total_time']);
    }

    public function testTrackModelLoad()
    {
        $model = Mage::getModel('catalog/product');
        /** @var Ecocode_Profiler_Model_Collector_ModelDataCollector $collector */
        $collector = new Ecocode_Profiler_Model_Collector_ModelDataCollector();


        $collector->trackModelLoad($this->getObserver(['object' => $model, 'time' => 100]));

        $callLogProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_ModelDataCollector', 'callLog');
        $callLogProperty->setAccessible(true);

        $callLog = $callLogProperty->getValue($collector);

        $this->assertCount(1, $callLog);

        $callLogItem = reset($callLog);

        $this->assertEquals('load', $callLogItem['action']);
        $this->assertEquals('Mage_Catalog_Model_Product', $callLogItem['class']);
        $this->assertEquals('catalog/product', $callLogItem['class_group']);
        $this->assertEquals('load', $callLogItem['action']);
        $this->assertEquals(100, $callLogItem['time']);
        $this->assertCount(0, $callLogItem['trace']);


    }

    public function testTrackModelDelete()
    {
        $model     = Mage::getModel('catalog/product');
        $collector = new Ecocode_Profiler_Model_Collector_ModelDataCollector();

        $collector->trackModelDelete($this->getObserver(['object' => $model]));

        $callLogProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_ModelDataCollector', 'callLog');
        $callLogProperty->setAccessible(true);

        $callLog = $callLogProperty->getValue($collector);

        $this->assertCount(1, $callLog);

        $callLogItem = reset($callLog);

        $this->assertEquals('delete', $callLogItem['action']);
        $this->assertCount(0, $callLogItem['trace']);
    }

    public function testTrackModelSave()
    {
        $model     = Mage::getModel('catalog/product');
        $collector = new Ecocode_Profiler_Model_Collector_ModelDataCollector();

        $collector->trackModelSave($this->getObserver(['object' => $model]));

        $callLogProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_ModelDataCollector', 'callLog');
        $callLogProperty->setAccessible(true);

        $callLog = $callLogProperty->getValue($collector);

        $this->assertCount(1, $callLog);

        $callLogItem = reset($callLog);

        $this->assertEquals('save', $callLogItem['action']);
        $this->assertCount(0, $callLogItem['trace']);
    }


    public function testCleanBacktrace()
    {
        $collector = new Ecocode_Profiler_Model_Collector_ModelDataCollector();

        $cleanBacktraceMethod = new ReflectionMethod('Ecocode_Profiler_Model_Collector_ModelDataCollector', 'cleanBacktrace');
        $cleanBacktraceMethod->setAccessible(true);

        $trace        = json_decode('[{"file":"xdebug:\/\/debug-eval(1) : eval()\'d code","line":1,"function":"getBacktrace","class":"Ecocode_Profiler_Model_Collector_AbstractDataCollector","type":"->"},{"file":"xdebug:\/\/debug-eval","line":1,"function":"eval"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/vendor\/ecocode\/magento_profiler\/app\/code\/community\/Ecocode\/Profiler\/Model\/Collector\/ModelDataCollector.php","line":157,"function":"track","class":"Ecocode_Profiler_Model_Collector_ModelDataCollector","type":"::"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/vendor\/ecocode\/magento_profiler\/app\/code\/community\/Ecocode\/Profiler\/Model\/Collector\/ModelDataCollector.php","line":140,"function":"track","class":"Ecocode_Profiler_Model_Collector_ModelDataCollector","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/vendor\/ecocode\/magento_profiler\/app\/code\/community\/Ecocode\/Profiler\/Model\/Collector\/ModelDataCollector.php","line":123,"function":"trackEvent","class":"Ecocode_Profiler_Model_Collector_ModelDataCollector","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Core\/Model\/App.php","line":1338,"function":"trackModelLoad","class":"Ecocode_Profiler_Model_Collector_ModelDataCollector","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/vendor\/ecocode\/magento_profiler\/app\/code\/community\/Ecocode\/Profiler\/Model\/AppDev.php","line":124,"function":"_callObserverMethod","class":"Mage_Core_Model_App","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Core\/Model\/App.php","line":1317,"function":"_callObserverMethod","class":"Ecocode_Profiler_Model_AppDev","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/vendor\/ecocode\/magento_profiler\/app\/code\/community\/Ecocode\/Profiler\/Model\/AppDev.php","line":91,"function":"dispatchEvent","class":"Mage_Core_Model_App","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/var\/cache\/Original_Mage_1.0.11-a6f179080788cdb98ede80fce0a470e1.php","line":456,"function":"dispatchEvent","class":"Ecocode_Profiler_Model_AppDev","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/vendor\/ecocode\/magento_profiler\/app\/code\/community\/Ecocode\/Profiler\/overwrite\/Mage.php","line":102,"function":"dispatchEvent","class":"MageOriginal","type":"::"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/vendor\/ecocode\/magento_profiler\/app\/code\/community\/Ecocode\/Profiler\/overwrite\/MageCoreModelResourceDbAbstract.php","line":24,"function":"dispatchDebugEvent","class":"Mage","type":"::"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Cms\/Model\/Resource\/Page.php","line":170,"function":"load","class":"Mage_Core_Model_Resource_Db_Abstract","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Core\/Model\/Abstract.php","line":225,"function":"load","class":"Mage_Cms_Model_Resource_Page","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Cms\/Model\/Page.php","line":113,"function":"load","class":"Mage_Core_Model_Abstract","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Cms\/Helper\/Page.php","line":74,"function":"load","class":"Mage_Cms_Model_Page","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Cms\/Helper\/Page.php","line":52,"function":"_renderPage","class":"Mage_Cms_Helper_Page","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Cms\/controllers\/IndexController.php","line":45,"function":"renderPage","class":"Mage_Cms_Helper_Page","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Core\/Controller\/Varien\/Action.php","line":418,"function":"indexAction","class":"Mage_Cms_IndexController","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Core\/Controller\/Varien\/Router\/Standard.php","line":250,"function":"dispatch","class":"Mage_Core_Controller_Varien_Action","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Core\/Controller\/Varien\/Front.php","line":172,"function":"match","class":"Mage_Core_Controller_Varien_Router_Standard","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/app\/code\/core\/Mage\/Core\/Model\/App.php","line":354,"function":"dispatch","class":"Mage_Core_Controller_Varien_Front","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/httpdocs\/var\/cache\/Original_Mage_1.0.11-a6f179080788cdb98ede80fce0a470e1.php","line":692,"function":"run","class":"Mage_Core_Model_App","type":"->"},{"file":"\/projects\/ecocode\/ecocode_profiler_test\/magento-1.8.1.0\/vendor\/ecocode\/magento_profiler\/dev.php","line":68,"function":"run","class":"MageOriginal","type":"::"}]', true);
        $cleanedTrace = $cleanBacktraceMethod->invoke($collector, $trace);


        $firstTraceItem = reset($cleanedTrace);
        $this->assertCount(12, $cleanedTrace);
        $this->assertEquals('load', $firstTraceItem['function']);
        $this->assertFalse(isset($firstTraceItem['object']));
        $this->assertFalse(isset($firstTraceItem['args']));
        $this->assertFalse(isset($firstTraceItem['type']));
    }

    public function shouldRemoveBacktraceProvider()
    {
        return [
            [[], true],
            [['class' => 'test'], true],
            [['class' => 'Mage_Cms_Model_Resource_Page', 'function' => 'load'], true],
            [['class' => 'Mage_Cms_Model_Resource_Page', 'function' => '_load'], true],
            [false, false],
            [['class' => 'Mage_Core_Model_Resource_Db_Abstract', 'function' => '_load'], true],
            [['class' => 'Mage_Core_Model_Resource_Db_Abstract', 'function' => 'load'], false],
            [['class' => 'Mage_Eav_Model_Entity_Abstract', 'function' => 'load'], false],
        ];
    }

    /**
     * @dataProvider shouldRemoveBacktraceProvider
     *
     * @param $data
     * @param $expect
     */
    public function testShouldRemoveBacktrace($data, $expect)
    {
        $collector = new Ecocode_Profiler_Model_Collector_ModelDataCollector();

        $shouldRemoveBacktraceMethod = new ReflectionMethod('Ecocode_Profiler_Model_Collector_ModelDataCollector', 'shouldRemoveBacktrace');
        $shouldRemoveBacktraceMethod->setAccessible(true);

        $shouldRemove = $shouldRemoveBacktraceMethod->invoke($collector, $data);

        $this->assertEquals($expect, $shouldRemove);
    }
}
