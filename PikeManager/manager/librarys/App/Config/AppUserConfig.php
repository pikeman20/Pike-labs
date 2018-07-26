<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    final class AppUserConfig extends BaseConfig
    {

        const ARRAY_KEY_USERNAME        = 'username';
        const ARRAY_KEY_EMAIL           = 'email';
        const ARRAY_KEY_SECRET_QUESTION = 'secret_question';
        const ARRAY_KEY_SECRET_ANSWER   = 'secret_answer';
        const ARRAY_KEY_PASSWORD        = 'password';
        const ARRAY_KEY_POSITION        = 'position';
        const ARRAY_KEY_CREATE_AT       = 'create_at';
        const ARRAY_KEY_MODIFY_AT       = 'modify_at';
        const ARRAY_KEY_LOGIN_AT        = 'login_at';
        const ARRAY_KEY_BAND_AT         = 'band_at';
        const ARRAY_KEY_BAND_OF         = 'band_of';

        private static $instance;

        protected function __construct()
        {
            parent::__construct(env('resource.config.user'));
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
                self::$instance = new AppUserConfig();

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