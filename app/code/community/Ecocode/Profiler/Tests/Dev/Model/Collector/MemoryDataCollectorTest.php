<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_MemoryDataCollectorTest
    extends TestHelper
{

    public function collectDataProvider()
    {
        return [
            ['0x1DD5650000', '128134217728'], //hexa //128MB
            ['01672531200000', '128134217728'], //octal //128MB
            ['-1', -1],
            ['1048576K', 1073741824],
            ['2048M', 2147483648],
            ['2G', 2147483648],
            ['1T', 1099511627776]
        ];
    }

    /**
     * @dataProvider collectDataProvider
     * @param $limit
     * @param $expectedBytes
     */
    public function testConvertToBytes($limit, $expectedBytes)
    {
        /** @var Ecocode_Profiler_Model_Collector_MemoryDataCollector $collector */
        $collector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_MemoryDataCollector')
            ->setMethods(['getCurrentMemoryLimit'])
            ->getMock();


        $collector->method('getCurrentMemoryLimit')
            ->willReturn($limit);

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $this->assertEquals($expectedBytes, $collector->getMemoryLimit());
    }

    public function testCollect()
    {
        /** @var Ecocode_Profiler_Model_Collector_MemoryDataCollector $collector */
        $collector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_MemoryDataCollector')
            ->setMethods(['getCurrentMemoryUsage'])
            ->getMock();


        $collector->method('getCurrentMemoryUsage')
            ->willReturn(100000);


        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $this->assertGreaterThan(100000, $collector->getMemory());
    }

    public function testLateCollect()
    {
        $collector = new Ecocode_Profiler_Model_Collector_MemoryDataCollector();

        $collector->lateCollect();
        $this->assertGreaterThan(0, $collector->getMemory());
    }
}
