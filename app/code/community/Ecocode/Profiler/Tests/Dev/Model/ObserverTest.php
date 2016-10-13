<?php


class Ecocode_Profiler_Tests_Dev_Model_ObserverTest
    extends TestHelper
{
    protected function setUp()
    {
        parent::setUp();

        /** @var Ecocode_Profiler_Helper_Context $contextHelper */
        $this->contextHelper = $this->getMockBuilder('Ecocode_Profiler_Helper_Context')
            ->getMock();
    }


    public function testControllerFrontSendResponseBefore()
    {
        /** @var Ecocode_Profiler_Model_Profiler $profiler */
        $profiler = $this->getMockBuilder('Ecocode_Profiler_Model_Profiler')
            ->setMethods(['saveProfile', 'collect'])
            ->getMock();

        $profiler->method('collect')
            ->willReturn(new Ecocode_Profiler_Model_Profile('xxx'));


        $profiler->disable();
        $this->checkIfToolbarIsInjected($profiler);
        $profiler->enable();
        $observer = $this->checkIfToolbarIsInjected($profiler);

        return $observer;
    }

    public function testLinkTokenHeader()
    {
        $profiler = $this->getMockBuilder('Ecocode_Profiler_Model_Profiler')
            ->setMethods(['collect'])
            ->getMock();

        $frontController = $this->getMockBuilder('Mage_Core_Controller_Varien_Front')
            ->setMethods(['getResponse'])
            ->getMock();

        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $response->setHeader('X-Debug-Token', 'XXX');

        $frontController->method('getResponse')->willReturn($response);

        $eventObserver = $this->getObserver(['front' => $frontController]);

        $observer = $this->getMockBuilder('Ecocode_Profiler_Model_Observer')
            ->setMethods(['getProfiler', 'injectToolbar'])
            ->getMock();


        $observer->method('getProfiler')->willReturn($profiler);

        $profiler->enable();
        /** @var Ecocode_Profiler_Model_Observer $observer */
        $observer->controllerFrontSendResponseBefore($eventObserver);

        $headers         = $response->getHeaders();
        $tokenLinkHeader = false;
        foreach ($headers as $header) {
            if ($header['name'] === 'X-Debug-Token-Link') {
                $tokenLinkHeader = $header;
                break;
            }
        }
        $this->assertNotFalse($tokenLinkHeader);
    }

    public function checkIfToolbarIsInjected(Ecocode_Profiler_Model_Profiler $profiler)
    {
        $frontController = $this->getMockBuilder('Mage_Core_Controller_Varien_Front')
            ->setMethods(['getResponse'])
            ->getMock();

        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();

        $frontController->method('getResponse')->willReturn($response);

        $eventObserver = $this->getObserver(['front' => $frontController]);

        $observer = $this->getMockBuilder('Ecocode_Profiler_Model_Observer')
            ->setMethods(['getProfiler', 'injectToolbar'])
            ->getMock();


        $observer->method('getProfiler')->willReturn($profiler);

        if ($profiler->isEnabled()) {
            $observer->expects($this->once())
                ->method('injectToolbar');
        } else {
            $observer->expects($this->never())
                ->method('injectToolbar');
        }
        /** @var Ecocode_Profiler_Model_Observer $observer */
        $observer->controllerFrontSendResponseBefore($eventObserver);

        return [$profiler, $observer];
    }


    /**
     */
    public function testOnTerminate()
    {
        $observer = $this->getMockBuilder('Ecocode_Profiler_Model_Observer')
            ->setMethods(['getProfiler'])
            ->getMock();

        $profilesProperty = new ReflectionProperty('Ecocode_Profiler_Model_Observer', 'profiles');
        $profilesProperty->setAccessible(true);

        $profile = new Ecocode_Profiler_Model_Profile('token');

        $request           = new Mage_Core_Controller_Request_Http();
        $storage           = $profilesProperty->getValue($observer);
        $storage[$request] = $profile;


        $profiler = $this->getMockBuilder('Ecocode_Profiler_Model_Profiler')
            ->setMethods(['saveProfile'])
            ->getMock();

        $observer->method('getProfiler')->willReturn($profiler);

        $profiler->expects($this->once())
            ->method('saveProfile');

        $observer->onTerminate($this->getObserver([]));
    }


    public function testInjectToolbar()
    {
        $layout   = new Mage_Core_Model_Layout();
        $observer = $this->getMockBuilder('Ecocode_Profiler_Model_Observer')
            ->setMethods(['getLayout'])
            ->getMock();

        $observer->method('getLayout')->willReturn($layout);

        $injectToolbarMethod = new ReflectionMethod('Ecocode_Profiler_Model_Observer', 'injectToolbar');
        $injectToolbarMethod->setAccessible(true);

        $request  = new Mage_Core_Controller_Request_Http();
        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $response->setBody('<html><body>Some Content</body></html>');

        $injectToolbarMethod->invoke($observer, $response, $request);

        $this->assertContains('Some Content', $response->getBody());
        $this->assertContains('<!-- START of ecocode Web Debug Toolbar -->', $response->getBody());

        $this->assertNotFalse($layout->getBlock('profiler_toolbar'));
        $this->assertNotFalse($layout->getBlock('profiler_base_js'));
    }
}
