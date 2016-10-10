<?php

class Ecocode_Profiler_Tests_Dev_Model_Overwrite_MageCoreModelResourceDbAbstract
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

        $resourceLog = $this->getMockBuilder('Mage_Log_Model_Resource_Log')
            ->setMethods(['dispatch', '_getReadAdapter', '_getLoadSelect', 'unserializeFields', '_afterLoad'])
            ->getMock();

        $resourceLog->method('_getReadAdapter')->willReturn($readAdapter);

        $visitor = new Mage_Log_Model_Visitor();


        $resourceLog->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('model_resource_db_load'),
                $this->callback(function ($subject) use ($visitor) {
                    return $subject['object'] === $visitor && isset($subject['time']);
                })
            );


        $resourceLog->load($visitor, 1);
    }

    public function testSave()
    {
        $writeAdapter = $this->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['insert', 'lastInsertId'])
            ->getMock();


        $resourceLog = $this->getMockBuilder('Mage_Log_Model_Resource_Log')
            ->setMethods([
                'dispatch', '_serializeFields', '_getWriteAdapter',
                '_beforeSave', '_checkUnique', '_prepareDataForSave',
                'getIdFieldName', 'unserializeFields', 'afterSave'])
            ->getMock();

        $resourceLog->method('_getWriteAdapter')->willReturn($writeAdapter);
        $resourceLog->method('getIdFieldName')->willReturn('id');
        $resourceLog->method('_prepareDataForSave')->willReturn([]);


        $visitor = new Mage_Log_Model_Visitor();
        $resourceLog->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('model_resource_db_save'),
                $this->callback(function ($subject) use ($visitor) {
                    return $subject['object'] === $visitor && isset($subject['time']);
                })
            );


        $resourceLog->save($visitor, 1);
    }

    public function testSaveDeleted()
    {
        $resourceLog = $this->getMockBuilder('Mage_Log_Model_Resource_Log')
            ->setMethods(['dispatch', 'delete'])
            ->getMock();

        $visitor = new Mage_Log_Model_Visitor();
        $visitor->isDeleted(true);
        $resourceLog->expects($this->never())
            ->method('dispatch');

        $resourceLog->save($visitor, 1);
    }

    public function testDelete()
    {
        $writeAdapter = $this->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'quoteInto'])
            ->getMock();


        $resourceLog = $this->getMockBuilder('Mage_Log_Model_Resource_Log')
            ->setMethods([
                'dispatch', '_beforeDelete',
                'getMainTable', '_getWriteAdapter',
                '_afterDelete'])
            ->getMock();

        $resourceLog->method('_getWriteAdapter')->willReturn($writeAdapter);


        $visitor = new Mage_Log_Model_Visitor();

        $resourceLog->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('model_resource_db_delete'),
                $this->callback(function ($subject) use ($visitor) {
                    return $subject['object'] === $visitor && isset($subject['time']);
                })
            );


        $resourceLog->delete($visitor, 1);
    }
}
