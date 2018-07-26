<?php

    namespace Librarys\Http;

    class Uri
    {

        const SCHEME_HTTP  = 'http';
        const SCHEME_HTTPS = 'https';

        protected function __construct()
        {

        }

        protected function __clone()
        {

        }

        protected function __wakeup()
        {

        }

        public static function urlAddPrefixScheme($url, $scheme = self::SCHEME_HTTP)
        {
            $separator = '://';

            if (stripos($url, self::SCHEME_HTTP . $separator) === 0)
                return $url;

            if (stripos($url, self::SCHEME_HTTPS . $separator) === 0)
                return $url;

            return $scheme . $separator . $url;
        }

        public static function seoMaker($string)
        {
            $string = preg_replace('/(â|ầ|ầ|ấ|ấ|ậ|ậ|ẩ|ẩ|ẫ|ẫ|ă|ằ|ằ|ắ|ắ|ặ|ặ|ẳ|ẳ|ẵ|ẵ|à|à|á|á|ạ|ạ|ả|ả|ã|ã)/', 'a', $string);
            $string = preg_replace('/(ê|ề|ề|ế|ế|ệ|ệ|ể|ể|ễ|ễ|è|è|é|é|ẹ|ẹ|ẻ|ẻ|ẽ|ẽ)/', 'e', $string);
            $string = preg_replace('/(ì|ì|í|í|ị|ị|ỉ|ỉ|ĩ|ĩ)/', 'i', $string);
            $string = preg_replace('/(ô|ồ|ồ|ố|ố|ộ|ộ|ổ|ổ|ỗ|ỗ|ơ|ờ|ờ|ớ|ớ|ợ|ợ|ở|ở|ỡ|ỡ|ò|ò|ó|ó|ọ|ọ|ỏ|ỏ|õ|õ)/', 'o', $string);
            $string = preg_replace('/(ư|ừ|ừ|ứ|ứ|ự|ự|ử|ử|ữ|ữ|ù|ù|ú|ú|ụ|ụ|ủ|ủ|ũ|ũ)/', 'u', $string);
            $string = preg_replace('/(ỳ|ỳ|ý|ý|ỵ|ỵ|ỷ|ỷ|ỹ|ỹ)/', 'y', $string);
            $string = preg_replace('/(đ)/', 'd', $string);
            $string = preg_replace('/(B)/', 'b', $string);
            $string = preg_replace('/(C)/', 'c', $string);
            $string = preg_replace('/(D)/', 'd', $string);
            $string = preg_replace('/(F)/', 'f', $string);
            $string = preg_replace('/(G)/', 'g', $string);
            $string = preg_replace('/(H)/', 'h', $string);
            $string = preg_replace('/(J)/', 'j', $string);
            $string = preg_replace('/(K)/', 'k', $string);
            $string = preg_replace('/(L)/', 'l', $string);
            $string = preg_replace('/(M)/', 'm', $string);
            $string = preg_replace('/(N)/', 'n', $string);
            $string = preg_replace('/(P)/', 'p', $string);
            $string = preg_replace('/(Q)/', 'q', $string);
            $string = preg_replace('/(R)/', 'r', $string);
            $string = preg_replace('/(S)/', 's', $string);
            $string = preg_replace('/(T)/', 't', $string);
            $string = preg_replace('/(V)/', 'v', $string);
            $string = preg_replace('/(W)/', 'w', $string);
            $string = preg_replace('/(X)/', 'x', $string);
            $string = preg_replace('/(Z)/', 'z', $string);
            $string = preg_replace('/(Â|Ầ|Ầ|Ấ|Ấ|Ậ|Ậ|A|Ẩ|Ẩ|Ẫ|Ẫ|Ă|Ắ|Ằ|Ằ|Ắ|Ặ|Ặ|Ẳ|Ẳ|Ẵ|Ẵ|À|À|Á|Á|Ạ|Ạ|Ả|Ả|Ã|Ã)/', 'a', $string);
            $string = preg_replace('/(Ẽ|Ẽ|Ê|Ề|E|Ề|Ế|Ế|Ệ|Ệ|Ể|Ể|Ễ|Ễ|È|È|É|É|Ẹ|Ẹ|Ẻ|Ẻ)/', 'e', $string);
            $string = preg_replace('/(Ì|Ì|Í|Í|Ị|Ị|I|Ỉ|Ỉ|Ĩ|Ĩ)/', 'i', $string);
            $string = preg_replace('/(Ô|Ồ|Ồ|Ố|Ố|O|Ộ|Ộ|Ổ|Ổ|Ỗ|Ỗ|Ờ|Ơ|Ờ|Ớ|Ớ|Ợ|Ợ|Ở|Ở|Ỡ|Ỡ|Ò|Ò|Ó|Ó|Ọ|Ọ|Ỏ|Ỏ|Õ|Õ)/', 'o', $string);
            $string = preg_replace('/(Ư|Ừ|Ừ|U|Ứ|Ứ|Ự|Ự|Ử|Ử|Ữ|Ữ|Ù|Ù|Ú|Ú|Ụ|Ụ|Ủ|Ủ|Ũ|Ũ)/', 'u', $string);
            $string = preg_replace('/(Ỳ|Ỳ|Ý|Ý|Ỵ|Y|Ỵ|Ỷ|Ỷ|Ỹ|Ỹ)/', 'y', $string);
            $string = preg_replace('/(́|̀|̉|̃||̣)/', '', $string);
            $string = preg_replace('/(Đ)/', 'd', $string);
            $string = str_replace(' ', '-', $string);
            $string = str_replace('_', '-', $string);
            $string = str_replace("\n", '-', $string);
            $string = str_replace(',', '', $string);
            $string = str_replace(array('?', '“', '”', '"', '#', ';', ':', '!', '\'', '.', '&ldquo;', '&rdquo;', '&quot;', '&laquo;', '&raquo;', '&bull;', '&ETH;', '&Eth;', '&eth;', '&hellip', '&nbsp;', '&ndash;', '&amp', '<', '>', '&lt;', '&gt;'), '-', $string);
            $string = preg_replace("/-{2,100}/", '-', $string);

            return $string;
        }

    }
