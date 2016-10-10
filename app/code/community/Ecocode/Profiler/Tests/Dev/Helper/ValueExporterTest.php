<?php

class Ecocode_Profiler_Tests_Dev_Helper_ValueExporterTest
    extends TestHelper
{
    /**
     * @var Ecocode_Profiler_Helper_ValueExporter
     */
    private $valueExporter;

    protected function setUp()
    {
        $this->valueExporter = new Ecocode_Profiler_Helper_ValueExporter();
    }

    public function exportValueDataProvider()
    {
        $stream = fopen('php://memory', 'w');
        return [
            [new Varien_Object(), 'Object(Varien_Object)'],
            [[new Varien_Object(), new Varien_Object()], '[0 => Object(Varien_Object), 1 => Object(Varien_Object)]'],
            [[1, 2], '[0 => 1, 1 => 2]'],
            [[1 => ['a', 'b'], []], "[\n  1 => [\n    0 => a, \n    1 => b\n  ], \n  2 => []\n]"],
            [[null, true, false], '[0 => null, 1 => true, 2 => false]'],
            [$stream, sprintf('Resource(stream#%d)', $stream)],
            [[str_repeat('x', 100)], "[\n  0 => " . str_repeat('x', 100) . "\n]"]
        ];
    }

    /**
     * @dataProvider exportValueDataProvider
     * @param $value
     * @param $expectValue
     */
    public function testExportValue($value, $expectValue)
    {
        $value = $this->valueExporter->exportValue($value);
        $this->assertEquals($expectValue, $value);
    }

    public function testDateTime()
    {
        $dateTime = new \DateTime('2014-06-10 07:35:40', new \DateTimeZone('UTC'));
        $this->assertSame('Object(DateTime) - 2014-06-10T07:35:40+0000', $this->valueExporter->exportValue($dateTime));
    }

    public function testDateTimeImmutable()
    {
        $dateTime = new \DateTimeImmutable('2014-06-10 07:35:40', new \DateTimeZone('UTC'));
        $this->assertSame('Object(DateTimeImmutable) - 2014-06-10T07:35:40+0000', $this->valueExporter->exportValue($dateTime));
    }

    public function testIncompleteClass()
    {
        $foo                                  = new \__PHP_Incomplete_Class();
        $array                                = new \ArrayObject($foo);
        $array['__PHP_Incomplete_Class_Name'] = 'AppBundle/Foo';
        $this->assertSame('__PHP_Incomplete_Class(AppBundle/Foo)', $this->valueExporter->exportValue($foo));
    }
}
