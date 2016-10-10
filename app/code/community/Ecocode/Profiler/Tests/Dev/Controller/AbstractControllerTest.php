<?php

/**
 * Class Ecocode_Profiler_AbstractController
 */
class Ecocode_Profiler_Tests_Dev_Controller_AbstractControllerTest
    extends TestHelper
{
    protected function tearDown()
    {
        //reset mage as we did some config changes
        $this->resetMage();
        Mage::getConfig();
    }


    /**
     * @expectedException RuntimeException
     * @expectExceptionMessageRegExp ^You are not allowed to access this file
     */
    public function testAccessDenied()
    {
        $request    = new Mage_Core_Controller_Request_Http();
        $response   = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $controller = $this->getMockBuilder('Ecocode_Profiler_Controller_AbstractController')
            ->setMethods(['getApp'])
            ->setConstructorArgs([$request, $response])
            ->getMock();

        $app = new MageOriginal();
        $controller->method('getApp')
            ->willReturn($app);

        /** @var Ecocode_Profiler_Controller_AbstractController $controller */
        $controller->preDispatch();
    }

    public function testAccessAllow()
    {
        $request    = new Mage_Core_Controller_Request_Http();
        $response   = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $controller = $this->getMockBuilder('Ecocode_Profiler_Controller_AbstractController')
            ->setMethods(['getApp', '_rewrite'])
            ->setConstructorArgs([$request, $response])
            ->getMock();

        $app = new Ecocode_Profiler_Model_AppDev();
        $app->init('', 'store');
        $controller->method('getApp')
            ->willReturn($app);


        $controller->expects($this->once())
            ->method('_rewrite')
            ->willReturn(true);

        /** @var Ecocode_Profiler_Controller_AbstractController $controller */
        $controller->preDispatch();
        $this->assertConfigValue(
            $app->getConfig(),
            'disabled',
            'frontend/events/core_block_abstract_to_html_before/observers/ecocode_profiler_context/type'
        );
    }

    protected function assertConfigValue(Mage_Core_Model_Config $config, $expectValue, $configPath)
    {
        $this->assertEquals($expectValue, $config->getNode($configPath));
    }
}
