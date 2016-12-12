<?php

class Ecocode_Profiler_Tests_Dev_Block_Renderer_Settings_FieldTest
    extends TestHelper
{
    /** @var Ecocode_Profiler_Block_Renderer_Settings_Field */
    protected $renderer;

    public function setUp()
    {
        parent::setUp();
        $this->renderer = new Ecocode_Profiler_Block_Renderer_Settings_Field();
    }

    public function testRenderField()
    {
        $html = $this->renderer->renderField('text', 'email', 'test@test.de', ['id' => 'idx']);

        $expect = '<input id="idx" type="text" name="email" value="test@test.de">';
        $this->assertEquals($expect, trim($html));
    }

    /**
     * @dataProvider prepareAttributesProvider
     * @param       $type
     * @param array $data
     * @param array $expectAttributes
     */
    public function testPrepareAttributes($type, array $data, array $expectAttributes)
    {
        $prepareAttributesMethod = new ReflectionMethod($this->renderer, 'prepareAttributes');
        $prepareAttributesMethod->setAccessible(true);

        $attributes = $prepareAttributesMethod->invoke(
            $this->renderer,
            $type, $data
        );

        $this->assertEquals($expectAttributes, $attributes);
    }


    public function prepareAttributesProvider()
    {
        return [
            ['text', ['id' => 'test-id', 'class' => 'test-class', 'name' => 'test-name'], ['id' => 'id="test-id"', 'class' => 'class="test-class"', 'name' => 'name="test-name"']],
            ['text', ['id' => 'x', 'attributes' => ['custom' => 'y']], ['id' => 'id="x"', 'custom' => 'custom="y"']],
            ['text', ['id' => 'x', 'attributes' => ['custom' => 'y'], 'data' => ['key' => 'value']], ['id' => 'id="x"', 'custom' => 'custom="y"', 'data-key' => 'data-key="value"']]
        ];
    }
}
