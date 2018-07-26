<?php

    namespace Librarys;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('LOADED') == false)
        exit;

    error_reporting(E_ALL);

    require_once(__DIR__ . SP . 'Function.php');

    use Librarys\Http\Request;
    use Librarys\Buffer\BufferOutput;
    use Librarys\Error\ErrorHandler;
    use Librarys\Http\Secure\CFSRToken;

    class Boot
    {

        private static $instance;

        protected function __construct($configPath, $cacheDirectory, $isCustomHeader = false)
        {
            BufferOutput::startBuffer($isCustomHeader);
            BufferOutput::listenEndBuffer();
            BufferOutput::fixMagicQuotesGpc();

            Environment::getInstance($configPath, $cacheDirectory)->execute();
            Language::getInstance($this)->execute();

            $reported        = env('app.dev.error_reported.enable',         false);
            $reportedProduct = env('app.dev.error_reported.enable_product', false);

            if (($reported && Request::isLocal()) || $reportedProduct)
                ErrorHandler::listenError(env('app.dev.error_reported.level'));
            else
                ErrorHandler::disError();

            if (CFSRToken::getInstance()->validatePost() !== true)
                die('CFSR Token not validate');

            @date_default_timezone_set(env('app.date.timezone', 'Asia/Ho_Chi_Minh'));
        }

        protected function __wakeup()
        {

        }

        protected function __clone()
        {

        }

        public static function getInstance($configPath, $cacheDirectory, $isCustomHeader = false)
        {
            if (null === self::$instance)
                self::$instance = new Boot($configPath, $cacheDirectory, $isCustomHeader);

            return self::$instance;
        }

        public function getMemoryUsageBegin()
        {
            return $this->memoryUsageBegin;
        }

        public function getMemoryUsageEnd()
        {
            return $this->memoryUsageEnd;
        }

    }

?>
