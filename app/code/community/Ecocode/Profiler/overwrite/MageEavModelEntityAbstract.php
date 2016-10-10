<?php

Ecocode_Profiler_Helper_Data::loadRenamedClass('core/Mage/Eav/Model/Entity/Abstract.php', 'Original_Mage_Eav_Model_Entity_Abstract');

abstract class Mage_Eav_Model_Entity_Abstract extends
    Original_Mage_Eav_Model_Entity_Abstract
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

    public function load($object, $entityId, $attributes = [])
    {
        $start  = microtime(true);
        $result = parent::load($object, $entityId, $attributes);

        $this->dispatch('model_resource_db_load', [
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
     * @param $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function delete($object)
    {
        $start  = microtime(true);
        $result = parent::delete($object);

        //is captured separately
        $this->dispatch('model_resource_db_delete', [
            'object' => $object,
            'time'   => microtime(true) - $start
        ]);

        return $result;
    }
}
