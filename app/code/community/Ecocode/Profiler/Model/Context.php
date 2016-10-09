<?php

/**
 * Class Ecocode_Profiler_Model_Context
 */
class Ecocode_Profiler_Model_Context
    implements Ecocode_Profiler_Model_ContextInterface
{
    protected $parentId = 0;

    /** @var string */
    protected $key;

    /** @var string */
    protected $data;

    public function __construct($key, array $data = [])
    {
        $this->id   = uniqid();
        $this->key  = $key;
        $this->data = $data;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function setParentId($id)
    {
        $this->parentId = 0;
    }


    public function getKey()
    {
        return $this->key;
    }

    public function addData($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
}
