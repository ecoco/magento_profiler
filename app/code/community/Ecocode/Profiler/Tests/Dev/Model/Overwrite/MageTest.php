<?php


class Ecocode_Profiler_Tests_Dev_Model_Overwrite_MageTest
    extends TestHelper
{
    public function testGetLogger()
    {
        if (!class_exists('Monolog\Handler\TestHandler')) {
            $this->markTestSkipped(
                'Monolog not installed skipping.'
            );
        }
        $logger = Mage::getLogger();

        $this->assertInstanceOf('Ecocode_Profiler_Model_Logger', $logger);
        $this->assertEquals(Mage::getDefaultLogger(), $logger);

        $this->assertNotEquals(Mage::getDefaultLogger(), Mage::getLogger('new'));
    }

    public function testLog()
    {
        if (!class_exists('Monolog\Handler\TestHandler')) {
            $this->markTestSkipped(
                'Monolog not installed skipping.'
            );
        }
        $logger = Mage::getLogger();

        $this->assertCount(0, $logger->getLogs());

        Mage::log('test');
        Mage::log('test', Zend_Log::ALERT, 'error.log');

        $logs = $logger->getLogs();
        $this->assertCount(2, $logs);
        $lastLog = end($logs);

        $this->assertEquals(Ecocode_Profiler_Model_Logger::ALERT, $lastLog['priority']);
        $this->assertEquals('error', $lastLog['channel']);
    }
}
