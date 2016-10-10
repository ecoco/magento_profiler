<?php

class Ecocode_Profiler_Tests_Dev_Model_Overwrite_MageCoreModelResourceTest
    extends TestHelper
{


    public function testGetConnection()
    {
        $configProperty = new ReflectionProperty('Varien_Db_Adapter_Pdo_Mysql', '_config');
        $configProperty->setAccessible(true);

        $resource = new Mage_Core_Model_Resource();


        /** @var Varien_Db_Adapter_Pdo_Mysql $connection */
        $connection = $resource->getConnection('core_read');

        $this->assertEquals(
            'Ecocode_Profiler_Db_Statement_Pdo_Mysql',
            $connection->getStatementClass()
        );


        $connectionConfig = $configProperty->getValue($connection);
        $this->assertEquals('core_read', $connectionConfig['connection_name']);

    }
}
