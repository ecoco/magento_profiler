<?php


define('BP', MAGENTO_ROOT);

$originalIncludePath = get_include_path();

function registerComposerAutoloader()
{
    $parts = explode(DIRECTORY_SEPARATOR, MAGENTO_ROOT);
    $parts = array_filter($parts);
    while ($parts) {
        $path = DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR . 'vendor';
        if (file_exists($path)) {
            $file = $path . DIRECTORY_SEPARATOR . 'autoload.php';
            if (!file_exists($file)) {
                throw new Exception('The developer toolbar needs the composer autoloader');
            }
            require_once $file;
            return;
        }
        array_pop($parts);
    }
    throw new Exception('The developer toolbar needs the composer autoloader');
}

function loadMageOriginal()
{
    $mageRoot      = MAGENTO_ROOT . DIRECTORY_SEPARATOR;
    $mageVarDir    = $mageRoot . 'var' . DIRECTORY_SEPARATOR;
    $mageFile      = $mageRoot . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
    $mgeMd5        = md5_file($mageFile);
    $mageCacheFile = $mageVarDir . DIRECTORY_SEPARATOR . 'MageDev-' . $mgeMd5 . '.php';

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
}

registerComposerAutoloader();
loadMageOriginal();

class Mage extends MageOriginal
{
    protected static $_logs = [];

    public static function terminate()
    {
        self::dispatchEvent('mage_terminate');
    }

    public static function log($message, $level = null, $file = '', $forceLog = false)
    {
        $level = is_null($level) ? Zend_Log::DEBUG : $level;
        $file  = empty($file) ? 'system.log' : $file;

        self::$_logs[] = [$file, $level, $message];

        return parent::log($message, $level, $file, $forceLog);
    }

    public static function run($code = '', $type = 'store', $options = [])
    {
        try {
            ob_start();
            parent::run($code, $type, $options);
            ob_end_flush();
        } catch (\Symfony\Component\Debug\Exception\ContextErrorException $e) {
            ob_clean();
            throw $e;
        }
    }


    public static function getLogEntries()
    {
        return self::$_logs;
    }
}

Mage::setRoot(MAGENTO_ROOT . DIRECTORY_SEPARATOR . 'app');
Mage::register('original_include_path', $originalIncludePath);
