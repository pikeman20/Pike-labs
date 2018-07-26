<?php

    namespace Librarys\App\Mysql;

    use Librarys\App\Config\BaseConfig;

    final class AppMysqlConfig extends BaseConfig
    {

        private static $instance;

        protected function __construct()
        {
            parent::__construct(env('resource.config.mysql'), env('resource.filename.config.mysql'));
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
                self::$instance = new AppMysqlConfig();

            return self::$instance;
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