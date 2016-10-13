<?php

use Monolog\Handler\TestHandler;

class Ecocode_Profiler_Tests_Dev_Model_LoggerTest
    extends TestHelper
{
    protected function setUp()
    {
        if (!class_exists('Monolog\Handler\TestHandler')) {
            $this->markTestSkipped(
                'Monolog not installed skipping.'
            );
        }
    }

    public function testMageLog()
    {
        $handler = new Ecocode_Profiler_Model_Logger_DebugHandler();
        $logger  = new Ecocode_Profiler_Model_Logger(__METHOD__, [$handler]);

        $this->assertTrue($logger->mageLog(Zend_Log::ERR, 'error message'));
        $logs = $logger->getLogs();

        $this->assertCount(1, $logs);
        $log = reset($logs);
        $this->assertEquals(Ecocode_Profiler_Model_Logger::ERROR, $log['priority']);
    }

    public function testGetLogsWithDebugHandler()
    {
        $handler = new Ecocode_Profiler_Model_Logger_DebugHandler();
        $logger  = new Ecocode_Profiler_Model_Logger(__METHOD__, [$handler]);

        $this->assertTrue($logger->error('error message'));
        $this->assertSame(1, count($logger->getLogs()));
    }

    public function testGetLogsWithoutDebugHandler()
    {
        $handler = new TestHandler();
        $logger  = new Ecocode_Profiler_Model_Logger(__METHOD__, [$handler]);

        $this->assertTrue($logger->error('error message'));
        $this->assertSame([], $logger->getLogs());
    }

    public function testCountErrorsWithDebugHandler()
    {
        $handler = new Ecocode_Profiler_Model_Logger_DebugHandler();
        $logger  = new Ecocode_Profiler_Model_Logger(__METHOD__, [$handler]);

        $this->assertTrue($logger->debug('test message'));
        $this->assertTrue($logger->info('test message'));
        $this->assertTrue($logger->notice('test message'));
        $this->assertTrue($logger->warning('test message'));

        $this->assertTrue($logger->error('test message'));
        $this->assertTrue($logger->critical('test message'));
        $this->assertTrue($logger->alert('test message'));
        $this->assertTrue($logger->emergency('test message'));

        $this->assertSame(4, $logger->countErrors());
    }

    public function testGetLogs()
    {
        $logger = new Ecocode_Profiler_Model_Logger('test');
        $logger->pushHandler(new Ecocode_Profiler_Model_Logger_DebugHandler());

        $logger->addInfo('test');
        $this->assertCount(1, $logger->getLogs());
        list($record) = $logger->getLogs();

        $this->assertEquals('test', $record['message']);
        $this->assertEquals(Ecocode_Profiler_Model_Logger::INFO, $record['priority']);
    }

    public function testCountErrorsWithoutDebugHandler()
    {
        $handler = new TestHandler();
        $logger  = new Ecocode_Profiler_Model_Logger(__METHOD__, [$handler]);

        $this->assertTrue($logger->error('error message'));
        $this->assertSame(0, $logger->countErrors());
    }
}
