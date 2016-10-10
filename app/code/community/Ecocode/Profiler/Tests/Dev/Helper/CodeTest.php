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
        $this->helper = new Ecocode_Profiler_Helper_Code('txmt://open?url=file://%f&line=%l', '/root', 'UTF-8');
    }
    
    public function testFormatFile()
    {
        $expected = sprintf('<a href="txmt://open?url=file://%s&amp;line=25" title="Click to open this file" class="file_link">%s at line 25</a>', __FILE__, __FILE__);
        $this->assertEquals($expected, $this->helper->formatFile(__FILE__, 25));
    }

    /**
     * @dataProvider getClassNameProvider
     */
    public function testGettingClassAbbreviation($class, $abbr)
    {
        $this->assertEquals($this->helper->abbrClass($class), $abbr);
    }

    /**
     * @dataProvider getMethodNameProvider
     */
    public function testGettingMethodAbbreviation($method, $abbr)
    {
        $this->assertEquals($this->helper->abbrMethod($method), $abbr);
    }

    public function getClassNameProvider()
    {
        return array(
            array('F\Q\N\Foo', '<abbr title="F\Q\N\Foo">Foo</abbr>'),
            array('Bare', '<abbr title="Bare">Bare</abbr>'),
        );
    }

    public function getMethodNameProvider()
    {
        return array(
            array('F\Q\N\Foo::Method', '<abbr title="F\Q\N\Foo">Foo</abbr>::Method()'),
            array('Bare::Method', '<abbr title="Bare">Bare</abbr>::Method()'),
            array('Closure', '<abbr title="Closure">Closure</abbr>'),
            array('Method', '<abbr title="Method">Method</abbr>()'),
        );
    }
}
