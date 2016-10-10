<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_CacheDataCollectorTest
    extends TestHelper
{

    public function testCollect()
    {
        $collector = $this->getMockedCollector();

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $expectCacheList = json_decode('[{"id":"config","cache_type":"Configuration","description":"System(config.xml, local.xml) and modules configuration files(config.xml).","tags":"CONFIG","status":1},{"id":"layout","cache_type":"Layouts","description":"Layout building instructions.","tags":"LAYOUT_GENERAL_CACHE_TAG","status":1},{"id":"block_html","cache_type":"Blocks HTML output","description":"Page blocks HTML.","tags":"BLOCK_HTML","status":1},{"id":"translate","cache_type":"Translations","description":"Translation files.","tags":"TRANSLATE","status":1},{"id":"collections","cache_type":"Collections Data","description":"Collection data files.","tags":"COLLECTION_DATA","status":1},{"id":"eav","cache_type":"EAV types and attributes","description":"Entity types declaration cache.","tags":"EAV","status":1},{"id":"config_api","cache_type":"Web Services Configuration","description":"Web Services definition files (api.xml).","tags":"CONFIG_API","status":1},{"id":"config_api2","cache_type":"Web Services Configuration","description":"Web Services definition files (api2.xml).","tags":"CONFIG_API2","status":1}]', true);

        $this->assertEquals('Ecocode_Profiler_Tests_Dev_Fixtures_DummyCacheBackend', $collector->getBackendName());
        $this->assertEquals(['test_option' => 1], $collector->getBackendOptions());
        $this->assertEquals($expectCacheList, $collector->getCacheList());
        $this->assertEmpty($collector->getCacheCalls());
    }

    public function testLateCollect()
    {
        $logs = [
            ['action' => 'clear', 'id' => 'unknown', 'time' => 0.001],
            ['action' => 'load', 'id' => 'core_cache_options1', 'hit' => true, 'time' => 0.005],
            ['action' => 'load', 'id' => 'core_cache_options2', 'hit' => true, 'time' => 0.005],
            ['action' => 'load', 'id' => 'core_cache_options_miss1', 'hit' => false, 'time' => 0.005],
            ['action' => 'load', 'id' => 'core_cache_options_miss2', 'hit' => false, 'time' => 0.005],
            ['action' => 'save', 'id' => 'config_global_admin', 'tags' => ['CONFIG'], 'life_time' => null, 'time' => 0.005],
        ];

        $collector = $this->getMockedCollector([], $logs);


        $collector->lateCollect();

        $this->assertEquals([
            'total' => 6,
            'hit'   => 2,
            'miss'  => 2,
            'save'  => 1,
        ], $collector->getStats());
        
        $this->assertEquals(6, $collector->getStats('total'));
        $this->assertEquals(0.026, $collector->getTotalTime());
    }

    public function getMockedCollector($cacheTypes = [], $log = [])
    {
        $cacheBackend = new Ecocode_Profiler_Tests_Dev_Fixtures_DummyCacheBackend(['test_option' => 1]);

        $cacheMock = $this->getMockBuilder('Ecocode_Profiler_Model_Core_Cache')
            ->setMethods(['getTypes', 'getBackend', 'getLog'])
            ->getMock();

        $cacheTypes    = $cacheTypes ? $cacheTypes : json_decode('[{"id":"config","cache_type":"Configuration","description":"System(config.xml, local.xml) and modules configuration files(config.xml).","tags":"CONFIG","status":1},{"id":"layout","cache_type":"Layouts","description":"Layout building instructions.","tags":"LAYOUT_GENERAL_CACHE_TAG","status":1},{"id":"block_html","cache_type":"Blocks HTML output","description":"Page blocks HTML.","tags":"BLOCK_HTML","status":1},{"id":"translate","cache_type":"Translations","description":"Translation files.","tags":"TRANSLATE","status":1},{"id":"collections","cache_type":"Collections Data","description":"Collection data files.","tags":"COLLECTION_DATA","status":1},{"id":"eav","cache_type":"EAV types and attributes","description":"Entity types declaration cache.","tags":"EAV","status":1},{"id":"config_api","cache_type":"Web Services Configuration","description":"Web Services definition files (api.xml).","tags":"CONFIG_API","status":1},{"id":"config_api2","cache_type":"Web Services Configuration","description":"Web Services definition files (api2.xml).","tags":"CONFIG_API2","status":1}]', true);
        $collectorMock = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_CacheDataCollector')
            ->setMethods(['getCacheInstance', 'getCache'])
            ->getMock();

        $types = [];
        foreach ($cacheTypes as $typeData) {
            $id         = $typeData['id'];
            $types[$id] = new Varien_Object($typeData);
        }

        $cacheMock
            ->method('getTypes')
            ->willReturn($types);

        $cacheMock
            ->method('getBackend')
            ->willReturn($cacheBackend);

        $cacheMock
            ->method('getLog')
            ->willReturn($log);


        $collectorMock->method('getCacheInstance')->willReturn($cacheMock);
        $collectorMock->method('getCache')->willReturn($cacheMock);


        return $collectorMock;
    }
}
