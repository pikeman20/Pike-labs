<?php

    namespace Librarys\Error;

    if (defined('E_ERROR') == false)
        define('E_ERROR', 1);

    if (defined('E_WARNING') == false)
        define('E_WARNING', 2);

    if (defined('E_PARSE') == false)
        define('E_PARSE', 4);

    if (defined('E_NOTICE') == false)
        define('E_NOTICE', 8);

    if (defined('E_CODE_ERROR') == false)
        define('E_CODE_ERROR', 16);

    if (defined('E_CODE_WARNING') == false)
        define('E_CODE_WARNING', 32);

    if (defined('E_COMPILE_ERROR') == false)
        define('E_COMPILE_ERROR', 64);

    if (defined('E_COMPILE_WARNING') == false)
        define('E_COMPILE_WARNING', 128);

    if (defined('E_USER_ERROR') == false)
        define('E_USER_ERROR', 256);

    if (defined('E_USER_WARNING') == false)
        define('E_USER_WARNING', 512);

    if (defined('E_USER_NOTICE') == false)
        define('E_USER_NOTICE', 1024);

    if (defined('E_STRICT') == false)
        define('E_STRICT', 2048);

    if (defined('E_RECOVERABLE_ERROR') == false)
        define('E_RECOVERABLE_ERROR', 4069);

    if (defined('EU_ALL') == false)
        define('EU_ALL', E_ALL);

    if (defined('EU_WARNING') == false)
        define('EU_WARNING', E_WARNING | E_USER_WARNING | E_COMPILE_WARNING | E_RECOVERABLE_ERROR);

    if (defined('EU_FATAL') == false)
        define('EU_FATAL', E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

    if (defined('EU_DEPRECATED') == false)
        define('EU_DEPRECATED', E_DEPRECATED | E_USER_DEPRECATED);

    if (defined('EU_NOTICE') == false)
        define('EU_NOTICE', E_NOTICE | E_USER_NOTICE);

    if (defined('EU_STRICT') == false)
        define('EU_STRICT', E_STRICT);

    class ErrorHandler
    {

        private static $errorLists = [
            'fatal' => [
                'mask'  => EU_FATAL,
                'label' => 'Fatal Error'
            ],

            'warning' => [
                'mask'  => EU_WARNING,
                'label' => 'Warning'
            ],

            'notice' => [
                'mask'  => EU_NOTICE,
                'label' => 'Notice'
            ],

            'strict' => [
                'mask'  => EU_STRICT,
                'label' => 'Strict'
            ],

            'deprecated' => [
                'mask'  => EU_DEPRECATED,
                'label' => 'Deprecated'
            ]
        ];

        const EU_DISABLE    = 0;
        const EU_ALL        = EU_ALL;
        const EU_WARNING    = EU_WARNING;
        const EU_FATAL      = EU_FATAL;
        const EU_DEPRECATED = EU_DEPRECATED;
        const EU_NOTICE     = EU_STRICT;
        const EU_STRICT     = EU_STRICT;

        private static $level = self::EU_ALL;

        public static function disError()
        {
            error_reporting(0);
            ini_set('error_reporting', 0);
        }

        public static function listenError($level = self::EU_ALL)
        {
            if ($level === self::EU_DISABLE)
                return;

            if (is_array(error_get_last()) == false)
                self::disError();

            self::$level = $level;
            self::handlerListen();
        }

        private static function handlerListen()
        {
            set_exception_handler(function($exception) {
                ErrorDisplay::putExceptions($exception);
                ErrorDisplay::display();
            });

            register_shutdown_function(function() {
                $errors = error_get_last();

                if (is_array($errors) == false)
                    return;

                $errorType    = intval($errors['type']);
                $errorMessage = trim($errors['message']);
                $errorFile    = trim($errors['file']);
                $errorLine    = intval($errors['line']);
                $errorLabel   = self::getLabelErrorCode($errorType);

                if ($errorLabel !== false) {
                    ErrorDisplay::putError($errorLabel, $errorType, $errorMessage, $errorFile, $errorLine);
                    ErrorDisplay::display();
                }
            });
        }

        public static function getLabelErrorCode($code)
        {
            foreach (self::$errorLists AS $keyerror => $arrays) {
                $mask  = intval($arrays['mask']);
                $label = trim($arrays['label']);

                if ((self::$level == self::EU_ALL || (self::$level & $mask) !== 0) && ($mask & $code) !== 0)
                    return $label;
            }

            return false;
        }

    }
