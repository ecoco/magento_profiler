<?php

use Symfony\Component\Stopwatch\Stopwatch;

class Varien_Profiler
{
    const CATEGORY_DEFAULT = 'default';
    const CATEGORY_CORE    = 'core';
    const CATEGORY_CONFIG  = 'config';
    const CATEGORY_DB      = 'database';
    const CATEGORY_LAYOUT  = 'layout';
    const CATEGORY_EVENT   = 'event';
    const CATEGORY_EAV     = 'eav';
    const CATEGORY_SECTION = 'section';

    static private $sectionMap = [
        'mage::app::init::system_config' => 'config'
    ];
    /**
     * Timers for code profiling
     *
     * @var array
     */
    /** @var  Stopwatch */
    static private $stopWatch;
    static private $enabled = false;

    public static function enable()
    {
        if (self::$enabled) {
            return;
        }
        self::$enabled = true;
    }

    public static function disable()
    {
        self::$enabled = false;
    }

    public static function getCategory($timerName)
    {
        if (isset(self::$sectionMap[$timerName])) {
            return self::CATEGORY_SECTION;
        }

        if (substr($timerName, 0, 14) === 'mage::dispatch') {
            return self::CATEGORY_SECTION;
        }

        $namespaces    = explode(':', $timerName, 2);
        $baseNamespace = reset($namespaces);
        switch (strtolower($baseNamespace)) {
            case 'mage':
            case 'core':
                $category = self::CATEGORY_CORE;
                break;
            case 'eav':
            case '_LOAD_ATTRIBUTE_BY_CODE__':
            case '__EAV_COLLECTION_AFTER_LOAD__':
                $category = self::CATEGORY_EAV;
                break;
            case 'dispatch event':
            case 'observer':
                $category = self::CATEGORY_EVENT;
                break;
            default:
                if (substr($timerName, -6) === '.phtml') {
                    $category = self::CATEGORY_LAYOUT;
                } else {
                    $category = self::CATEGORY_DEFAULT;
                }

        }
        return $category;
    }

    public static function reset()
    {
        self::$stopWatch = new Stopwatch();
        self::$stopWatch->openSection();
    }

    public static function resume($timerName)
    {
        if (!self::$enabled) {
            return;
        }

        if (!self::$stopWatch) {
            self::reset();
        }

        $category = self::getCategory($timerName);
        self::$stopWatch->start($timerName, $category);
    }

    public static function start($timerName)
    {
        self::resume($timerName);
    }

    public static function pause($timerName)
    {
        if (!self::$enabled) {
            return;
        }
        if (self::$stopWatch->isStarted($timerName)) {
            self::$stopWatch->stop($timerName);
        }
    }

    public static function stop($timerName)
    {
        self::pause($timerName);
    }

    public static function fetch()
    {
        return false;
    }

    public static function getTimers()
    {
        if (self::$stopWatch) {
            self::$stopWatch->stopSection('mage');
            return self::$stopWatch->getSectionEvents('mage');
        }

        return [];
    }

    /**
     * Output SQl Zend_Db_Profiler
     *
     */
    public static function getSqlProfiler($res)
    {
        return '';
    }
}
