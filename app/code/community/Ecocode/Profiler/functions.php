<?php

$overwriteDir = MAGENTO_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
define('PROFILER_OVERWRITE_DIR', $overwriteDir);


function getProfilerVersion()
{
    if (!defined('PROFILER_VERSION')) {
        $configFile = __DIR__ . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'config.xml';
        $xml        = file_get_contents($configFile);
        preg_match('/<version>([0-9\.]+)<\/version>/', $xml, $matches);
        if (!$matches) {
            die('unable to determinate profiler version');
        }
        define('PROFILER_VERSION', $matches[1]);
    }

    return PROFILER_VERSION;
}

/**
 * load a class by changing its original name
 * this is more a hack, but it prevents causing rewrite collisions
 * with user defined rewrites within magento
 *
 * @param $fileName
 * @param $className
 */
function loadRenamedClass($fileName, $className)
{
    if (!file_exists(PROFILER_OVERWRITE_DIR)) {
        mkdir(PROFILER_OVERWRITE_DIR, 0777, true);
    }

    $ds         = DIRECTORY_SEPARATOR;
    $sourceFile = MAGENTO_ROOT . $ds . 'app' . $ds . 'code' . $ds . $fileName;
    $sourceMd5  = md5_file($sourceFile);


    $fileName  = sprintf('%s-%s-%s.php', $className, getProfilerVersion(), $sourceMd5);
    $cacheFile = PROFILER_OVERWRITE_DIR . $fileName;

    if (!file_exists($cacheFile)) {
        $code = file_get_contents($sourceFile);

        $code = preg_replace('/class ([^\s]+)/', 'class ' . $className, $code);


        file_put_contents($cacheFile, $code);
    }
    require_once $cacheFile;
}