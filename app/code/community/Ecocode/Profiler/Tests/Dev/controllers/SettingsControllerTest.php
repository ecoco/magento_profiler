<?php

class Ecocode_Profiler_Tests_Dev_SettingsControllerTest extends TestControllerHelper
{
    public function setUp()
    {
        parent::setUp();

        require_once Mage::getModuleDir('controllers', 'Ecocode_Profiler') . DS . 'SettingsController.php';
    }


    public function testSaveAction()
    {
        $controller = $this->getMockedController(
            'Ecocode_Profiler_SettingsController',
            ['key' => 'test-key', 'value' => 'test-value'],
            null,
            ['getConfig']
        );

        $config = $this->getMockBuilder('Ecocode_Profiler_Model_Config')->getMock();
        $controller->method('getConfig')->willReturn($config);

        $config
            ->expects($this->exactly(1))
            ->method('saveValue')
            ->with(
                $this->equalTo('test-key'),
                $this->equalTo('test-value')
            );

        /** @var Ecocode_Profiler_SettingsController $controller */
        $controller->saveAction();
    }

    public function testSaveCollectorAction()
    {
        $controller = $this->getMockedController(
            'Ecocode_Profiler_SettingsController',
            ['key' => 'test-key', 'value' => 'test-value', 'collector' => 'test-collector'],
            null,
            ['getConfig', 'getProfiler']
        );

        $collector = new Ecocode_Profiler_Model_Collector_TimeDataCollector();
        $profiler  = $this->getMockBuilder('Ecocode_Profiler_Model_Profiler')->getMock();
        $profiler->method('getDataCollector')->with('test-collector')->willReturn($collector);
        $controller->method('getProfiler')->willReturn($profiler);

        $config = $this->getMockBuilder('Ecocode_Profiler_Model_Config')->getMock();
        $controller->method('getConfig')->willReturn($config);

        $config
            ->expects($this->exactly(1))
            ->method('saveCollectorValue')
            ->with(
                $this->equalTo($collector),
                $this->equalTo('test-key'),
                $this->equalTo('test-value')
            );

        /** @var Ecocode_Profiler_SettingsController $controller */
        $controller->saveAction();
    }

    public function testResetAction()
    {
        $controller = $this->getMockedController(
            'Ecocode_Profiler_SettingsController',
            ['key' => 'test-key'],
            null,
            ['getConfig']
        );

        $config = $this->getMockBuilder('Ecocode_Profiler_Model_Config')->getMock();
        $config->method('getValue')->willReturn('default-value');
        $controller->method('getConfig')->willReturn($config);

        $config
            ->expects($this->exactly(1))
            ->method('deleteValue')
            ->with(
                $this->equalTo('test-key')
            );

        /** @var Ecocode_Profiler_SettingsController $controller */
        $controller->resetAction();

        $this->assertEquals(
            ['value' => 'default-value'],
            json_decode($controller->getResponse()->getBody(), true)
        );
    }


    public function testResetCollectorAction()
    {
        $controller = $this->getMockedController(
            'Ecocode_Profiler_SettingsController',
            ['key' => 'test-key', 'collector' => 'test-collector'],
            null,
            ['getConfig', 'getProfiler']
        );

        $collector = new Ecocode_Profiler_Model_Collector_TimeDataCollector();
        $profiler  = $this->getMockBuilder('Ecocode_Profiler_Model_Profiler')->getMock();
        $profiler->method('getDataCollector')->with('test-collector')->willReturn($collector);
        $controller->method('getProfiler')->willReturn($profiler);

        $config = $this->getMockBuilder('Ecocode_Profiler_Model_Config')->getMock();
        $config->method('getValue')->willReturn('default-value');
        $controller->method('getConfig')->willReturn($config);

        $config
            ->expects($this->exactly(1))
            ->method('deleteCollectorValue')
            ->with(
                $this->equalTo($collector),
                $this->equalTo('test-key')
            );

        /** @var Ecocode_Profiler_SettingsController $controller */
        $controller->resetAction();


        $this->assertEquals(
            ['value' => 'default-value'],
            json_decode($controller->getResponse()->getBody(), true)
        );
    }

}
