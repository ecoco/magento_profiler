<?php

class Ecocode_Profiler_Tests_Dev_Helper_ContextTest
    extends TestHelper
{
    public function testInit()
    {
        $helper = $this->getNewHelper();

        $current = $helper->getCurrent();

        $this->assertEquals('unknown', $current->getKey());
        $this->assertEquals(0, $current->getParentId());
        $this->assertCount(1, $helper->getList());
    }

    public function testCurrentId()
    {
        $helper  = $this->getNewHelper();
        $current = $helper->getCurrent();
        $this->assertEquals($current->getId(), $helper->getCurrentId());
        $helper->close($current);

        $this->assertNull($helper->getCurrentId());
    }

    public function testOpen()
    {
        $helper = $this->getNewHelper();

        $context = new Ecocode_Profiler_Model_Context('test-context');

        $this->assertNotEquals($context, $helper->getCurrent());
        $helper->open($context);
        $this->assertEquals($context, $helper->getCurrent());
    }

    public function testClose()
    {
        $helper = $this->getNewHelper();

        $context = new Ecocode_Profiler_Model_Context('test-context');

        $helper->open($context);
        $this->assertCount(2, $helper->getStack());
        $helper->close($context);
        $this->assertCount(1, $helper->getStack());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage unable to close unknown context
     */
    public function testCloseInvalid()
    {
        $helper = $this->getNewHelper();

        $context = new Ecocode_Profiler_Model_Context('test-context');

        $helper->close($context);
    }

    public function testStacking()
    {
        $helper      = $this->getNewHelper();
        $rootContext = $helper->getCurrent();
        $context1    = new Ecocode_Profiler_Model_Context('test-context-1');
        $context2    = new Ecocode_Profiler_Model_Context('test-context-2');

        $helper->open($context1);
        $this->assertCount(2, $helper->getStack());
        $helper->open($context2);
        $this->assertCount(3, $helper->getStack());
        $this->assertEquals(
            [$rootContext->getId(), $context1->getId(), $context2->getId()],
            $this->getStackIds($helper)
        );

        $helper->close($context2);
        $this->assertEquals(
            [$rootContext->getId(), $context1->getId()],
            $this->getStackIds($helper)
        );

    }

    public function testGetContextById()
    {
        $helper = $this->getNewHelper();

        $context          = new Ecocode_Profiler_Model_Context('test-context');
        $profile          = new Ecocode_Profiler_Model_Profile('xxx');
        $contextCollector = new Ecocode_Profiler_Model_Collector_ContextDataCollector();

        $profile->addCollector($contextCollector);
        $helper->open($context);

        $dataProperty = new ReflectionProperty('Ecocode_Profiler_Model_Collector_ContextDataCollector', 'data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($contextCollector, ['list' => $helper->getList()]);

        $mock = $this
            ->getMockBuilder('Ecocode_Profiler_Helper_Context')
            ->setMethods(['getCurrentProfile'])
            ->getMock();

        $mock->method('getCurrentProfile')
            ->willReturn($profile);

        /** @var Ecocode_Profiler_Helper_Context $loadedContext */
        $loadedContext = $mock->getContextById($context->getId());
        $this->assertEquals(
            $context,
            $loadedContext
        );
    }


    /**
     * @return Ecocode_Profiler_Helper_Context
     */
    protected function getNewHelper()
    {
        return new Ecocode_Profiler_Helper_Context();
    }

    protected function getStackIds(Ecocode_Profiler_Helper_Context $helper)
    {
        $ids = [];
        foreach ($helper->getStack() as $context) {
            /** @var Ecocode_Profiler_Model_ContextInterface $context */
            $ids[] = $context->getId();
        }
        return $ids;
    }
}
