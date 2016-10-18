<?php

/**
 * Class Ecocode_Profiler_Model_Profiler_Session
 */
class Ecocode_Profiler_Model_Session
    extends Mage_Core_Model_Session_Abstract
{
    public function __construct($data = [])
    {
        $name = isset($data['name']) ? $data['name'] : null;
        $this->init('eco_profiler', $name);
    }
}
