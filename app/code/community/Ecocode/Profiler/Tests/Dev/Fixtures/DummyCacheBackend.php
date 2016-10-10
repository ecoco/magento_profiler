<?php

class Ecocode_Profiler_Tests_Dev_Fixtures_DummyCacheBackend
    extends Zend_Cache_Backend
{
    protected $_options = ['test_option' => 0];
}
