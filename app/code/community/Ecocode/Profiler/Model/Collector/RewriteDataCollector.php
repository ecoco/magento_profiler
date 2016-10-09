<?php

/**
 * Class Ecocode_Profiler_Model_Collector_RewriteDataCollector
 */
class Ecocode_Profiler_Model_Collector_RewriteDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    /**
     * @codeCoverageIgnore
     * @return Ecocode_Profiler_Helper_Rewrite
     */
    protected function getRewriteHelper()
    {
        return Mage::helper('ecocode_profiler/rewrite');
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $rewriteHelper = $this->getRewriteHelper();
        $this->data    = [
            'module_rewrites'          => $rewriteHelper->loadRewrites(),
            'module_rewrite_conflicts' => $rewriteHelper->getRewriteConflicts(),
        ];

    }

    /**
     * @return array
     */
    public function getModuleRewrites()
    {
        return $this->data['module_rewrites'];
    }

    /**
     * @return array
     */
    public function getModuleRewriteConflicts()
    {
        return $this->data['module_rewrite_conflicts'];
    }


    public function getModuleRewriteConflictCount()
    {
        return count($this->data['module_rewrite_conflicts']);
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getName()
    {
        return 'rewrite';
    }

}
