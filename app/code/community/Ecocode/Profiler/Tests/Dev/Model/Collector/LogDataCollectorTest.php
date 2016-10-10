<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_LogDataCollectorTest
    extends TestHelper
{

    public function testCollect()
    {
        /** @var Ecocode_Profiler_Model_Collector_LogDataCollector $collector */
        $collector = new Ecocode_Profiler_Model_Collector_LogDataCollector();


        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $this->assertEmpty(0, $collector->getLogs());
        $this->assertEquals(0, $collector->getLogCount());
    }

    public function testLateCollect()
    {
        $logHandler = new Ecocode_Profiler_Model_Logger_DebugHandler();
        $logger     = new Ecocode_Profiler_Model_Logger('test', [$logHandler]);

        $collector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_LogDataCollector')
            ->setMethods(['getLogger'])
            ->getMock();

        $collector->method('getLogger')
            ->willReturn($logger);


        $logger->debug('test');
        $logger->debug('test');

        $context = [
            "type"  => 8192, "file"  => "path/xxxxCategoryController.php", "line"  => 43, "level" => 32767
        ];
        //simulate 3 times same error like in a loop
        $logger->info('iconv_set_encoding(): Use of iconv.internal_encoding is deprecated', $context);
        $logger->info('iconv_set_encoding(): Use of iconv.internal_encoding is deprecated', $context);
        $logger->info('iconv_set_encoding(): Use of iconv.internal_encoding is deprecated', $context);

        $logger->info('another deprecation', ['level' => E_USER_ERROR, 'type' => E_USER_DEPRECATED]);


        $logger->critical('Fatal Parse Error:', ['level' => E_ALL, 'type' => E_PARSE]);

        /** @var Ecocode_Profiler_Model_Collector_LogDataCollector $collector */
        $collector->lateCollect();



        $logsByPriorities = $collector->getPriorities();

        $this->assertEquals(2, $logsByPriorities[Ecocode_Profiler_Model_Logger::DEBUG]['count']);
        $this->assertEquals(4, $logsByPriorities[Ecocode_Profiler_Model_Logger::INFO]['count']);
        $this->assertEquals(1, $logsByPriorities[Ecocode_Profiler_Model_Logger::CRITICAL]['count']);


        $this->assertEquals(0, $collector->countScreams());
        $this->assertEquals(1, $collector->countErrors());
        $this->assertEquals(4, $collector->countDeprecations());

    }
}
