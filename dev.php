<?php
if ((!isset($_SERVER['ALLOW_PROFILER']) || $_SERVER['ALLOW_PROFILER'] !== '1') && (
        isset($_SERVER['HTTP_CLIENT_IP'])
        || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || php_sapi_name() === 'cli-server')
    )
) {
    header('HTTP/1.0 403 Forbidden');
    //exit here, if you do not deploy the profiler on production you can comment these lines
    exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
}

if (version_compare(phpversion(), '5.4.0', '<') === true) {
    throw new RuntimeException('ERROR: Whoops, it looks like you have an invalid PHP version. Magento supports PHP 5.4.0 or newer.');
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
    throw new RuntimeException($mageFilename  . " was not found");
}

require_once $mageFilename;
require_once PROFILER_DIR . 'debug.php';



if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
    Mage::setIsDeveloperMode(true);
}


umask(0);

/* Store or website code */
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';

/* Run store or run website */
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';


$options = [
    'cache'        => ['id_prefix' => 'dev'],
    'config_model' => 'Ecocode_Profiler_Model_Core_Config'
];
Varien_Profiler::enable();
Mage::run($mageRunCode, $mageRunType, $options);

Mage::terminate();
