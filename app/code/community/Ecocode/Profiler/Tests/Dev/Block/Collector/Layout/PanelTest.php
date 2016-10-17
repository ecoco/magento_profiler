<?php

class Ecocode_Profiler_Tests_Dev_Block_Collector_Layout_PanelTest
    extends TestHelper
{

    public function testResolveChildren()
    {
        $nodeList = [
            'node-2' => ['id' => 2],
            'node-3' => ['id' => 3, 'children' => ['node-4']],
            'node-4' => ['id' => 4, 'children' => []],
        ];

        $blockReflection = new ReflectionMethod('Ecocode_Profiler_Block_Collector_Layout_Panel', 'resolveChildren');
        $blockReflection->setAccessible(true);

        $block = new Ecocode_Profiler_Block_Collector_Layout_Panel();

        $node = ['node-1' => [
            'id'       => 1,
            'children' => [
                'node-2',
                'node-3'
            ]
        ]];
        $node = $node['node-1'];
        //use invokeArgs to prevent deprecation warning for pass by reference
        $blockReflection->invokeArgs($block, [&$node, $nodeList]);

        $this->assertEquals([
            'id'       => 1,
            'children' => [
                'node-2' => array_merge($nodeList['node-2'], ['children' => []]),
                'node-3' => array_merge(
                    $nodeList['node-3'],
                    ['children' => ['node-4' => $nodeList['node-4']]]
                )
            ]
        ], $node);
    }
}
