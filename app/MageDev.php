<?php


define('BP', MAGENTO_ROOT);
$ds = DIRECTORY_SEPARATOR;

/** AUTOLOADER PATCH **/
if (file_exists($autoloaderPath = BP . $ds . '../vendor/autoload.php') ||
    file_exists($autoloaderPath = BP . $ds . 'vendor/autoload.php')
) {
    require $autoloaderPath;
}

$originalIncludePath = get_include_path();


$overwriteBasePath = [MAGENTO_ROOT, 'app', 'code', 'community', 'Ecocode', 'Profiler', 'overwrite'];
$overwriteBasePath = implode(DIRECTORY_SEPARATOR, $overwriteBasePath) . DIRECTORY_SEPARATOR;


require_once BP . $ds . 'app'.$ds.'code'.$ds.'community'.$ds.'Ecocode'.$ds.'Profiler'.$ds.'functions.php';
require_once BP . $ds . 'app'.$ds.'code'.$ds.'community'.$ds.'Ecocode'.$ds.'Profiler'.$ds.'functions.php';

require_once $overwriteBasePath . 'Mage.php';

//load overwrites so we can get around the autoloader
require_once $overwriteBasePath . 'MageCoreModelResource.php';
require_once $overwriteBasePath . 'MageCoreModelTranslate.php';
require_once $overwriteBasePath . 'MageCoreModelStore.php';


Mage::setRoot(MAGENTO_ROOT . DIRECTORY_SEPARATOR . 'app');
Mage::register('original_include_path', $originalIncludePath);
