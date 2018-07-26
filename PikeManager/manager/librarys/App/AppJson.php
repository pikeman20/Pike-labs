<?php

    namespace Librarys\App;

    class AppJson
    {

        private static $instance;
        private $datas;

        const ARRAY_KEY_ALERT           = 'alert';
        const ARRAY_KEY_RES_CODE        = 'code';
        const ARRAY_KEY_RES_DATA        = 'data';
        const ARRAY_KEY_RES_CODE_SYSTEM = 'code_sys';
        const ARRAY_KEY_RES_DATA_SYSTEM = 'data_sys';

        const ARRAY_KEY_ALERT_ID      = 'id';
        const ARRAY_KEY_ALERT_MESSAGE = 'message';
        const ARRAY_KEY_ALERT_TYPE    = 'type';

        protected function __construct()
        {
            $this->datas = [
                self::ARRAY_KEY_ALERT           => [],
                self::ARRAY_KEY_RES_DATA        => [],
                self::ARRAY_KEY_RES_DATA_SYSTEM => [],
                self::ARRAY_KEY_RES_CODE_SYSTEM => 0,
                self::ARRAY_KEY_RES_CODE        => 0
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
                self::$instance = new AppJson();

            return self::$instance;
        }

        public function addAlert($message, $type, $id)
        {
            $this->datas[self::ARRAY_KEY_ALERT][] = [
                self::ARRAY_KEY_ALERT_ID      => $id,
                self::ARRAY_KEY_ALERT_MESSAGE => $message,
                self::ARRAY_KEY_ALERT_TYPE    => $type
            ];
        }

        public function setResponseCode($code)
        {
            if (($this->datas[self::ARRAY_KEY_RES_CODE] & $code) === 0)
                $this->datas[self::ARRAY_KEY_RES_CODE] = $code;
        }

        public function getResponseCode()
        {
            return $this->datas[self::ARRAY_KEY_RES_CODE];
        }

        public function setResponseCodeSystem($code)
        {
            if (($this->datas[self::ARRAY_KEY_RES_CODE_SYSTEM] & $code) === 0)
                $this->datas[self::ARRAY_KEY_RES_CODE_SYSTEM] |= $code;
        }

        public function getResponseCodeSystem()
        {
            return $this->datas[self::ARRAY_KEY_RES_CODE_SYSTEM];
        }

        public function setResponseData($datas)
        {
            $this->datas[self::ARRAY_KEY_RES_DATA] = $datas;
        }

        public function getResponseData()
        {
            return $this->datas[self::ARRAY_KEY_RES_DATA];
        }

        public function setResponseDataSystem($datas)
        {
            $this->datas[self::ARRAY_KEY_RES_DATA_SYSTEM] = $datas;
        }

        public function getResponseDataSystem()
        {
            return $this->datas[self::ARRAY_KEY_RES_DATA_SYSTEM];
        }

        public function toResult($isShow = true, $isExit = true)
        {
            if ($isShow == false)
                return json_encode($this->datas);

            echo json_encode($this->datas);

            if ($isExit) {
//                sleep(1);
                exit(255);
            }
        }

    }
