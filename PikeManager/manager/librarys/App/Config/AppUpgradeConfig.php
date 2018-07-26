<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    final class AppUpgradeConfig extends BaseConfig
    {

        private static $instance;

        protected function __construct()
        {
            parent::__construct(env('resource.config.upgrade'), env('resource.filename.config.upgrade'));
            parent::parse(true);
        }

        protected function __wakeup()
        {

        }

        protected function __clone()
        {

        }

        public static function getInstance()
        {
            if (null === self::$instance)
                self::$instance = new AppUpgradeConfig();

            return self::$instance;
        }

        public function callbackPreWrite()
        {
            return true;
        }

        public function takeConfigArrayWrite()
        {
            return $this->getConfigArraySystem();
        }

        public function takePathConfigWrite()
        {
            return $this->pathConfigSystem;
        }

    }

?>