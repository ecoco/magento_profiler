<?php

/**
 * Class Ecocode_Profiler_Helper_AbstractHelper
 */
abstract class Ecocode_Profiler_Helper_AbstractHelper
{

    /**
     * @return null|Ecocode_Profiler_Model_Profile
     * @codeCoverageIgnore
     */
    public function getCurrentProfile()
    {
        return Mage::registry('current_profile');
    }
}
