<?php


class Ecocode_Profiler_Tests_Dev_Model_Observer_ContextTest
    extends TestHelper
{
    protected $contextHelper;

    protected function setUp()
    {
        parent::setUp();

        /** @var Ecocode_Profiler_Helper_Context $contextHelper */
        $this->contextHelper = $this->getMockBuilder('Ecocode_Profiler_Helper_Context')
            ->getMock();
    }


    public function testOpenBlockContext()
    {
        /** @var Ecocode_Profiler_Model_Observer_Context $contextHelper */
        $observer = $this->getMockBuilder('Ecocode_Profiler_Model_Observer_Context')
            ->setMethods(['getHelper'])
            ->getMock();

        $observer->method('getHelper')->willReturn($this->contextHelper);

        $block = new Mage_Core_Block_Template();
        $block->setTemplate('test.phtml');
        $eventObserver = $this->getObserver(['block' => $block]);

        $this->contextHelper->expects($this->once())->method('open');
        $observer->openBlockContext($eventObserver);

        /** @var Ecocode_Profiler_Model_ContextInterface $context */
        $context = $block->getData('__context');
        $this->assertInstanceOf('Ecocode_Profiler_Model_ContextInterface', $context);

        return $block;
    }

    /**
     * @depends testOpenBlockContext
     */
    public function testCloseBlockContext(Mage_Core_Block_Template $block )
    {
        /** @var Ecocode_Profiler_Model_Observer_Context $contextHelper */
        $observer = $this->getMockBuilder('Ecocode_Profiler_Model_Observer_Context')
            ->setMethods(['getHelper'])
            ->getMock();

        $observer->method('getHelper')->willReturn($this->contextHelper);

        $eventObserver = $this->getObserver(['block' => $block]);

        $this->contextHelper->expects($this->once())->method('close');
        $observer->closeBlockContext($eventObserver);

        /** @var Ecocode_Profiler_Model_ContextInterface $context */
        $contextData = $block->getData('__context')->getData();
        $this->assertEquals('test.phtml', $contextData['template']);


        return $block;
    }
}
