<?php

Ecocode_Profiler_Helper_Data::loadRenamedClass('core/Mage/Core/Model/Resource.php', 'Original_Mage_Core_Model_Resource');


class Mage_Core_Model_Resource extends
    Original_Mage_Core_Model_Resource
{
    protected $configProperty;

    public function __construct()
    {
        $this->configProperty = new ReflectionProperty('Varien_Db_Adapter_Pdo_Mysql', '_config');
        $this->configProperty->setAccessible(true);
    }

    public function getConnection($name)
    {
        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = parent::getConnection($name);
        if ($connection->getStatementClass() !== 'Ecocode_Profiler_Db_Statement_Pdo_Mysql') {
            $connection->setStatementClass('Ecocode_Profiler_Db_Statement_Pdo_Mysql');
            $config = $connection->getConfig();
            if (!isset($config['connection_name'])) {
                $config['connection_name'] = $name;
                $this->configProperty->setValue($connection, $config);
            }
        }

        return $connection;
    }

}
