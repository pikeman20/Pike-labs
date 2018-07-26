<?php

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('LOADED') == false)
        exit;

    use Librarys\Environment;
    use Librarys\Language;
    use Librarys\File\FileInfo;
    use Librarys\Http\Validate;
    use Librarys\Http\Secure\CFSRToken;
    use Librarys\Text\Encryption\Services_JSON;

    function env($name, $default = null, $renew = false)
    {
        return Environment::env($name, $default, $renew);
    }

    /**
     * [lng Load value for key language]
     * @param  [string] $name [Key name language]
     * @param  [array|string] $params [Array params replace in language result]
     * @return [string]       [Value for key language]
     */
    function lng($name)
    {
        $params = null;

        if (is_array($params) == false) {
            $nums = func_num_args() - 1;
            $args = func_get_args();

            if ($nums >= 1 && is_array($args[1]))
                $params = $args[1];
            else if ($nums > 0 && $nums % 2 == 0)
                $params = array_splice($args, 1, $nums);
        }

        return Language::lng($name, $params);
    }

    /**
     * [lngToJson List language to json]
     * @param  [array]  $argument... [List file language load]
     * @return [string] [Json array string]
     */
    function lngToJson()
    {
        if (is_array($load) == false) {
            $load = array();
            $nums = func_num_args();

            if ($nums > 0)
                $load = func_get_args();
        }

        return Language::lngToJson($load);
    }

    function separator($str, $separator = SP)
    {
        $str = str_replace('/',  $separator, $str);
        $str = str_replace('\\', $separator, $str);

        return $str;
    }

    function urlSeparatorMatches($str)
    {
        if (Validate::ip($str))
            $str = separator($str, '/');

        return $str;
    }

    function bug($var)
    {
        echo('<pre>');
        var_dump($var);
        echo('</pre>');
    }

    function stripcslashesResursive(&$value)
    {
        if (is_array($value) == false)
            $value = stripslashes($value);
        else
            array_walk_recursive($value, __FUNCTION__);
    }

    function requireDefine($filename)
    {
        if ($filename == null)
            return;

        $path  = env('app.path.define') . SP;
        $path .= $filename . '.php';

        if (FileInfo::isTypeFile($path))
            require_once($path);
    }

    function countStringArray($array, $search, $isLowerCase = false)
    {
        $count = 0;

        if ($array != null && is_array($array)) {
            foreach ($array AS $entry) {
                if ($isLowerCase)
                    $entry = strtolower($entry);

                if ($entry == $search)
                    ++$count;
            }
        }

        return $count;
    }

    function isInArray($array, $search, $isLowerCase)
    {
        if ($array == null || !is_array($array))
            return false;

        foreach ($array AS $entry) {
            if ($isLowerCase)
                $entry = strtolower($entry);

            if ($entry == $search)
                return true;
        }

        return false;
    }

    function addslashesArray($array)
    {
        if (is_array($array) == false)
            return $array;

        foreach ($array AS &$value) {
            if (is_array($value))
                $value = addslashesArray($value);
            else
                $value = addslashes($value);
        }

        return $array;
    }

    function stripslashesArray($array)
    {
        if (is_array($array) == false)
            return $array;

        foreach ($array AS &$value) {
            if (is_array($value))
                $value = stripslashesArray($value);
            else
                $value = stripslashes($value);
        }

        return $array;
    }

    function addPrefixHttpURL($url, $prefix = 'http://')
    {
        $posHttp  = stripos($url, 'http://');
        $posHttps = stripos($url, 'https://');

        if ($posHttp === 0 || $posHttps === 0)
            return $url;

        if ($prefix == null || empty($prefix))
            $prefix = 'http://';

        return $prefix . $url;
    }

    function removePrefixHttpURL($url)
    {
        if (stripos($url, 'http://') !== false)
            return substr($url, 7);
        else if (stripos($url, 'https://') !== false)
            return substr($url, 8);

        return $url;
    }

    function baseNameURL($url)
    {
        $parseURLPath = @parse_url($url, PHP_URL_PATH);

        if ($parseURLPath === false)
            return $url;

        return basename($parseURLPath);
    }

    function jsonEncode($var)
    {
        if (function_exists('json_encode'))
            return @json_encode($var);

        $json   = new Services_JSON();
        $result = $json->encode($var);

        return $result;
    }

    function jsonDecode($var, $assoc = true)
    {
        if (function_exists('json_decode'))
            return @json_decode($var, $assoc);

        $json = new Services_JSON($assoc ? SERVICES_JSON_LOOSE_TYPE : 0);
        $result = $json->decode($var);

        return $result;
    }

    function cfsrTokenName()
    {
        return CFSRToken::getInstance()->getName();
    }

    function cfsrTokenValue()
    {
        return CFSRToken::getInstance()->getToken();
    }

    function installUpgradeCallbackExtractZip($event, $header)
    {
        if (FileInfo::isTypeFile($header['filename'])) {
            if (FileInfo::unlink($header['filename']) == false)
                return 0;
        }
        return 1;
    }

    function installAdditionalCallbackExtractZip($event, $header)
    {
        if (FileInfo::isTypeFile($header['filename'])) {
            if (FileInfo::unlink($header['filename']) == false)
                return 0;
        }
        return 1;
    }

    if (function_exists('boolval') == false) {
        function boolval($val)
        {
            return !!$val;
        }
    }