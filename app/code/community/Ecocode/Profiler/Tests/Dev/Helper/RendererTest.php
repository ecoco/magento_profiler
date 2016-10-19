<?php

class Ecocode_Profiler_Tests_Dev_Helper_RendererTest
    extends TestHelper
{
    /** @var  Ecocode_Profiler_Helper_Renderer */
    protected $helper;

    public function setUp()
    {
        $this->helper = new Ecocode_Profiler_Helper_Renderer();
    }

    public function testGetInstanceSingleton()
    {
        $instance = $this->helper->getInstance('table');

        $this->assertInstanceOf('Ecocode_Profiler_Block_Renderer_Table', $instance);


        $this->assertSame(
            $instance,
            $this->helper->getInstance('table')
        );
    }

    public function testRenderBag()
    {
        $bag = new Ecocode_Profiler_Model_Http_ParameterBag();

        //test if the data is correctly passed to the renderer

        $helper = $this->getMockedHelperWithRenderer('Ecocode_Profiler_Block_Renderer_Bag');

        /** @var Ecocode_Profiler_Block_Renderer_Bag $bagRenderer */
        $bagRenderer = $helper->getInstance('xxx');

        $helper->renderBag($bag, ['more_data' => 1]);

        $this->assertSame($bag, $bagRenderer->getBag());
        $this->assertEquals(1, $bagRenderer->getData('more_data'));
    }

    public function testRenderTable()
    {
        //test if the data is correctly passed to the renderer
        $helper = $this->getMockedHelperWithRenderer('Ecocode_Profiler_Block_Renderer_Table');

        /** @var Ecocode_Profiler_Block_Renderer_Table $callStackRenderer */
        $tableRenderer = $helper->getInstance('xxx');

        $items  = [1 => 'test', 2 => 'test2'];
        $labels = ['ID', 'VALUE'];

        $helper->renderTable($items, $labels);

        $this->assertEquals($items, $tableRenderer->getItems());
        $this->assertEquals($labels, $tableRenderer->getLabels());
    }


    public function testRenderCallstack()
    {
        //test if the data is correctly passed to the renderer
        $helper = $this->getMockedHelperWithRenderer('Ecocode_Profiler_Block_Renderer_CallStack');

        /** @var Ecocode_Profiler_Block_Renderer_CallStack $callStackRenderer */
        $callStackRenderer = $helper->getInstance('xxx');

        $stack = [
            ['1'], ['2']
        ];
        $helper->renderCallStack('xxx', $stack, false);

        $this->assertEquals('xxx', $callStackRenderer->getStackId());
        $this->assertEquals($stack, $callStackRenderer->getStack());
        $this->assertEquals(false, $callStackRenderer->shouldWarp());
    }

    /**
     * @param $class
     * @return Ecocode_Profiler_Helper_Renderer
     */
    protected function getMockedHelperWithRenderer($class)
    {
        $helper = $this->getMockBuilder('Ecocode_Profiler_Helper_Renderer')
            ->setMethods(['getInstance'])
            ->getMock();

        $rendererMock = $this->getMockBuilder($class)
            ->setMethods(['_toHtml', 'unsetData'])
            ->getMock();

        $helper->method('getInstance')->willReturn($rendererMock);

        return $helper;
    }
}
