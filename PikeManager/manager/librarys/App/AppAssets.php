<?php

    namespace Librarys\App;

    use Librarys\App\AppDirectory;
    use Librarys\File\FileInfo;
    use Librarys\Http\Request;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Config\AppAboutConfig;
    use Librarys\App\Config\AppAssetsConfig;
    use Librarys\Exception\RuntimeException;

    final class AppAssets
    {

        private static $uid;
        private static $rand;

        public static function getUID()
        {
            if (self::$uid == null)
                self::$uid = md5(AppAboutConfig::getInstance()->getSystem(AppAboutConfig::ARRAY_KEY_BUILD_AT));

            return self::$uid;
        }

        public static function getRand()
        {
            if (self::$rand == null)
                self::$rand = intval($_SERVER['REQUEST_TIME']);

                return self::$rand;
        }

        public static function makeURLResourceTheme($themeDirectory, $filename)
        {
            $rootPath  = env('app.path.root');
            $themePath = env('app.path.theme');
            $filename  = str_ireplace('.css', null, basename($filename));
            $themePath = FileInfo::filterPaths($themePath . SP . $themeDirectory);

            if (FileInfo::isTypeDirectory($themePath) == false)
                return null;

            $themeFilename = $filename . '.css';
            $themeFilepath = FileInfo::filterPaths($themePath . SP . $themeFilename);

            if (FileInfo::isTypeFile($themeFilepath) == false)
                return null;

            $buffer  = env('app.http.host');
            $buffer .= substr($themeFilepath, strlen($rootPath));

            if (env('app.dev.enable') || Request::isLocal()) {
                $buffer .= '?' . ASSET_PARAMETER_RAND_URL . '=' . self::getRand();
                $buffer .= '&' . ASSET_PARAMETER_UID_URL  . '=' . self::getUID();
            } else {
                $buffer .= '?' . ASSET_PARAMETER_UID_URL  . '=' . self::getUID();
            }

            return separator($buffer, '/');
        }

        public static function makeURLResourceJavascript($filename, $scriptDirectory = null)
        {
            $filename = str_ireplace('.js', null, basename($filename));
            $root     = env('app.path.root');
            $jsPath   = env('app.path.javascript');

            if ($scriptDirectory != null)
                $jsPath .= SP . $scriptDirectory;

            if (FileInfo::isTypeDirectory($jsPath) == false)
                die(lng('default.resource.directory_not_found'));

            $jsFilename = $filename . '.js';
            $jsFilePath = FileInfo::filterPaths($jsPath . SP . $jsFilename);

            if (FileInfo::isTypeFile($jsFilePath) == false)
                return null;

            $buffer  = env('app.http.host');
            $buffer .= substr($jsFilePath, strlen($root));

            if (env('app.dev.enable') || Request::isLocal()) {
                $buffer .= '?' . ASSET_PARAMETER_RAND_URL . '=' . self::getRand();
                $buffer .= '&' . ASSET_PARAMETER_UID_URL  . '=' . self::getUID();
            } else {
                $buffer .= '?' . ASSET_PARAMETER_UID_URL  . '=' . self::getUID();
            }

            return separator($buffer, '/');
        }

        public static function makeURLResourceIcon($themeDirectory, $filename)
        {
            return env('app.http.theme') . '/' . $themeDirectory . '/icon/' . $filename;
        }

    }

?>