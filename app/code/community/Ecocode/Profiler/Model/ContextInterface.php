<?php

/**
 * Interface Ecocode_Profiler_Model_ContextInterface
 */
interface Ecocode_Profiler_Model_ContextInterface
{

    public function getId();

    public function getParentId();

    public function setParentId($id);

    public function getKey();

    public function getData();

    public function addData($key, $value);

}
