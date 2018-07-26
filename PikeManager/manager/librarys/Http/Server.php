<?php

    namespace Librarys\Http;

    class Server
    {

        private static $httpBaseHost;
        private static $httpAbsHost;

        protected function __construct()
        {

        }

        protected function __clone()
        {

        }

        protected function __wakeup()
        {

        }

        public static function takeHttpHost()
        {
            if (null == self::$httpBaseHost) {
                self::$httpBaseHost = trim($_SERVER['SERVER_NAME']);
                self::$httpBaseHost = Uri::urlAddPrefixScheme(self::$httpBaseHost);
            }

            return self::$httpBaseHost;
        }

    }
