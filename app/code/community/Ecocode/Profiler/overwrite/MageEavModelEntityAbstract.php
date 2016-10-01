<?php

loadRenamedClass('core/Mage/Eav/Model/Entity/Abstract.php', 'Original_Mage_Eav_Model_Entity_Abstract');

abstract class Mage_Eav_Model_Entity_Abstract extends
    Original_Mage_Eav_Model_Entity_Abstract
{
    public function load($object, $entityId, $attributes = [])
    {
        $start  = microtime(true);
        $result = parent::load($object, $entityId, $attributes);

        Mage::dispatchDebugEvent('model_resource_db_load', [
            'object' => $object,
            'time'   => microtime(true) - $start
        ]);

        return $result;
    }

    /**
     * overwrite load function as "_afterSave" etc can be overwritten
     */
    public function save(Varien_Object $object)
    {
        $start  = microtime(true);
        $result = parent::save($object);

        if (!$object->isDeleted()) {
            //is captured separately
            Mage::dispatchDebugEvent('model_resource_db_save', [
                'object' => $object,
                'time'   => microtime(true) - $start
            ]);
        }

        return $result;
    }

    /**
     * overwrite load function as "_afterDelete" etc can be overwritten
     *
     * @param $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function delete($object)
    {
        $start  = microtime(true);
        $result = parent::delete($object);

        if (!$object->isDeleted()) {
            //is captured separately
            Mage::dispatchDebugEvent('model_resource_db_delete', [
                'object' => $object,
                'time'   => microtime(true) - $start
            ]);
        }

        return $result;
    }
}

