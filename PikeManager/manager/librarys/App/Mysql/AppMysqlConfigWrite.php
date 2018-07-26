<?php

    namespace Librarys\App\Mysql;

    use Librarys\App\Base\BaseConfigWrite;
    use Librarys\App\Mysql\AppMysqlConfig;

    final class AppMysqlConfigWrite extends BaseConfigWrite
    {

        public function __construct()
        {
            parent::__construct(AppMysqlConfig::getInstance(), AppMysqlConfig::getInstance()->getPathConfig());
        }

        public function callbackPreWrite()
        {
            if ($this->baseConfigRead->getPathConfig() == $this->baseConfigRead->getPathConfigSystem())
                return false;

            return true;
        }

        public function resultConfigArray()
        {
            return $this->baseConfigRead->getConfigArray();
        }

    }

?>