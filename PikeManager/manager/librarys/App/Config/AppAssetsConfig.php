<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    final class AppAssetsConfig extends BaseConfig
    {

        public function __construct($pathThemeEnv)
        {
            parent::__construct($pathThemeEnv);
            parent::parse(true);
        }

        public function callbackPreWrite()
        {
            return false;
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