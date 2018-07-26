<?php

    namespace Librarys\Http;

    class Validate
    {

        public static function ip($ip)
        {
            if ($ip === null || is_string($ip) == false)
                return false;

            if (function_exists('filter_var'))
                return filter_var($ip, FILTER_VALIDATE_IP);

            if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip) != false)
                return true;

            if (preg_match('/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?7)){3})\z/i', $ip) != false)
                return true;

            return false;
        }

        public static function url($url)
        {
            if (empty($url))
                return false;

            $url = addPrefixHttpURL($url);

            if (function_exists('filter_var')) {
                if (filter_var($url, FILTER_VALIDATE_URL))
                    return true;
            } else if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
                return true;
            }

            return false;
        }

        public static function email($email)
        {
            if (empty($email))
                return false;

            if (function_exists('filter_var') == false) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL))
                    return true;
            } else if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $email)) {
                return true;
            }

            return false;
        }

    }
