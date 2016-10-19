<?php

class Ecocode_Profiler_Tests_Dev_Block_Renderer_BagTest
    extends TestHelper
{
    /** @var Ecocode_Profiler_Block_Renderer_Table */
    protected $renderer;

    public function setUp()
    {
        parent::setUp();
        $this->renderer = new Ecocode_Profiler_Block_Renderer_Bag();
    }

    public function testClassRendering()
    {
        $html = $this->renderer->render(['class' => 'test-class']);

        $this->assertNotFalse(strpos($html, '<table class="test-class"'));
    }

    public function testDefaultLabelRendering()
    {
        $html = $this->renderer->render();

        $this->assertNotFalse(strpos($html, 'Key</th>'));
        $this->assertNotFalse(strpos($html, 'Value</th>'));
    }

    public function testLabelRendering()
    {
        $html = $this->renderer->render(['labels' => ['Key1', 'Value1']]);

        $this->assertNotFalse(strpos($html, 'Key1</th>'));
        $this->assertNotFalse(strpos($html, 'Value1</th>'));
    }

    public function testRowRendering()
    {
        $params = [
            'test' => 'value',
            'test2' => 'value2'
        ];
        $bag = new Ecocode_Profiler_Model_Http_ParameterBag($params);
        $html = $this->renderer->render(['bag' => $bag]);

        foreach($params as $key => $value) {
            $this->assertNotFalse(strpos($html, $key . '</th>'));
            $this->assertNotFalse(strpos($html, $value . '</td>'));
        }
    }
}
