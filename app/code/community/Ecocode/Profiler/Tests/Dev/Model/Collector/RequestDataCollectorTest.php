<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_RequestDataCollectorTest
    extends TestHelper
{
    protected $request;
    protected $response;
    /** @var  Ecocode_Profiler_Model_Collector_RequestDataCollector */
    protected $collector;

    public function setUp()
    {
        $request = $this->getMockBuilder('Mage_Core_Controller_Request_Http')
            ->setMethods(['getMethod', 'getServer', 'getCookie'])
            ->getMock();

        $serverData = [
            'REDIRECT_STATUS'      => 200,
            'HTTP_HOST'            => 'profiler.test',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
        ];
        $cookieData = [
            'frontend' => 'session-key'
        ];
        $request->method('getMethod')->willReturn('GET');
        $request->method('getServer')->willReturn($serverData);
        $request->method('getCookie')->willReturn($cookieData);

        /** @var Mage_Core_Controller_Request_Http $request */
        $request->setRequestUri('/dev.php/electronics.html');
        $request->setBaseUrl('/dev.php');
        $request->setPathInfo();

        $request->setRequestUri('/dev.php/catalog/category/view/id/13');
        $request->setPathInfo('catalog/category/view/id/13');
        $request->setParams(['id' => '13']);
        $request->setRouteName('catalog');
        $request->setControllerName('category');
        $request->setActionName('view');


        /** @var Mage_Core_Controller_Request_Http $request */

        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $response->setHttpResponseCode(201);
        $response->setHeader('content-Type', 'application/json');
        $response->setHeader('X-DEBUG-TOKEN', 'XXX');

        $this->request  = $request;
        $this->response = $response;

        $this->collector = $collector = new Ecocode_Profiler_Model_Collector_RequestDataCollector();
    }

    public function testCollect()
    {
        $collector = $this->collector;
        $collector->collect(
            $this->request,
            $this->response
        );

        $this->assertEquals('GET', $collector->getMethod());
        $this->assertEquals('application/json', $collector->getContentType());
        $this->assertEquals(201, $collector->getStatusCode());
        $this->assertEquals(Ecocode_Profiler_Model_Collector_RequestDataCollector::$statusTexts[201], $collector->getStatusText());
        $this->assertEquals(
            ['host' => ['profiler.test'], 'accept-encoding' => ['gzip, deflate, sdch']],
            $collector->getRequestHeaders()->all()
        );

        $this->assertEquals(
            [
                'REDIRECT_STATUS'      => 200,
                'HTTP_HOST'            => 'profiler.test',
                'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
            ],
            $collector->getRequestServer()->all()
        );

        $this->assertEquals(
            ['frontend' => 'session-key'],
            $collector->getRequestCookies()->all()
        );

        $this->assertEquals('catalog/category/view/id/13', $collector->getPathInfo());
        //no post data
        $this->assertEmpty($collector->getRequestRequest());

        //no get data
        $this->assertEmpty($collector->getRequestQuery());

        //no redirect
        $this->assertEmpty($collector->getRedirect());

        $this->assertCount(4, $collector->getController());

        $this->assertInstanceOf('Ecocode_Profiler_Model_Http_ResponseHeaderBag', $collector->getResponseHeaders());

        return $collector;
    }

    /**
     * @depends testCollect
     */
    public function testCollectRedirectData()
    {
        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $response->setRedirect(Mage::getUrl('test'));

        $collector = $this->collector;
        $collector->collect(
            $this->request,
            $response
        );
        $parseControllerMethod = new ReflectionMethod($collector, 'collectRedirectData');
        $parseControllerMethod->setAccessible(true);

        $parseControllerMethod->invoke($collector, $this->request, $response);

        $this->assertEquals(false, $collector->getRedirect());

        $request = new Mage_Core_Controller_Request_Http();
        $request->setParam('_redirected', true);

        $parseControllerMethod->invoke($collector, $request, $response);

        $redirectData = $collector->getRedirect();
        //unset system depended variables
        unset($redirectData['controller']['line']);
        unset($redirectData['controller']['file']);

        $this->assertEquals(302, $redirectData['status_code']);
    }

    public function testCollectParameters()
    {
        $collector = new Ecocode_Profiler_Model_Collector_RequestDataCollector();


        $request = $this->getMockBuilder('Mage_Core_Controller_Request_Http')
            ->setMethods(['getQuery', 'getPost'])
            ->getMock();

        $request->method('getQuery')->willReturn(['q' => 'search']);
        $request->method('getPost')->willReturn(['key' => 'data']);

        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $collector->collect($request, $response);

        //no post data
        $this->assertEquals(['key' => 'data'], $collector->getRequestRequest()->all());

        //no get data
        $this->assertEquals(['q' => 'search'], $collector->getRequestQuery()->all());
    }


    public function testParseController()
    {
        $collector = new Ecocode_Profiler_Model_Collector_RequestDataCollector();

        $controller = new Mage_Core_Controller_Varien_Front();

        $parseControllerMethod = new ReflectionMethod('Ecocode_Profiler_Model_Collector_RequestDataCollector', 'parseController');
        $parseControllerMethod->setAccessible(true);


        $controllerData = $parseControllerMethod->invoke($collector, false);
        $this->assertEquals('n/a', $controllerData);

        $controllerData = $parseControllerMethod->invoke($collector, $controller);

        $this->assertEquals('Mage_Core_Controller_Varien_Front', $controllerData['class']);
    }


    public function testParseControllerWithAction()
    {
        $collector = new Ecocode_Profiler_Model_Collector_RequestDataCollector();

        $controller = new Mage_Core_Controller_Varien_Front();

        $request  = new Mage_Core_Controller_Request_Http();
        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();

        $request->setActionName('index');

        require 'Mage/Cms/controllers/IndexController.php';
        $action = new Mage_Cms_IndexController(
            $request,
            $response
        );

        $controller->setData('action', $action);

        $parseControllerMethod = new ReflectionMethod('Ecocode_Profiler_Model_Collector_RequestDataCollector', 'parseController');
        $parseControllerMethod->setAccessible(true);


        $controllerData = $parseControllerMethod->invoke($collector, $controller);

        $this->assertEquals('Mage_Cms_IndexController', $controllerData['class']);
        $this->assertEquals('indexAction', $controllerData['method']);
    }

    public function testDetectStatusCode()
    {
        $collector = new Ecocode_Profiler_Model_Collector_RequestDataCollector();

        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $response->setHttpResponseCode(200);

        $detectStatusCodeMethod = new ReflectionMethod('Ecocode_Profiler_Model_Collector_RequestDataCollector', 'detectStatusCode');
        $detectStatusCodeMethod->setAccessible(true);

        $this->assertEquals(200, $detectStatusCodeMethod->invoke($collector, $response));

        //magento does not set the status 100% correct, sometime only the header is present
        $response->setHeader('Http/1.1', '404 Not Found');
        $this->assertEquals(404, $detectStatusCodeMethod->invoke($collector, $response));
    }

    /**
     * @depends testCollect
     */
    public function testCollectRequestAttributes(Ecocode_Profiler_Model_Collector_RequestDataCollector $collector)
    {
        $this->assertInstanceOf('Ecocode_Profiler_Model_Http_ParameterBag', $collector->getRequestAttributes());
        $this->assertEquals('/electronics.html', $collector->getRequestString());
        $this->assertEquals('/dev.php/catalog/category/view/id/13', $collector->getRequestUri());
        $this->assertEquals('catalog', $collector->getModuleName());
        $this->assertEquals('category', $collector->getControllerName());
        $this->assertEquals('view', $collector->getActionName());
        $this->assertEquals('catalog_category_view', $collector->getRoute());
        $this->assertEquals('catalog', $collector->getRouteName());
        $this->assertEquals(['id' => '13'], $collector->getRouteParams());
    }
}
