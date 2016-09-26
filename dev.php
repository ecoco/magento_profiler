<?php
use Symfony\Component\Debug\Debug;

if ((!isset($_SERVER['ALLOW_PROFILER']) || $_SERVER['ALLOW_PROFILER'] !== '1') && (
        isset($_SERVER['HTTP_CLIENT_IP'])
        || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || php_sapi_name() === 'cli-server')
    )
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
}

if (version_compare(phpversion(), '5.4.0', '<') === true) {
    die('ERROR: Whoops, it looks like you have an invalid PHP version. Magento supports PHP 5.4.0 or newer.');
}


/**
 * Error reporting
 */
error_reporting(E_ALL | E_STRICT);


define('MAGENTO_ROOT', getcwd());

set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path());
$mageFilename    = MAGENTO_ROOT . '/app/MageDev.php';
$maintenanceFile = 'maintenance.flag';

if (!file_exists($mageFilename)) {
    if (is_dir('downloader')) {
        header("Location: downloader");
    } else {
        echo $mageFilename . " was not found";
    }
    exit;
}

if (file_exists($maintenanceFile)) {
    include_once dirname(__FILE__) . '/errors/503.php';
    exit;

}

require_once $mageFilename;

#Varien_Profiler::enable();

if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
    Mage::setIsDeveloperMode(true);
}

#ini_set('display_errors', 1);

umask(0);

/* Store or website code */
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';

/* Run store or run website */
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';

Debug::enable();

$options = [
    'cache' => ['id_prefix' => 'dev']
];
Mage::run($mageRunCode, $mageRunType, $options);


Mage::terminate();
