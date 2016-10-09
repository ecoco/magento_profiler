<?php

/**
 * Class Ecocode_Profiler_AbstractController
 */
class Ecocode_Profiler_Controller_AbstractController
    extends Mage_Core_Controller_Front_Action
{
    /** @var  Ecocode_Profiler_Model_Profiler */
    protected $profiler;

    public function preDispatch()
    {
        $app = $this->getApp();
        //should not be needed as we do not include development.xml
        //in production anyway
        if (!$app instanceof Ecocode_Profiler_Model_AppDev) {
            throw new \RuntimeException('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
        }

        /** @var Mage_Core_Model_Config $config */
        $config = $app->getConfig();

        //disable before/after to html observer as its quiet costly
        $config->setNode('frontend/events/core_block_abstract_to_html_before/observers/ecocode_profiler_context/type', 'disabled');
        $config->setNode('frontend/events/core_block_abstract_to_html_after/observers/ecocode_profiler_context/type', 'disabled');

        parent::preDispatch();
        return $this;
    }

    /**
     * @codeCoverageIgnore
     * @return Mage_Core_Model_App
     */
    protected function getApp()
    {
        return Mage::app();
    }

    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Model_Profiler
     */
    protected function getProfiler()
    {
        if (!$this->profiler) {
            $this->profiler = Mage::getSingleton('ecocode_profiler/profiler');
        }
        return $this->profiler;
    }
}
