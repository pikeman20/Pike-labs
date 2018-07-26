<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    final class AppEnvironmentCacheConfig extends BaseConfig
    {

        private static $instance;

        public function __construct($cachePath)
        {
            parent::__construct($cachePath);
            parent::parse(true);
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