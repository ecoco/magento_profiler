<?php


define('BP', MAGENTO_ROOT);
$ds = DIRECTORY_SEPARATOR;

/** AUTOLOADER PATCH **/
if (file_exists($autoloaderPath = BP . $ds . '../vendor/autoload.php') ||
    file_exists($autoloaderPath = BP . $ds . 'vendor/autoload.php')
) {
    require $autoloaderPath;
}
/** AUTOLOADER PATCH **/




$profilerDir = implode(DIRECTORY_SEPARATOR, [MAGENTO_ROOT, 'app', 'code', 'community', 'Ecocode', 'Profiler']);
$profilerDir .= DIRECTORY_SEPARATOR;
define('PROFILER_DIR', $profilerDir);

$overwriteBasePath = $profilerDir . 'overwrite' . DIRECTORY_SEPARATOR;

require_once PROFILER_DIR . 'autoloader.php';
$autoloader = Ecocode_Profiler_Autoloader::getAutoloader();
$autoloader->register(true);


$autoloader
    ->addOverwrite('Mage_Core_Model_Resource', 'MageCoreModelResource.php')
    ->addOverwrite('Mage_Core_Model_Resource_Db_Abstract', 'MageCoreModelResourceDbAbstract.php')
    ->addOverwrite('Mage_Core_Model_Store', 'MageCoreModelStore.php')
    ->addOverwrite('Mage_Core_Model_Translate', 'MageCoreModelTranslate.php')
    ->addOverwrite('Mage_Eav_Model_Entity_Abstract', 'MageEavModelEntityAbstract.php');

require_once $profilerDir . 'Helper' . DIRECTORY_SEPARATOR . 'Data.php';

require_once $overwriteBasePath . 'Mage.php';

//load overwrites so we can get around the autoloader
Mage::setRoot(MAGENTO_ROOT . DIRECTORY_SEPARATOR . 'app');

