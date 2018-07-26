<?php

    namespace Librarys\Text\Encryption\Exception;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Exception\RuntimeException;

    class Services_JSON_Error extends RuntimeException
    {
        function __construct($message = 'unknown error', $code = null, $mode = null, $options = null, $userinfo = null)
        {
            parent::Exception($message);
        }
    }

?>