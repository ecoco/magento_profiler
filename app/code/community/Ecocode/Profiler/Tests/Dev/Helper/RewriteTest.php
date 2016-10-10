<?php

class Ecocode_Profiler_Tests_Dev_Helper_RewriteTest
    extends TestHelper
{

    /**
     * Magento doesn't have any conflicts out of the box, so we need to fake one
     */
    public function testLoadRewrites()
    {
        $helperMock = $this->getMockBuilder('Ecocode_Profiler_Helper_Rewrite')
            ->setMethods(['getModules', 'getModuleConfigXml'])
            ->getMock();

        $modelBaseConfig         = new Mage_Core_Model_Config_Element('<data><active>true</active></data>');
        $modelDisabledBaseConfig = new Mage_Core_Model_Config_Element('<data><active>false</active></data>');
        $modelEtcConfig          = new Mage_Core_Model_Config_Element('<config>
    <global>
        <models>
            <core_mysql4>
                <rewrite>
                    <session>XXX_REWRITE</session>
                </rewrite>
            </core_mysql4>
        </models>
    </global>
</config>');

        $helperMock
            ->expects($this->any())
            ->method('getModules')
            ->will($this->returnValue([
                'test'  => $modelBaseConfig,
                'test2' => $modelDisabledBaseConfig,
            ]));

        $helperMock
            ->expects($this->at(0))
            ->method('getModuleConfigXml')
            ->willReturn(false);

        $helperMock
            ->expects($this->at(1))
            ->method('getModuleConfigXml')
            ->with($this->equalTo('test'), $this->equalTo('config.xml'))
            ->will($this->returnValue($modelEtcConfig));

        /** @var Ecocode_Profiler_Helper_Rewrite $helperMock */
        $result = $helperMock->loadRewrites();
        $this->assertEquals(5, count($result, COUNT_RECURSIVE));
    }

    /**
     * Magento doesn't have any conflicts out of the box, so we need to fake one
     */
    public function testExecuteConflict()
    {
        $rewrites = [
            'blocks'  => [
                'n98/mock_conflict' => [
                    'Mage_Customer_Block_Account',
                    'Mage_Tag_Block_All',
                ]
            ],
            'helpers' => [
                'n98/mock_conflict' => [
                    'Mage_Catalog_Helper_Data',
                    'Mage_Sales_Helper_Data',
                ]
            ],
            'models'  => [
                'n98/mock_conflict' => [
                    'Mage_Catalog_Model_Product',
                    'Mage_Sales_Model_Order',
                ]
            ]
        ];
        $helper   = $this->getHelperWithMockLoadRewrites($rewrites);
        $result   = $helper->getRewriteConflicts();
        $this->assertCount(3, $result);
    }

    /**
     * This is made to look like a conflict (2 rewrites for the same class) but
     * because Bundle extends Catalog, it's valid.  Note that we're implying
     * Bundle depends on Catalog by passing it as the second value in the array.
     */
    public function testExecuteConflictFalsePositive()
    {
        $rewrites = [
            'blocks' => [
                'n98/mock_conflict' => [
                    'Mage_Catalog_Block_Product_Price',
                    'Mage_Bundle_Block_Catalog_Product_Price',
                ]
            ]
        ];
        $helper   = $this->getHelperWithMockLoadRewrites($rewrites);
        $result   = $helper->getRewriteConflicts();
        $this->assertCount(0, $result);
    }

    /**
     * Mock the ConflictsCommand and change the return value of loadRewrites()
     * to the given argument
     *
     * @param  array $return
     * @return Ecocode_Profiler_Helper_Rewrite
     */
    private function getHelperWithMockLoadRewrites(array $return)
    {
        $helperMock = $this->getMockBuilder('Ecocode_Profiler_Helper_Rewrite')
            ->setMethods(['loadRewrites'])
            ->getMock();

        $helperMock
            ->expects($this->any())
            ->method('loadRewrites')
            ->will($this->returnValue($return));

        return $helperMock;
    }
}
