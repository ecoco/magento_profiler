<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_RewriteDataCollectorTest
    extends TestHelper
{
    public function testCollect()
    {
        $rewriteHelperMock = $this->getMockBuilder('Ecocode_Profiler_Helper_Rewrite')
            ->setMethods(['loadRewrites', 'getRewriteConflicts'])
            ->getMock();

        //fake a conflict and sample load rewrites
        $rewrites  = json_decode('{"blocks":[],"helpers":[],"models":{"core_mysql4\/session":["XXX_REWRITE"]}}', true);
        $conflicts = json_decode('[{"type":"blocks","class":"n98\/mock_conflict","rewrites":["Mage_Customer_Block_Account","Mage_Tag_Block_All"],"loaded_class":"Mage_N98_Block_Mock_Conflict"}]', true);

        $rewriteHelperMock->method('loadRewrites')->willReturn($rewrites);
        $rewriteHelperMock->method('getRewriteConflicts')->willReturn($conflicts);

        /** @var Ecocode_Profiler_Model_Collector_RewriteDataCollector $collector */
        $collector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_RewriteDataCollector')
            ->setMethods(['getRewriteHelper'])
            ->getMock();


        $collector->method('getRewriteHelper')->willReturn($rewriteHelperMock);

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $rewrites = $collector->getModuleRewrites();
        $this->assertCount(0, $rewrites['blocks']);
        $this->assertCount(0, $rewrites['helpers']);
        $this->assertCount(1, $rewrites['models']);
        $this->assertCount(1, $collector->getModuleRewriteConflicts());
        $this->assertEquals(1, $collector->getModuleRewriteConflictCount());
    }
}
