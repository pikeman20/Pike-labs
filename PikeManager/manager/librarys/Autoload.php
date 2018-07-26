<?php

    namespace Librarys;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('LOADED') == false)
        exit;

    use Exception;

    class Autoload
    {

        private static $instance;

        private $prefixNamespace;
        private $preifxClassMime;
        private $prefixs;

        const PREFIX_NAMESPACE_DEFAULT  = 'Librarys';
        const PREFIX_CLASS_MIME_DEFAULT = '.php';

        protected function __construct($prefixNamespace, $preifxClassMime)
        {
            $this->prefixNamespace = $prefixNamespace;
            $this->preifxClassMime = $preifxClassMime;
            $this->prefixs         = array();
        }

        protected function __wakeup()
        {

        }

        protected function __clone()
        {

        }

        public static function getInstance($prefixNamespace = self::PREFIX_NAMESPACE_DEFAULT, $preifxClassMime = self::PREFIX_CLASS_MIME_DEFAULT)
        {
            if (null === self::$instance)
                self::$instance = new Autoload($prefixNamespace, $preifxClassMime);

            return self::$instance;
        }

        public function execute($classes = null)
        {
            if ($classes == false) {
                if ($this->prefixNamespace != null) {
                    $array = explode('\\', $this->prefixNamespace);

                    if (is_array($array) == false)
                        $array = array($this->prefixNamespace);

                    $this->prefixs = $array;
                }

                return spl_autoload_register(array($this, __FUNCTION__));
            }

            if (($path = $this->isFileLibrarys($classes)) !== false)
                require_once($path);
            else
                throw new Exception('Class ' . $classes . ' not require');
        }

        public function isFileLibrarys($classes)
        {
            if ($classes == null)
                return false;

            $array_class  = explode('\\', $classes);

            if (is_array($array_class) == false)
                $array_class = array($classes);

            $length = count($array_class);
            $path   = null;

            for ($i = 0; $i < $length; ++$i) {
                $entry_class = trim($array_class[$i]);

                if (isset($this->prefixs[$i]) == false || strcmp(trim($this->prefixs[$i]), $entry_class) !== 0) {
                    $path .= $entry_class;

                    if ($i + 1 < $length)
                        $path .= SP;
                }
            }

            $absolute = $path . $this->preifxClassMime;

            if (@is_file(__DIR__ . SP . $absolute))
                return __DIR__ . SP . $absolute;

            return false;
        }

    }

?>