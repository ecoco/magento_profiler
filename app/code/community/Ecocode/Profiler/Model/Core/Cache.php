<?php

/**
 * Class Ecocode_Profiler_Model_Core_Cache
 */
class Ecocode_Profiler_Model_Core_Cache extends Mage_Core_Model_Cache
{
    protected $log = [];

    public function load($id)
    {
        $start    = microtime(true);
        $result   = parent::load($id);
        $execTime = microtime(true) - $start;

        $this->log[] = ['action' => 'load', 'id' => $id, 'hit' => ($result !== false), 'time' => $execTime];

        return $result;
    }

    public function save($data, $id, $tags = [], $lifeTime = null)
    {
        $start    = microtime(true);
        $result   = parent::save($data, $id, $tags, $lifeTime);
        $execTime = microtime(true) - $start;

        $this->log[] = ['action' => 'save', 'id' => $id, 'tags' => $tags, 'life_time' => $lifeTime, 'time' => $execTime];
        return $result;
    }

    public function clean($tags = [])
    {

        $loadTime = microtime(true);
        $result   = parent::clean($tags);
        $execTime = microtime(true) - $loadTime;
        $this->log[] = ['action' => 'clean', 'tags' => $tags, 'time' => $execTime];

        return $result;
    }

    public function getLog()
    {
        return $this->log;
    }
}
