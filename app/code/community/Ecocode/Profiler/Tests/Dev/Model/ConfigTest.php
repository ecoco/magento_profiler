<?php


class Ecocode_Profiler_Tests_Dev_Model_ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ecocode_Profiler_Model_Config
     */
    private $config;

    private $tmpUserConfig;

    protected function setUp()
    {
        $config = $this->getMockBuilder('Ecocode_Profiler_Model_Config')
            ->setMethods(['getBaseConfigFile', 'getUserConfigFile'])
            ->getMock();

        /** @var PHPUnit_Framework_MockObject_MockObject $config */
        $fixtureDir = __DIR__ . DS . '..' . DS . 'Fixtures' . DS;

        $userConfigFile      = $fixtureDir . '.profiler.conf.json';
        $this->tmpUserConfig = $userConfigFile . '.tmp';
        copy($userConfigFile, $this->tmpUserConfig);
        $config->method('getBaseConfigFile')->willReturn($fixtureDir . 'config.json');
        $config->method('getUserConfigFile')->willReturn($this->tmpUserConfig);

        $this->config = $config;
    }

    protected function tearDown()
    {
        unlink($this->tmpUserConfig);
    }

    public function getValueProvider()
    {
        return [
            ['no-set-key', null],
            ['key-1', 'value-1'],
            ['nested/key-2', 'value-2'],
            ['nested/nested-2/key-3', 'value-3'],
            ['nested/nested-2/key-not-set', null],
            ['overwrite-1', 'overwrite-1'],
            ['overwrite/overwrite-1', '1'],
            ['overwrite/overwrite-2', 'overwrite-2'],
            ['overwrite/nested/overwrite-2', '2'],
            ['overwrite/nested/overwrite-3', 'overwrite-3'],

        ];
    }

    /**
     * @param $key
     * @param $value
     *
     * @dataProvider getValueProvider
     */
    public function testGetValue($key, $value)
    {
        $this->assertEquals($value, $this->config->getValue($key));
    }

    public function testSaveValue()
    {
        $this->config->saveValue('save', 1);
        $this->assertEquals(1, $this->config->getValue('save'));

        $this->config->saveValue('save-nested', ['key-1' => 1, 'key-2' => 2]);
        $this->assertEquals(1, $this->config->getValue('save-nested/key-1'));
        $this->assertEquals(2, $this->config->getValue('save-nested/key-2'));
    }

    /**
     *
     * @depends testSaveValue
     */
    public function testDeleteValue()
    {
        $this->config->deleteValue('save');
        $this->assertEquals(null, $this->config->getValue('save'));

        $this->config->deleteValue('save-nested/key-1');
        $this->assertEquals(null, $this->config->getValue('save-nested/key-1'));
        $this->config->deleteValue('save-nested');
        $this->assertEquals(null, $this->config->getValue('save-nested/key-2'));
    }

    public function testSaveCollectorValue()
    {
        $collector = new Ecocode_Profiler_Model_Collector_MysqlDataCollector();

        $this->config->saveCollectorValue($collector, 'save', 1);
        $this->assertEquals(1, $this->config->getValue('collector/mysql/save'));
        $this->assertEquals(1, $this->config->getCollectorValue($collector, 'save'));

        return $collector;
    }

    /**
     *
     * @depends testSaveCollectorValue
     * @param $collector
     */
    public function testDeleteCollectorValue($collector)
    {
        $this->config->deleteCollectorValue($collector, 'save');
        $this->assertEquals(null, $this->config->getCollectorValue($collector, 'save'));
    }
}
