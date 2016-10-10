<?php

class Ecocode_Profiler_Tests_Dev_CacheControllerTest extends TestHelper
{
    protected function getMockedController($class, $request = null, $response = null, $app = null)
    {
        if (!$request instanceof Mage_Core_Controller_Request_Http) {
            $_request = new Mage_Core_Controller_Request_Http();
            if (is_array($request)) {
                $_request->setParams($request);

            }
            $request = $_request;

        }
        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $appMock  = $app ? $app : $this->getMockBuilder('Mage_Core_Model_App')->getMock();

        $controller = $this->getMockBuilder($class)
            ->setMethods(['getApp'])
            ->setConstructorArgs([$request, $response])
            ->getMock();

        $controller->method('getApp')
            ->willReturn($appMock);


        return $controller;
    }

    public function setUp()
    {
        parent::setUp();

        require_once Mage::getModuleDir('controllers', 'Ecocode_Profiler') . DS . 'CacheController.php';
    }


    public function testClearAction()
    {
        $appMock    = $this->getMockBuilder('Mage_Core_Model_App')->getMock();
        $controller = $this->getMockedController(
            'Ecocode_Profiler_CacheController',
            ['types' => join(',', ['test', 'block_html', 'config'])],
            null, $appMock
        );

        $cache = $this->getMockBuilder('Mage_Core_Model_Cache')->getMock();
        $appMock->method('getCacheInstance')
            ->willReturn($cache);

        $cache
            ->expects($this->exactly(3))
            ->method('cleanType');


        /** @var Ecocode_Profiler_CacheController $controller */
        $controller->clearAction();
    }

    public function testClearAllAction()
    {
        $appMock    = $this->getMockBuilder('Mage_Core_Model_App')->getMock();
        $controller = $this->getMockedController(
            'Ecocode_Profiler_CacheController',
            null, null, $appMock
        );

        $cache = $this->getMockBuilder('Mage_Core_Model_Cache')->getMock();
        $appMock->method('getCacheInstance')
            ->willReturn($cache);

        $cache
            ->expects($this->once())
            ->method('flush');

        /** @var Ecocode_Profiler_CacheController $controller */
        $controller->clearAllAction();
    }

    public function testEnableAction()
    {
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParams(['types' => 'config,block_html']);

        $response   = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $controller = $this->getMockBuilder('Ecocode_Profiler_CacheController')
            ->setMethods(['setCacheStatus'])
            ->setConstructorArgs([$request, $response])
            ->getMock();


        $controller
            ->expects($this->once())
            ->method('setCacheStatus')
            ->with(
                $this->equalTo(['config', 'block_html']),
                $this->equalTo(1)
            );

        /** @var Ecocode_Profiler_CacheController $controller */
        $controller->enableAction();
    }

    public function testDisableAction()
    {
        $request = new Mage_Core_Controller_Request_Http();
        $request->setParams(['types' => 'config,block_html']);

        $response   = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $controller = $this->getMockBuilder('Ecocode_Profiler_CacheController')
            ->setMethods(['setCacheStatus'])
            ->setConstructorArgs([$request, $response])
            ->getMock();


        $controller
            ->expects($this->once())
            ->method('setCacheStatus')
            ->with(
                $this->equalTo(['config', 'block_html']),
                $this->equalTo(0)
            );

        /** @var Ecocode_Profiler_CacheController $controller */
        $controller->disableAction();
    }

    public function testSetCacheStatus()
    {
        $types = [
            'config'     => new Varien_Object(['id' => 'config', 'status' => 0]),
            'block_html' => new Varien_Object(['id' => 'block_html', 'status' => 0])
        ];


        $cacheMock = $this->getMockBuilder('Mage_Core_Model_Cache')
            ->getMock();
        $cacheMock->method('getTypes')->willReturn($types);

        $appMock = $this->getMockBuilder('Mage_Core_Model_App')
            ->setMethods(['saveUseCache', 'getCacheInstance', 'getCache'])
            ->getMock();

        $appMock->method('getCacheInstance')->willReturn($cacheMock);
        $appMock->method('getCache')->willReturn($cacheMock);

        $this->initApp($appMock);
        $controller = $this->getMockedController(
            'Ecocode_Profiler_CacheController',
            null, null, $appMock
        );

        $status = 1;

        $setCacheStatusMethod = new ReflectionMethod('Ecocode_Profiler_CacheController', 'setCacheStatus');
        $setCacheStatusMethod->setAccessible(true);

        $types               = array_fill_keys(array_keys($types), 0);
        $types['block_html'] = 1;

        $appMock->expects($this->once())
            ->method('saveUseCache')
            ->with($this->equalTo($types));

        $setCacheStatusMethod->invoke($controller, ['block_html', 'test'], $status);
    }
}
