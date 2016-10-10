<?php

Ecocode_Profiler_Helper_Data::loadRenamedClass('core/Mage/Core/Model/Resource/Db/Abstract.php', 'Original_Mage_Core_Model_Resource_Db_Abstract');

abstract class Mage_Core_Model_Resource_Db_Abstract extends
    Original_Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @codeCoverageIgnore
     *
     * @param       $event
     * @param array $data
     */
    protected function dispatch($event, array $data = [])
    {
        Mage::dispatchDebugEvent($event, $data);
    }

    /**
     * overwrite load function as "_afterLoad" etc can be overwritten
     *
     * @param Mage_Core_Model_Abstract $object
     * @param mixed                    $value
     * @param null                     $field
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        $start  = microtime(true);
        $result = parent::load($object, $value, $field);

        $this->dispatch('model_resource_db_load', [
            'object' => $object,
            'time'   => microtime(true) - $start
        ]);

        return $result;
    }

    /**
     * overwrite load function as "_afterSave" etc can be overwritten
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        $start  = microtime(true);
        $result = parent::save($object);

        if (!$object->isDeleted()) {
            //is captured separately
            $this->dispatch('model_resource_db_save', [
                'object' => $object,
                'time'   => microtime(true) - $start
            ]);
        }

        return $result;
    }

    /**
     * overwrite load function as "_afterDelete" etc can be overwritten
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function delete(Mage_Core_Model_Abstract $object)
    {
        $start  = microtime(true);
        $result = parent::delete($object);

        if (!$object->isDeleted()) {
            //is captured separately
            $this->dispatch('model_resource_db_delete', [
                'object' => $object,
                'time'   => microtime(true) - $start
            ]);
        }

        return $result;
    }
}
