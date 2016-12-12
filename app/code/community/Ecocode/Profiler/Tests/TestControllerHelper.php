<?php

class TestControllerHelper extends TestHelper
{
    protected function getMockedController(
        $class,
        $request = null,
        Mage_Core_Controller_Response_Http $response = null,
        $methods = []
    )
    {
        if (!$request instanceof Mage_Core_Controller_Request_Http) {
            $_request = new Mage_Core_Controller_Request_Http();
            if (is_array($request)) {
                $_request->setParams($request);

            }
            $request = $_request;

        }
        if (!$response) {
            $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        }

        $methods[] = 'getApp';
        $appMock   = $this->getMockBuilder('Mage_Core_Model_App')->getMock();

        $controller = $this->getMockBuilder($class)
            ->setMethods($methods)
            ->setConstructorArgs([$request, $response])
            ->getMock();

        $controller->method('getApp')
            ->willReturn($appMock);


        return $controller;
    }
}
