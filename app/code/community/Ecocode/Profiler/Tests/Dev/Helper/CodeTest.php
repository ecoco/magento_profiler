<?php

class Ecocode_Profiler_Tests_Dev_Helper_CodeTest
    extends TestHelper
{
    /**
     * @var Ecocode_Profiler_Helper_Code
     */
    private $helper;

    protected function setUp()
    {
        $this->helper = new Ecocode_Profiler_Helper_Code(
            'txmt://open?url=file://%f&line=%l',
            null,
            'UTF-8'
        );
    }

    public function testFormatFile()
    {
        $expected = sprintf('<a target="_blank" href="txmt://open?url=file://%s&amp;line=25" title="Click to open this file" class="file_link">%s at line 25</a>', __FILE__, __FILE__);
        $this->assertEquals($expected, $this->helper->formatFile(__FILE__, 25));

        $expected = sprintf('<a target="_blank" href="txmt://open?url=file://%s&amp;line=25" title="Click to open this file" class="file_link">%s</a>', __FILE__, 'test txt');
        $this->assertEquals($expected, $this->helper->formatFile(__FILE__, 25, 'test txt'));

        //set root dir
        $rootDirProperty = new ReflectionProperty($this->helper, 'rootDir');
        $rootDirProperty->setAccessible(true);

        $rootDir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
        $rootDirProperty->setValue($this->helper, $rootDir);

        $expected = sprintf(
            '<a target="_blank" href="txmt://open?url=file://%s&amp;line=25" title="Click to open this file" class="file_link"><abbr title="%s">%s</abbr>%s at line 25</a>',
            __FILE__,
            $rootDir . 'Tests',
            'Tests',
            '/Dev/Helper/CodeTest.php'
        );
        $this->assertEquals($expected, $this->helper->formatFile(__FILE__, 25));
    }


    public function testFormatClass()
    {
        $expected = sprintf(
            '<a target="_blank" href="txmt://open?url=file://%s&amp;line=3" title="Click to open this file" class="file_link">%s</a>',
            __FILE__,
            'Ecocode_Profiler_Tests_Dev_Helper_CodeTest'
        );

        $this->assertEquals($expected, $this->helper->formatClass($this));
    }

    public function testFormatClassMethod()
    {
        $text     = $this->helper->formatClassMethod($this, 'testFormatClassMethod');
        $expected = sprintf(
            '<a target="_blank" href="txmt://open?url=file://%s&amp;line=57" title="Click to open this file" class="file_link">%s</a>',
            __FILE__,
            'Ecocode_Profiler_Tests_Dev_Helper_CodeTest:testFormatClassMethod'
        );

        $this->assertEquals($expected, $text);
    }

    /**
     * @dataProvider getClassNameProvider
     * @param $class
     * @param $abbr
     */
    public function testGettingClassAbbreviation($class, $abbr)
    {
        $this->assertEquals($this->helper->abbrClass($class), $abbr);
    }

    /**
     * @dataProvider getMethodNameProvider
     * @param $method
     * @param $abbr
     */
    public function testGettingMethodAbbreviation($method, $abbr)
    {
        $this->assertEquals($this->helper->abbrMethod($method), $abbr);
    }

    public function getClassNameProvider()
    {
        return [
            ['F\Q\N\Foo', '<abbr title="F\Q\N\Foo">Foo</abbr>'],
            ['Bare', '<abbr title="Bare">Bare</abbr>'],
        ];
    }

    public function getMethodNameProvider()
    {
        return [
            ['F\Q\N\Foo::Method', '<abbr title="F\Q\N\Foo">Foo</abbr>::Method()'],
            ['Bare::Method', '<abbr title="Bare">Bare</abbr>::Method()'],
            ['Closure', '<abbr title="Closure">Closure</abbr>'],
            ['Method', '<abbr title="Method">Method</abbr>()'],
        ];
    }
}
