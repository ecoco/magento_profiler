<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_ConfigCollectorTest
    extends TestHelper
{

    public function testCollect()
    {
        $token    = 'XXX';
        $response = new Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp();
        $response->setHeader('X-Debug-Token', $token);
        $collector = new Ecocode_Profiler_Model_Collector_ConfigDataCollector();

        Mage::app()->setCurrentStore('admin');
        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            $response
        );


        $this->assertEquals(0, $collector->getStoreId());
        $this->assertEquals('admin', $collector->getStoreCode());
        $this->assertEquals('Admin', $collector->getStoreName());

        $this->assertEquals(0, $collector->getWebsiteId());
        $this->assertEquals('admin', $collector->getWebsiteCode());
        $this->assertEquals('Admin', $collector->getWebsiteName());

        $this->assertEquals($token, $collector->getToken());

        $this->assertFalse($collector->isDeveloperMode());

        $this->assertGreaterThan(1, count($collector->getMagentoModules()));

        $this->assertGreaterThan(1, count($collector->geModulesByState(true)));

        $this->assertNotNull($collector->getMagentoVersion());
        $this->assertNotNull($collector->getPhpVersion());

        $this->assertNotNull($collector->hasXDebug());
        $this->assertNotNull($collector->hasEAccelerator());
        $this->assertNotNull($collector->hasApc());
        $this->assertNotNull($collector->hasZendOpcache());
        $this->assertNotNull($collector->hasXCache());
        $this->assertNotNull($collector->hasWinCache());
        $this->assertNotNull($collector->hasAccelerator());
        $this->assertNotNull($collector->getSapiName());


    }
}
