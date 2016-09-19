<?php

class Ecocode_Profiler_Model_Collector_LayoutDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    protected $renderLog      = [];
    protected $renderedBlocks = [];
    protected $currentBlock;
    protected $tree;

    public function beforeToHtml(Varien_Event_Observer $observer)
    {
        //@FIXME if hide module output is disabled
        /** @var Mage_Core_Block_Abstract $block */
        $block = $observer->getEvent()->getData('block');
        $id    = uniqid();
        $block->setData('profiler_id', $id);

        $data = [
            'id'           => $id,
            'start_render' => microtime(true),
            'hash'         => spl_object_hash($block),
            'children'     => [],
            'parent_id'    => false,
        ];

        if ($parentBlock = $block->getParentBlock()) {

        } else if ($block instanceof Mage_Widget_Block_Interface) {
            $parentBlock = $this->currentBlock;
        }

        if ($parentBlock && $parentId = $parentBlock->getData('profiler_id')) {
            $this->renderLog[$parentId]['children'][] = $id;
            $data['parent_id']                        = $parentId;
        }

        if (!$block instanceof Mage_Widget_Block_Interface) {
            $this->currentBlock = $block;
        }
        $this->renderLog[$id] = $data;
    }

    public function afterToHtml(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Block_Abstract $block */
        $block = $observer->getEvent()->getData('block');
        $id    = $block->getData('profiler_id');
        $data  = $this->renderLog[$id];

        $data += $this->getBaseBlockData($block);
        $data['stop_render']      = microtime(true);
        $data['render_time_incl'] = $data['stop_render'] - $data['start_render'];

        if ($block instanceof Mage_Core_Block_Template) {
            $data['template'] = $block->getTemplate();
        }

        $this->renderedBlocks[$id] = $block;
        $this->renderLog[$id]      = $data;


    }

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $outputProperties = new ReflectionProperty('Mage_Core_Model_Layout', '_output');
        $outputProperties->setAccessible(true);

        $layout       = Mage::app()->getLayout();
        $outputBlocks = [];
        foreach ($outputProperties->getValue($layout) as $name => $data) {
            $block          = $layout->getBlock($name);
            $outputBlocks[] = $block->getData('profiler_id');
        }

        $totalTime = 0;
        foreach ($this->renderLog as &$data) {
            $renderTimeExcl = $data['render_time_incl'];
            foreach ($data['children'] as $childId) {
                $child = $this->renderLog[$childId];
                $renderTimeExcl -= $child['render_time_incl'];
            }
            $data['render_time'] = $renderTimeExcl;

            $totalTime += $data['render_time'];
        }

        $renderedCount     = 0;
        $notRenderedBlocks = [];
        foreach ($layout->getAllBlocks() as $block) {
            if ($block->getData('profiler_id')) {
                $renderedCount++;
            } else {
                $notRenderedBlocks[] = $this->getBaseBlockData($block);
            }
        }
        $this->data = [
            'handles'                   => $layout->getUpdate()->getHandles(),
            'blocks_created_count'      => count($layout->getAllBlocks()),
            'blocks_rendered_count'     => count($this->renderLog),
            'blocks_not_rendered_count' => count($notRenderedBlocks),
            'blocks_not_rendered'       => $notRenderedBlocks,
            'output_blocks'             => $outputBlocks,
            'render_log'                => $this->renderLog,
            'render_time'               => $totalTime

        ];
        return $this;
    }

    protected function getBaseBlockData(Mage_Core_Block_Abstract $block)
    {
        return [
            'name'       => $block->getNameInLayout(),
            'class'      => get_class($block),
            'module'     => $block->getModuleName(),
            'type'       => $block->getData('type'),
            'cacheable'  => $block->getCacheLifetime() !== null,
            'cache_time' => $block->getCacheLifetime(),
        ];
    }

    public function getTotalRenderTime()
    {
        return $this->data['render_time'];
    }
    public function getBlocksNotRendered()
    {
        return $this->data['blocks_not_rendered'];
    }

    public function getLayoutHandles()
    {
        return $this->data['handles'];
    }

    public function getBlocksCreatedCount()
    {
        return $this->data['blocks_created_count'];
    }

    public function getBlocksRenderedCount()
    {
        return $this->data['blocks_rendered_count'];
    }

    public function getCallTree()
    {
        if (!$this->tree) {
            $this->tree = $this->createCallTree();
        }
        return $this->tree;
    }

    protected function createCallTree()
    {
        $tree      = [];
        $totalRenderTime = $this->getTotalRenderTime();
        foreach($this->data['render_log'] as $id => &$node) {
            $node['render_time_percent'] = $node['render_time_incl'] / $totalRenderTime;
        }
        foreach($this->data['render_log'] as $id => &$node) {
            $this->data['render_log'][$id] = $node;
            if ($node['parent_id'] === false) {
                $this->resolveChildren($node);
                $tree[$id] = $node;
            }
        }

        return $tree;
    }

    protected function resolveChildren(&$node) {
        $children = [];
        if (isset($node['children'])) {
            foreach($node['children'] as $childId) {
                $child = $this->data['render_log'][$childId];

                $this->resolveChildren($child);
                $children[$childId] = $child;
            }
        }
        $node['children'] = $children;
    }

    public function getName()
    {
        return 'layout';
    }

}