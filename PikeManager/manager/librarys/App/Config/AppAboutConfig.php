<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Http\Request;

    final class AppAboutConfig extends BaseConfig
    {

        const ARRAY_KEY_NAME        = 'name';
        const ARRAY_KEY_AUTHOR      = 'author';
        const ARRAY_KEY_VERSION     = 'version';
        const ARRAY_KEY_IS_BETA     = 'is_beta';
        const ARRAY_KEY_EMAIL       = 'email';
        const ARRAY_KEY_PHONE       = 'phone';
        const ARRAY_KEY_FB_LINK     = 'fb_link';
        const ARRAY_KEY_FB_TITLE    = 'fb_title';
        const ARRAY_KEY_GIT_LINK    = 'git_link';
        const ARRAY_KEY_GIT_TITLE   = 'git_title';
        const ARRAY_KEY_CREATE_AT   = 'create_at';
        const ARRAY_KEY_UPGRADE_AT  = 'upgrade_at';
        const ARRAY_KEY_CHECK_AT    = 'check_at';
        const ARRAY_KEY_BUILD_AT    = 'build_at';

        const ARRAY_VALUE_NAME      = 'Manager';
        const ARRAY_VALUE_AUTHOR    = 'IzeroCs';
        const ARRAY_VALUE_VERSION   = '3.5.4';
        const ARRAY_VALUE_IS_BETA   = true;
        const ARRAY_VALUE_EMAIL     = 'Izero.Cs@gmail.com';
        const ARRAY_VALUE_FB_LINK   = 'https://facebook.com/IzeroCs';
        const ARRAY_VALUE_FB_TITLE  = 'fb.com/IzeroCs';
        const ARRAY_VALUE_GIT_LINK  = 'https://github.com/IzeroCs/Manager';
        const ARRAY_VALUE_GIT_TITLE = 'IzeroCs/Manager';
        const ARRAY_VALUE_PHONE     = '+841685929323';
        const ARRAY_VALUE_CREATE_AT = 1434468025;

        private static $instance;

        protected function __construct()
        {
            parent::__construct(env('resource.config.about'), env('resource.filename.config.about'));
            parent::parse(true);

            $array = [
                self::ARRAY_KEY_NAME      => self::ARRAY_VALUE_NAME,
                self::ARRAY_KEY_AUTHOR    => self::ARRAY_VALUE_AUTHOR,
                self::ARRAY_KEY_VERSION   => self::ARRAY_VALUE_VERSION,
                self::ARRAY_KEY_IS_BETA   => self::ARRAY_VALUE_IS_BETA,
                self::ARRAY_KEY_EMAIL     => self::ARRAY_VALUE_EMAIL,
                self::ARRAY_KEY_FB_LINK   => self::ARRAY_VALUE_FB_LINK,
                self::ARRAY_KEY_FB_TITLE  => self::ARRAY_VALUE_FB_TITLE,
                self::ARRAY_KEY_GIT_LINK  => self::ARRAY_VALUE_GIT_LINK,
                self::ARRAY_KEY_GIT_TITLE => self::ARRAY_VALUE_GIT_TITLE,
                self::ARRAY_KEY_PHONE     => self::ARRAY_VALUE_PHONE,
                self::ARRAY_KEY_CREATE_AT => self::ARRAY_VALUE_CREATE_AT
            ];

            foreach ($array AS $key => $value)
                $this->setSystem($key, $value);
        }

        protected function __wakeup()
        {

        }

        protected function __clone()
        {

        }

        public static function updateBuildDev()
        {
            if (Request::isLocal(true)) {
                if (self::getInstance()->setSystem(self::ARRAY_KEY_BUILD_AT, time()) == false)
                    return;

                self::getInstance()->write(true);
            }
        }

        public static function getInstance()
        {
            if (null === self::$instance)
                self::$instance = new AppAboutConfig();

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