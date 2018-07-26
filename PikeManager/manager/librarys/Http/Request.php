<?php

    namespace Librarys\Http;

    use Librarys\Http\Detection\SimpleDetect;

    class Request
    {

        const HTTP_CONTINUE                                     = 100;
        const HTTP_SWITCHING_PROTOCOLS                          = 101;
        const HTTP_PROCCESSING                                  = 102;

        const HTTP_CODE_OK                                      = 200;
        const HTTP_CREATED                                      = 201;
        const HTTP_ACCEPTED                                     = 202;
        const HTTP_NON_AUTHORITATIVE                            = 203;
        const HTTP_NO_CONTENT                                   = 204;
        const HTTP_RESET_CONTENT                                = 205;
        const HTTP_PARTIAL_CONTENT                              = 206;
        const HTTP_MULTI_STATUS                                 = 207;
        const HTTP_ALREADY_REPORTED                             = 208;
        const HTTP_IM_USED                                      = 226;

        const HTTP_MULTIPLE_CHOICES                             = 300;
        const HTTP_MOVED_PERMANENTLY                            = 301;
        const HTTP_FOUND                                        = 302;
        const HTTP_SEE_OTHER                                    = 303;
        const HTTP_NOT_MODIFIED                                 = 304;
        const HTTP_USE_PROXY                                    = 305;
        const HTTP_SWITCH_PROXY                                 = 306;
        const HTTP_TEMPORARY_REDIRECT                           = 307;
        const HTTP_PERMANENT_REDIRECT                           = 308;

        const HTTP_CLIENT_ERROR_BAD_REQUEST                     = 400;
        const HTTP_CLIENT_ERROR_UNAUTHORIZED                    = 401;
        const HTTP_CLIENT_ERROR_PAYMENT_REQUIRED                = 402;
        const HTTP_CLIENT_ERROR_FORBIDDEN                       = 403;
        const HTTP_CLIENT_ERROR_NOT_FOUND                       = 404;
        const HTTP_CLIENT_ERROR_METHOD_NOT_ALLOWED              = 405;
        const HTTP_CLIENT_ERROR_NOT_ACCEPTABLE                  = 406;
        const HTTP_CLIENT_ERROR_PROXY_AUTHENTICATION            = 407;
        const HTTP_CLIENT_ERROR_REQUEST_TIMEOUT                 = 408;
        const HTTP_CLIENT_ERROR_CONFLICT                        = 409;
        const HTTP_CLIENT_ERROR_GONE                            = 410;
        const HTTP_CLIENT_ERROR_LENGTH_REQUIRED                 = 411;
        const HTTP_CLIENT_ERROR_PRECONDITION_FAILED             = 412;
        const HTTP_CLIENT_ERROR_PAYLOAD_TOO_LARGE               = 413;
        const HTTP_CLIENT_ERROR_URI_TOO_LONG                    = 414;
        const HTTP_CLIENT_ERROR_UNSUPPORTED_MEDIA_TYPE          = 415;
        const HTTP_CLIENT_ERROR_RANGE_NOT_SATISFIABLE           = 416;
        const HTTP_CLIENT_ERROR_EXPECTATION_FAILED              = 417;
        const HTTP_CLIENT_ERROR_IM_A_TEAPOT                     = 418;
        const HTTP_CLIENT_ERROR_MISDIRECTED_REQUEST             = 421;
        const HTTP_CLIENT_ERROR_UNPROCESSABLE_ENTITY            = 422;
        const HTTP_CLIENT_ERROR_LOCKED                          = 423;
        const HTTP_CLIENT_ERROR_FAILED_DEPENDENCY               = 424;
        const HTTP_CLIENT_ERROR_UPGRADE_REQUIRED                = 426;
        const HTTP_CLIENT_ERROR_PRECONDITION_REQUIRED           = 428;
        const HTTP_CLIENT_ERROR_TOO_MANY_REQUESTS               = 429;
        const HTTP_CLIENT_ERROR_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
        const HTTP_CLIENT_ERROR_UNAVAILABLE_FOR_LEGAL_REASONS   = 451;

        const HTTP_SERVER_ERROR_INTERNAL_SERVER_ERROR           = 500;
        const HTTP_SERVER_ERROR_NOT_IMPLEMENTED                 = 501;
        const HTTP_SERVER_ERROR_BAD_GATEWAY                     = 502;
        const HTTP_SERVER_ERROR_SERVICE_UNAVAILABLE             = 503;
        const HTTP_SERVER_ERROR_GATEWAY_TIMEOUT                 = 504;
        const HTTP_SERVER_ERROR_HTTP_VERSION_NOT_SUPPORTED      = 505;
        const HTTP_SERVER_ERROR_VARIANT_ALSO_NEGOTIATES         = 506;
        const HTTP_SERVER_ERROR_INSUFFICIENT_STORAGE            = 507;
        const HTTP_SERVER_ERROR_LOOP_DETECTED                   = 508;
        const HTTP_SERVER_ERROR_NOT_EXTENDED                    = 510;
        const HTTP_SERVER_ERROR_NETWORK_AUTHENTICATION_REQUIRED = 511;

        private static $httpCodeStrings = null;

        public static function getHttpCodeStrings()
        {
            if (self::$httpCodeStrings == null) {
                self::$httpCodeStrings = [
                    self::HTTP_CONTINUE                                     => '100 Continue',
                    self::HTTP_SWITCHING_PROTOCOLS                          => '101 Switching_protocols',
                    self::HTTP_PROCCESSING                                  => '102 Processing',

                    self::HTTP_CODE_OK                                      => '200 OK',
                    self::HTTP_CREATED                                      => '201 Created',
                    self::HTTP_ACCEPTED                                     => '202 Accepted',
                    self::HTTP_NON_AUTHORITATIVE                            => '203 Non-Authoritative Information',
                    self::HTTP_NO_CONTENT                                   => '204 No Content',
                    self::HTTP_RESET_CONTENT                                => '205 Reset Content',
                    self::HTTP_PARTIAL_CONTENT                              => '206 Partial Content',
                    self::HTTP_MULTI_STATUS                                 => '207 Multi-Status',
                    self::HTTP_ALREADY_REPORTED                             => '208 Already Reported',
                    self::HTTP_IM_USED                                      => '226 IM Used',

                    self::HTTP_MULTIPLE_CHOICES                             => '300 Multiple Choices',
                    self::HTTP_MOVED_PERMANENTLY                            => '301 Moved Permanently',
                    self::HTTP_FOUND                                        => '302 Found',
                    self::HTTP_SEE_OTHER                                    => '303 See Other',
                    self::HTTP_NOT_MODIFIED                                 => '304 Not Modified',
                    self::HTTP_USE_PROXY                                    => '305 Use Proxy',
                    self::HTTP_SWITCH_PROXY                                 => '306 Switch Proxy',
                    self::HTTP_TEMPORARY_REDIRECT                           => '307 Temporary Redirect',
                    self::HTTP_PERMANENT_REDIRECT                           => '308 Permanent Redirect',

                    self::HTTP_CLIENT_ERROR_BAD_REQUEST                     => '400 Bad Request',
                    self::HTTP_CLIENT_ERROR_UNAUTHORIZED                    => '401 Unauthorized',
                    self::HTTP_CLIENT_ERROR_PAYMENT_REQUIRED                => '402 Payment Required',
                    self::HTTP_CLIENT_ERROR_FORBIDDEN                       => '403 Forbidden',
                    self::HTTP_CLIENT_ERROR_NOT_FOUND                       => '404 Not Found',
                    self::HTTP_CLIENT_ERROR_METHOD_NOT_ALLOWED              => '405 Method Not Allowed',
                    self::HTTP_CLIENT_ERROR_NOT_ACCEPTABLE                  => '406 Not Acceptable',
                    self::HTTP_CLIENT_ERROR_PROXY_AUTHENTICATION            => '407 Proxy Authentication Required',
                    self::HTTP_CLIENT_ERROR_REQUEST_TIMEOUT                 => '408 Request Timeout',
                    self::HTTP_CLIENT_ERROR_CONFLICT                        => '409 Conflict',
                    self::HTTP_CLIENT_ERROR_GONE                            => '410 Gone',
                    self::HTTP_CLIENT_ERROR_LENGTH_REQUIRED                 => '411 Length Required',
                    self::HTTP_CLIENT_ERROR_PRECONDITION_FAILED             => '412 Precondition Failed',
                    self::HTTP_CLIENT_ERROR_PAYLOAD_TOO_LARGE               => '413 Payload Too Large',
                    self::HTTP_CLIENT_ERROR_URI_TOO_LONG                    => '414 URI Too Long',
                    self::HTTP_CLIENT_ERROR_UNSUPPORTED_MEDIA_TYPE          => '415 Unsupported Media Type',
                    self::HTTP_CLIENT_ERROR_RANGE_NOT_SATISFIABLE           => '416 Range Not Satisfiable',
                    self::HTTP_CLIENT_ERROR_EXPECTATION_FAILED              => '417 Expectation Failed',
                    self::HTTP_CLIENT_ERROR_IM_A_TEAPOT                     => '418 I\'m a teapot',
                    self::HTTP_CLIENT_ERROR_MISDIRECTED_REQUEST             => '421 Misdirected Request',
                    self::HTTP_CLIENT_ERROR_UNPROCESSABLE_ENTITY            => '422 Unprocessable Entity',
                    self::HTTP_CLIENT_ERROR_LOCKED                          => '423 Locked',
                    self::HTTP_CLIENT_ERROR_FAILED_DEPENDENCY               => '424 Failed Dependency',
                    self::HTTP_CLIENT_ERROR_UPGRADE_REQUIRED                => '426 Upgrade Required',
                    self::HTTP_CLIENT_ERROR_PRECONDITION_REQUIRED           => '428 Precondition Required',
                    self::HTTP_CLIENT_ERROR_TOO_MANY_REQUESTS               => '429 Too Many Requests',
                    self::HTTP_CLIENT_ERROR_REQUEST_HEADER_FIELDS_TOO_LARGE => '431 Request Header Fields Too Large',
                    self::HTTP_CLIENT_ERROR_UNAVAILABLE_FOR_LEGAL_REASONS   => '451 Unavailable For Legal Reasons',

                    self::HTTP_SERVER_ERROR_INTERNAL_SERVER_ERROR           => '500 Internal Server',
                    self::HTTP_SERVER_ERROR_NOT_IMPLEMENTED                 => '501 Not Implemented',
                    self::HTTP_SERVER_ERROR_BAD_GATEWAY                     => '502 Bad Gateway',
                    self::HTTP_SERVER_ERROR_SERVICE_UNAVAILABLE             => '503 Service Unavailable',
                    self::HTTP_SERVER_ERROR_GATEWAY_TIMEOUT                 => '504 Gateway Timeout',
                    self::HTTP_SERVER_ERROR_HTTP_VERSION_NOT_SUPPORTED      => '505 HTTP Version Not Supported',
                    self::HTTP_SERVER_ERROR_VARIANT_ALSO_NEGOTIATES         => '506 Variant Also Negotiates',
                    self::HTTP_SERVER_ERROR_INSUFFICIENT_STORAGE            => '507 Insufficient Storage',
                    self::HTTP_SERVER_ERROR_LOOP_DETECTED                   => '508 Loop Detected',
                    self::HTTP_SERVER_ERROR_NOT_EXTENDED                    => '510 Not Extended',
                    self::HTTP_SERVER_ERROR_NETWORK_AUTHENTICATION_REQUIRED => '511 Network Authentication Required'
                ];
            }

            return self::$httpCodeStrings;
        }

        public static function session()
        {
            return Session::getInstance();
        }

        public static function ip()
        {
            $arrayIP = array();

            if (getenv('HTTP_X_FORWARDED_FOR') !== false) {
                $forwarded = getenv('HTTP_X_FORWARDED_FOR');
                $forwarded = array_reverse(explode(',', $forwarded));

                if (is_array($forwarded)) {
                    foreach ($forwarded AS $entry) {
                        $entry = trim($entry);
                        if (Validate::ip($entry))
                            $arrayIP[] = $entry;
                    }
                }
            }

            if (getenv('HTTP_CLIENT_IP') !== false)
                $arrayIP[] = trim(getenv('HTTP_CLIENT_IP'));

            if (getenv('HTTP_PROXY_USER') !== false)
                $arrayIp[] = trim(getenv('HTTP_PROXY_USER'));

            if (getenv('REMOTE_ADDR') !== false)
                $arrayIP[] = trim(getenv('REMOTE_ADDR'));

            foreach ($arrayIP AS $ip) {
                if (Validate::ip($ip))
                    return $ip;
            }

            return null;
        }

        public static function userAgent()
        {
            if (getenv('HTTP_USER_AGENT') !== false)
                return getenv('HTTP_USER_AGENT');

            return null;
        }

        public static function scheme()
        {
            $requestScheme = 'http';

            // If server using reverce proxy
            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp(trim($_SERVER['HTTP_X_FORWARDED_PROTO']), 'https') === 0) {
                $requestScheme = 'https';
            } else if (isset($_SERVER['HTTP_HTTPS']) || isset($_SERVER['HTTPS'])) {
                $httpHttps = null;

                if (isset($_SERVER['HTTPS']))
                    $httpHttps = trim($_SERVER['HTTPS']);
                else if (isset($_SERVER['HTTP_HTTPS']))
                    $httpHttps = trim($_SERVER['HTTP_HTTPS']);

                if (empty($httpHttps))
                    $requestScheme = 'http';
                else if (strcasecmp($httpHttps, 'on') === 0 || strcasecmp($httpHttps, '1') || $httpHttps == true)
                    $requestScheme = 'https';
            }

            return $requestScheme;
        }

        public static function isLocal($igoneWebLocal = false)
        {
            $host = $_SERVER['HTTP_HOST'];
            $ip   = self::ip();

            if (preg_match('/(localhost|127\.0\.0\.1)(:8080)?/i', $ip) || strcasecmp($host, 'izerocs.local') === 0)
                return true;

            return false;
        }

        public static function isUseManagerDemo()
        {
            return strpos(env('app.http.host'), 'demo-manager') !== false;
        }

        public static function isDesktop($checkMethod = true)
        {
            return false;

            if (SimpleDetect::getInstance()->getDeviceType() !== SimpleDetect::DEVICE_TYPE_COMPUTER)
                return false;

            if (env('app.dev.enable_desktop') == false || Request::isLocal() == false)
                return false;

            if ($checkMethod && self::isMethodPost() == false)
                return false;

            return true;
        }

        public static function isMethodGet()
        {
            return strcasecmp($_SERVER['REQUEST_METHOD'], 'GET') === 0;
        }

        public static function isMethodPost()
        {
            return strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') === 0;
        }

        public static function redirect($url)
        {
            header('Location:' . $url);
            exit(255);
        }

        public static function httpResponseCodeToString($code)
        {
            $array = self::getHttpCodeStrings();

            if (array_key_exists($code, $array) == true)
                return $array[$code];

            return $code;
        }

    }
