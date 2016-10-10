<?php

class Ecocode_Profiler_Tests_Dev_Model_Overwrite_MageEavModelEntityAbstractTest
    extends TestHelper
{
    public function testLoad()
    {
        $testData    = ['a' => 'b'];
        $readAdapter = $this->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['fetchRow'])
            ->getMock();

        $readAdapter->method('fetchRow')->willReturn($testData);

        $entityAbstract = $this->getMockBuilder('Mage_Eav_Model_Entity_Abstract')
            ->setMethods(['dispatch', '_getReadAdapter', '_getLoadRowSelect', 'loadAllAttributes'])
            ->getMock();

        $entityAbstract->method('_getReadAdapter')->willReturn($readAdapter);

        $product = new Mage_Catalog_Model_Product();


        $entityAbstract->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('model_resource_db_load'),
                $this->callback(function($subject) use ($product) {
                    return $subject['object'] === $product && isset($subject['time']);
                })
            );


        $entityAbstract->load($product, 1);
    }

    public function testSave()
    {
        $entityAbstract = $this->getMockBuilder('Mage_Eav_Model_Entity_Abstract')
            ->setMethods(['dispatch', 'loadAllAttributes', '_beforeSave', '_processSaveData', '_collectSaveData', '_afterSave'])
            ->getMock();

        $product = new Mage_Catalog_Model_Product();
        $product->setData('entity_type_id', 1);

        $entityAbstract->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('model_resource_db_save'),
                $this->callback(function($subject) use ($product) {
                    return $subject['object'] === $product && isset($subject['time']);
                })
            );


        $entityAbstract->save($product, 1);
    }

    public function testSaveDeleted()
    {
        $entityAbstract = $this->getMockBuilder('Mage_Eav_Model_Entity_Abstract')
            ->setMethods(['dispatch', 'delete', 'loadAllAttributes', '_beforeSave', '_processSaveData', '_collectSaveData', '_afterSave'])
            ->getMock();

        $product = new Mage_Catalog_Model_Product();
        $product->setData('entity_type_id', 1);
        $product->isDeleted(true);
        $entityAbstract->expects($this->never())
            ->method('dispatch');


        $entityAbstract->save($product, 1);
    }

    public function testDelete()
    {
        $writeAdapter = $this->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();


        $entityAbstract = $this->getMockBuilder('Mage_Eav_Model_Entity_Abstract')
            ->setMethods([
                'dispatch', '_beforeDelete',
                'getEntityIdField', '_getWriteAdapter',
                'getEntityTable', 'loadAllAttributes', '_afterDelete'])
            ->getMock();

        $entityAbstract->method('_getWriteAdapter')->willReturn($writeAdapter);
        $entityAbstract->method('getEntityIdField')->willReturn('id');


        $product = new Mage_Catalog_Model_Product();
        $product->setData('entity_type_id', 1);

        $entityAbstract->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('model_resource_db_delete'),
                $this->callback(function($subject) use ($product) {
                    return $subject['object'] === $product && isset($subject['time']);
                })
            );



        $entityAbstract->delete($product, 1);
    }
}
