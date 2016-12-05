<?php
use Symfony\Component\Debug\BufferingLogger;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;

if (@class_exists('\Symfony\Component\Debug\Debug')) {
    class MagentoErrorHandler extends ErrorHandler
    {
        public function handleException($exception, array $error = null)
        {
            while (ob_get_level()) {
                ob_end_clean();
            }
            parent::handleException($exception, $error);
            try {
                echo '<script src="' . Mage::getBaseUrl('js') . 'ecocode_profiler/vendor/jquery.min.js' . '"></script>
                    <script src="' . Mage::getBaseUrl('js') . 'ecocode_profiler/profiler.js' . '"></script>';
            } catch (Exception $e) {
                //if this fails we dont care
            }
        }
    }

    Debug::enable();
    $errorHandler = new MagentoErrorHandler(new BufferingLogger());
    $errorHandler->throwAt(-1, true);

    ErrorHandler::register($errorHandler);
    $errorHandler->setDefaultLogger(Mage::getLogger());
}
