<?php

class Ecocode_Profiler_Tests_Dev_Helper_DataTest
    extends TestHelper
{
    public function testGetClassGroup()
    {
        $classGroup = 'catalog/category';
        $class      = Mage::getModel($classGroup);

        $helper = $this->getNewHelper();

        $this->assertEquals(
            $classGroup,
            $helper->getClassGroup($class)
        );
    }

    public function testUnknownGetClassGroup()
    {
        $classGroup = 'catalog/xxx';
        $class      = Mage::getModel($classGroup);

        $helper = $this->getNewHelper();

        $this->assertEquals(
            'unknown',
            $helper->getClassGroup($class)
        );
    }

    public function testGetCollectorUrl()
    {
        $helper        = $this->getNewHelper();
        $collector     = new Ecocode_Profiler_Model_Collector_MysqlDataCollector();
        $token         = 'xxx';
        $collectorName = $collector->getName();

        $url = $helper->getCollectorUrl($token, $collector);
        $url = str_replace(Mage::getBaseUrl(), '', $url);

        $this->assertEquals(
            sprintf('_profiler/index/panel/eco_token/%s/panel/%s/', $token, $collectorName),
            $url
        );
    }


    public function testCleanBacktraceBase()
    {
        $helper = $this->getNewHelper();
        $backtrace = [
            ['test' => 'asd', 'function' => 'test'],
            ['class' => 'Test_Class', 'function' => 'asd'],
            ['class' => 'Test_Class2', 'function' => 'asd'],
        ];

        //test empty error handling
        $this->assertCount(0, $helper->cleanBacktrace([]));

        $this->assertCount(2, $helper->cleanBacktrace($backtrace));
        $this->assertCount(1, $helper->cleanBacktrace($backtrace, ['Test_Class::asd']));
    }

    public function testCleanBacktraceInstanceOf()
    {
        $helper = $this->getNewHelper();
        $backtrace = [
            ['test' => 'asd', 'function' => 'test'],
            ['class' => 'Mage_Catalog_Model_Product', 'function' => 'asd'],
            ['class' => 'Ecocode_Profiler_Helper_Data', 'function' => 'asd'],
            ['class' => 'Mage_Catalog_Model_Product', 'function' => 'asd']
        ];

        foreach ($backtrace as &$trace) {
            if (isset($trace['class'])) {
                $trace['object'] = $this->getMockBuilder($trace['class'])->disableOriginalConstructor()->getMock();
            }
        }

        $this->assertCount(3, $helper->cleanBacktrace($backtrace));
        $this->assertCount(2, $helper->cleanBacktrace($backtrace, [], ['Varien_Object']));
        $this->assertCount(0, $helper->cleanBacktrace($backtrace, [], ['Varien_Object', 'Ecocode_Profiler_Helper_Data']));
    }

    /**
     * @return Ecocode_Profiler_Helper_Data
     */
    protected function getNewHelper()
    {
        return new Ecocode_Profiler_Helper_Data();
    }

}
