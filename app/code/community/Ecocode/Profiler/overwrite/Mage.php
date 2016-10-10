<?php


$mageRoot      = MAGENTO_ROOT . DIRECTORY_SEPARATOR;
$mageVarDir    = $mageRoot . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
$mageFile      = $mageRoot . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
$mageMd5       = md5_file($mageFile);
$mageCacheFile = Ecocode_Profiler_Helper_Data::getOverwriteDir() . 'Original_Mage_' . Ecocode_Profiler_Helper_Data::getVersion() . '-' . $mageMd5 . '.php';

if (!file_exists($mageCacheFile)) {
    $mageCode = file_get_contents($mageFile);

    $replace  = [
        'final class Mage'                                             => 'class MageOriginal',
        ' private '                                                    => ' protected ',
        "define('BP', dirname(dirname(__FILE__)));"                    => '',
        "Mage::register('original_include_path', get_include_path());" => '$originalIncludePath = get_include_path();',
        "Mage::registry('original_include_path')"                      => '$originalIncludePath',
        'new Mage_Core_Model_App()'                                    => 'new Ecocode_Profiler_Model_AppDev()',
        'self::printException($e);'                                    => 'throw $e;'
    ];
    $mageCode = str_replace(array_keys($replace), array_values($replace), $mageCode);

    $replace  = [
        '/(?<!= )self::/' => 'static::'
    ];
    $mageCode = preg_replace(array_keys($replace), array_values($replace), $mageCode);

    file_put_contents($mageCacheFile, $mageCode);
}

require_once $mageCacheFile;


class Mage extends MageOriginal
{
    protected static $_logChannels = [];

    protected static $_logger;
    protected static $_loggerDebugHandler;

    /**
     * @return Ecocode_Profiler_Model_Logger
     */
    public static function getLogger($channel = null)
    {
        if ($channel === null) {
            return static::getDefaultLogger();
        }
        if (!isset(static::$_logChannels[$channel])) {
            static::$_logChannels[$channel] = static::getNewLogger($channel);
        }

        return static::$_logChannels[$channel];
    }

    public static function getDefaultLogger()
    {
        if (static::$_logger === null) {
            static::$_logger = static::getNewLogger('default');
        }

        return static::$_logger;
    }

    protected static function getNewLogger($channel)
    {
        if (!@class_exists('\Monolog\Logger')) {
            return false;
        }
        if (static::$_loggerDebugHandler === null) {
            static::$_loggerDebugHandler = new Ecocode_Profiler_Model_Logger_DebugHandler();
        }

        return new Ecocode_Profiler_Model_Logger(
            $channel,
            [static::$_loggerDebugHandler]
        );
    }

    public static function log($message, $level = null, $file = '', $forceLog = false)
    {
        $level = is_null($level) ? Zend_Log::DEBUG : $level;
        $file  = empty($file) ? 'system.log' : $file;

        $channel = preg_replace('/(\..+)$/', '', $file);
        static::getLogger($channel)->mageLog($level, $message);

        return parent::log($message, $level, $file, $forceLog);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function terminate()
    {
        self::dispatchDebugEvent('mage_terminate');
    }


    /**
     * @codeCoverageIgnore
     *
     * @param       $name
     * @param array $data
     * @return Mage_Core_Model_App
     */
    public static function dispatchDebugEvent($name, array $data = [])
    {
        $data['debug'] = true;
        return parent::dispatchEvent($name, $data);
    }

    /**
     * @codeCoverageIgnore
     * @return []
     */
    public static function getAllRegistryEntries()
    {
        return static::$_registry;
    }
}

Mage::register('original_include_path', get_include_path());
