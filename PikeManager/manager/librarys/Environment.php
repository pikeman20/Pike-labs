<?php

    namespace Librarys;

    require_once('File' . SP . 'FileInfo.php');

    if (defined('LOADED') == false)
        exit;

    use Librarys\File\FileInfo;
    use Librarys\Http\Request;
    use Librarys\App\Config\AppEnvironmentCacheConfig;

    class Environment
    {

        private static $instance;

        private $isUpdateCache;
        private $configPath;
        private $cacheDirectory;
        private $cacheFilePath;
        private $cache;
        private $array;


        protected function __construct($configPath, $cacheDirectory)
        {
            global $_SERVER, $_POST, $_GET, $_REQUEST, $_COOKIE, $_SESSION;

            $this->configPath     = $configPath;
            $this->cacheDirectory = $cacheDirectory;
            $this->cacheFilePath  = $cacheDirectory . SP . md5($configPath);
            $this->cache          = array();
            $this->array          = require_once($configPath);

            register_shutdown_function(function() {
                if ($this->isUpdateCache == false)
                    return;

               if (FileInfo::isTypeDirectory($this->cacheDirectory) == false)
                   FileInfo::mkdir($this->cacheDirectory, true);

               $cacheConfig = new AppEnvironmentCacheConfig($this->cacheFilePath);
               $cacheConfig->setConfigArraySystem($this->cache);
               $cacheConfig->write(true);
            });
        }

        protected function __wakeup()
        {

        }

        protected function __clone()
        {

        }

        public static function getInstance($configPath = null, $cacheDirectory = null)
        {
            if (null === self::$instance) {
                if ($configPath == null || $cacheDirectory == null)
                    return null;

                self::$instance = new Environment($configPath, $cacheDirectory);
            }

            return self::$instance;
        }

        public function execute()
        {
            if ($this->loadCacheFile()) {
                return;
            } else {
                $this->cache         = array();
                $this->isUpdateCache = true;
            }

            $requestScheme = Request::scheme();

            $this->cache('server.document_root',  env('SERVER.DOCUMENT_ROOT',  dirname(__DIR__)));
            $this->cache('server.request_scheme', $requestScheme);
            $this->cache('server.http_host',      env('server.request_scheme', $requestScheme) . '://' . env('SERVER.HTTP_HOST', '/'));

            if (Request::isLocal() == false)
                $this->setCache('app.dev.enable', false);

            $this->cache('app.date.timezone', 'Asia/Ho_Chi_Minh');

            $this->cache('app.autoload.prefix_namespace', 'Librarys');
            $this->cache('app.autoload.prefix_class_mime', '.php');

            $this->cache('app.path.root',       dirname(__DIR__));
            $this->cache('app.path.librarys',   env('app.path.root') . SP . 'librarys');
            $this->cache('app.path.error',      env('app.path.root') . SP . 'error');
            $this->cache('app.path.resource',   env('app.path.root') . SP . 'resource');
            $this->cache('app.path.theme',      env('app.path.resource') . SP . 'theme');
            $this->cache('app.path.icon',       env('app.path.resource') . SP . 'icon');
            $this->cache('app.path.javascript', env('app.path.resource') . SP . 'javascript');

            if (strcmp(env('server.document_root'), env('app.path.root')) === 0) {
                $this->cache('app.directory', '');
                $this->cache('app.directory_absolute', '');
            } else {
                $appDirectory         = FileInfo::filterPaths(substr(env('app.path.root'), strlen(env('server.document_root'))));
                $appDirectoryAbsolute = FileInfo::filterPaths(substr(env('app.path.root'), strlen(env('SERVER.DOCUMENT_ROOT'))));

                if (strpos($appDirectory, SP) === 0)
                    $appDirectory = substr($appDirectory, 1);

                if (strpos($appDirectoryAbsolute, SP) === 0)
                    $appDirectoryAbsolute = substr($appDirectoryAbsolute, 1);

                $this->cache('app.directory',          $appDirectory);
                $this->cache('app.directory_absolute', $appDirectoryAbsolute);
            }

            $this->cache('app.directory_absolute_http', separator(env('app.directory_absolute'), '/'));

            if (env('app.directory_absolute') == null || env('app.directory_absolute') == '')
                $this->cache('app.http.host', env('server.http_host'));
            else
                $this->cache('app.http.host', separator(env('server.http_host') . '/' . env('app.directory_absolute_http'), '/'));

            $lengthRoot     = strlen(env('app.path.root')) + 1;
            $lengthResource = strlen(env('app.path.resource')) + 1;

            $this->cache('app.http.resource',   separator(env('app.http.host')     . SP . substr(env('app.path.resource'),   $lengthRoot),     '/'));
            $this->cache('app.http.theme',      separator(env('app.http.resource') . SP . substr(env('app.path.theme'),      $lengthResource), '/'));
            $this->cache('app.http.icon',       separator(env('app.http.resource') . SP . substr(env('app.path.icon'),       $lengthResource), '/'));
            $this->cache('app.http.javascript', separator(env('app.http.resource') . SP . substr(env('app.path.javascript'), $lengthResource), '/'));

            $this->cache('app.session.init',            false);
            $this->cache('app.session.name',            session_name());
            $this->cache('app.session.cookie_lifetime', ini_get('session.cookie_lifetime'));
            $this->cache('app.session.cookie_path',     ini_get('session.cookie_path'));
            $this->cache('app.session.cache_limitter',  session_cache_limiter());
            $this->cache('app.session.cache_expire',    session_cache_expire());

            $this->cache('app.language.path',   env('app.path.resource') . SP . 'language');
            $this->cache('app.language.mime',   '.php');
            $this->cache('app.language.locale', 'en');

            $this->cache('app.cfsr.use_token',   true);
            $this->cache('app.cfsr.key_name',    '_cfsr_token');
            $this->cache('app.cfsr.time_live',   60000);

            if (env('app.directory_absolute') == '')
                $this->cache('app.cfsr.path_cookie', '/');
            else
                $this->cache('app.cfsr.path_cookie', env('app.directory_absolute_http'));

            $this->cache('app.cfsr.validate_post', true);
            $this->cache('app.cfsr.validate_get',  true);
        }

        public static function env($name, $default = null)
        {
            if (self::$instance instanceof Environment == false)
                return null;

            if (preg_match('/^(SERVER|POST|GET|REQUEST|GLOBALS|COOKIE|SESSION)(\.(.*?))?$/s', $name, $matches)) {
                if ($matches[1] != 'GLOBALS')
                    $matches[1] = '_' . $matches[1];

                if (count($matches) <= 2) {
                    if (isset($GLOBALS[$matches[1]]))
                        return $GLOBALS[$matches[1]];

                    return null;
                }

                if (isset($GLOBALS[$matches[1]]) == false)
                    return null;

                return self::$instance->get($matches[3], $default, $GLOBALS[$matches[1]]);
            }

            if (array_key_exists($name, self::$instance->cache))
                return self::$instance->cache[$name];
            else
                self::$instance->isUpdateCache = true;

            return (self::$instance->cache[$name] = urlSeparatorMatches(self::$instance->get($name, $default)));
        }

        private function cache($name, $default = null, $recache = false)
        {
            if ($recache && array_key_exists($name, self::$instance->cache))
                unset(self::$instance->cache[$name]);

            self::$instance->cache[$name] = self::env($name, $default);
        }

        private function setCache($name, $value)
        {
            self::$instance->cache[$name] = $value;
        }

        private function loadCacheFile()
        {
            if (FileInfo::fileExists($this->cacheDirectory) == false && FileInfo::mkdir($this->cacheDirectory, true) == false)
                return false;

            if (FileInfo::fileExists($this->cacheFilePath) == false)
                return false;

            $timeNow             = intval($_SERVER['REQUEST_TIME']);
            $configFileTime      = FileInfo::fileMTime($this->configPath);
            $configCacheFileTime = FileInfo::fileMTime($this->cacheFilePath);

            if ($configFileTime > $configCacheFileTime || $timeNow - $configCacheFileTime > 64800)
                return false;

            $this->cache = require_once($this->cacheFilePath);

            if (is_array($this->cache)) {
                $serverHttpHostCache = env('server.http_host');
                $appHttpHostCache    = env('app.http.host');
                $requestScheme       = Request::scheme() . '://';

                if (strpos($serverHttpHostCache, $requestScheme) === false)
                    return false;

                if (strpos($appHttpHostCache, $requestScheme) === false)
                    return false;

                if (strpos($appHttpHostCache, $serverHttpHostCache) !== 0)
                    return false;

                $serverHttpHost = $_SERVER['HTTP_HOST'];
                $httpScheme     = 'http://';
                $httpsScheme    = 'https://';

                if (strpos($serverHttpHostCache, $httpScheme) === 0)
                    $serverHttpHostCache = substr($serverHttpHostCache, strlen($httpScheme));
                else if (strpos($serverHttpHostCache, $httpsScheme) === 0)
                    $serverHttpHostCache = substr($serverHttpHostCache, strlen($httpsScheme));

                if (strpos($serverHttpHostCache, $serverHttpHost) === false)
                    return false;
            }

            return true;
        }

        private function get($key, $default = null, $array = null)
        {
            if (is_string($key) && empty($key) == false) {
                if ($array == null)
                    $array = $this->array;

                $keys  = explode('.', $key);

                if (is_array($keys) == false)
                    $keys = array($key);

                foreach ($keys AS $entry) {
                    $entry = trim($entry);

                    if (array_key_exists($entry, $array) == false)
                        return $default;

                    $array = $array[$entry];
                }

                return self::envMatchesString($array);
            }

            return $default;
        }

        public static function envMatchesString($str)
        {
            if (is_array($str) || preg_match('/\$\{(.+?)\}/si', $str, $matches) == false)
                return $str;

            return preg_replace_callback('/\$\{(.+?)\}/si', function($matches) {
                $result = null;

                if (isset($GLOBALS[$matches[1]]))
                    $result = $GLOBALS[$matches[1]];
                else if (defined($matches[1]))
                    $result = constant($matches[1]);
                else
                    $result = env(trim($matches[1]));

                if (is_array($result))
                    return 'Array';
                else if (is_object($result))
                    return 'Object';
                else if (is_resource($result))
                    return 'Resource';

                return $result;
            }, $str);
        }

    }

?>