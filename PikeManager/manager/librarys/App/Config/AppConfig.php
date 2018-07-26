<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    use Librarys\File\FileInfo;

    class AppConfig extends BaseConfig
    {

        private static $instance;
        private static $defaults;

        protected function __construct()
        {
            parent::__construct(env('resource.config.manager'), env('resource.filename.config.manager'));
            parent::parse(true);

            self::$defaults = [
                'auto_redirect.file_chmod'       => true,
                'auto_redirect.file_rename'      => true,
                'auto_redirect.create_directory' => true,
                'auto_redirect.create_file'      => true,
                'auto_redirect.create_database'  => true,
                'auto_redirect.rename_database'  => true,

                'enable_disable.button_save_on_javascript'       => true,
                'enable_disable.auto_focus_input_last'           => true,
                'enable_disable.count_checkbox_file_javascript'  => true,
                'enable_disable.count_checkbox_mysql_javascript' => true,
                'enable_disable.list_file_double'                => true,
                'enable_disable.list_database_double'            => true,
                'enable_disable.check_password_default'          => false,
                'enable_disable.development'                     => false,
                'enable_disable.autoload'                        => true,
                'enable_disable.header_fixed'                    => true,

                'login.enable_forgot_password'   => true,
                'login.enable_lock_count_failed' => true,
                'login.max_lock_count'           => 5,
                'login.time_lock'                => 180,
                'login.time_login'               => 64800,
                'login.enable_captcha_secure'    => 0,

                'paging.file_edit_text'      => 50,
                'paging.file_edit_text_line' => 20,
                'paging.file_home_list'      => 50,
                'paging.file_view_zip'       => 0,
                'paging.mysql_list_data'     => 5,

                'cache.lifetime' => 64800,

                'tmp.lifetime' => 300,
                'tmp.limit'    => 50,

                'theme.directory' => 'default',

                'check_update.enable' => true,
                'check_update.time' => 86400,

                'language' => 'vi'
            ];
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
                self::$instance = new AppConfig();

            return self::$instance;
        }

        public function get($name, $default = null, $recache = false)
        {
            if ($default == null && array_key_exists($name, self::$defaults))
                $default = self::$defaults[$name];

            return parent::get($name, $default, $recache);
        }

        public function getSystem($name, $default = null, $recache = false)
        {
            if ($default == null && array_key_exists($name, self::$defaults))
                $default = self::$defaults[$name];

            return parent::getSystem($name, $default, $recache);
        }

        public function callbackPreWrite()
        {
            if ($this->getPathConfig() == $this->getPathConfigSystem())
                return false;

            return true;
        }

        public function takeConfigArrayWrite()
        {
            return $this->getConfigArray();
        }

        public function takePathConfigWrite()
        {
            return $this->pathConfig;
        }

    }

?>