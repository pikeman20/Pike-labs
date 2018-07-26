<?php

    namespace Librarys\Buffer;

    class BufferOutput
    {

        public static function startBuffer($isCustomHeader = true)
        {
            self::cleanLevelBuffer();

            if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
                @ob_start('ob_gzhandler');
            else
                @ob_start();

            if ($isCustomHeader == false) {
                header('Cache-Control: private, max-age=0, no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s ', time()) . 'GMT');
                header('Etag: "' . md5(time()) . '"');
            }
        }

        public static function cleanLevelBuffer()
        {
            $level = @ob_get_level();

            if ($level <= 0)
                return;

            for ($i = 0; $i < $level; ++$i) {
                if (@ob_end_clean() == false && function_exists('ob_clean'))
                    @ob_clean();
            }
        }

        public static function flushBuffer()
        {
            @ob_flush();
        }

        public static function endFlushBuffer()
        {
            @ob_end_flush();
        }

        public static function clearBuffer()
        {
            self::endCleanBuffer();
            self::startBuffer();
        }

        public static function endCleanBuffer()
        {
            @ob_end_clean();
        }

        public static function listenEndBuffer()
        {
            if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
                return;

            register_shutdown_function(function() {
                self::flushBuffer();
                self::endFlushBuffer();
            });
        }

        public static function fixMagicQuotesGpc()
        {
            $_SERVER = filter_var_array($_SERVER, FILTER_SANITIZE_STRING);
            $_GET    = filter_var_array($_GET,    FILTER_SANITIZE_STRING);

            if (get_magic_quotes_gpc()) {
                stripcslashesResursive($_GET);
                stripcslashesResursive($_POST);
                stripcslashesResursive($_REQUEST);
                stripcslashesResursive($_COOKIE);
            }
        }

    }
