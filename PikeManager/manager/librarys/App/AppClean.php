<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\File\FileInfo;
    use Librarys\App\AppUser;
    use Librarys\App\AppAssets;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Config\AppUserConfig;

    final class AppClean
    {

        public static function scanAutoClean(
            $isCleanTmpAllNotCheck   = false,
            $isCleanTokenAllNotCheck = false,
            $isCleanCacheAllNotCheck = false,
            $isCleanUserAllNotCheck  = false
        ) {
            self::scanAutoCleanTmp  (AppConfig::getInstance(), $isCleanTmpAllNotCheck);
            self::scanAutoCleanToken(AppConfig::getInstance(), $isCleanTokenAllNotCheck);
            self::scanAutoCleanCache(AppConfig::getInstance(), $isCleanCacheAllNotCheck);
            self::scanAutoCleanUser (AppConfig::getInstance(), $isCleanUserAllNotCheck);

            return true;
        }

        private static function scanAutoCleanTmp($appConfig, $isCleanAllNotCheck, $directoryPath = null)
        {
            $directory = env('app.path.tmp');

            if ($directoryPath != null)
                $directory = $directoryPath;

            if (FileInfo::isTypeDirectory($directory)) {
                $time     = time();
                $lifetime = AppConfig::getInstance()->get('tmp.lifetime');
                $limit    = AppConfig::getInstance()->get('tmp.limit');
                $handle   = FileInfo::scanDirectory($directory);

                if ($handle === false)
                    return false;

                $lists = array();

                foreach ($handle AS $filename) {
                    if ($filename != '.' && $filename != '..') {
                        $filepath = FileInfo::filterPaths($directory . SP . $filename);

                        if (FileInfo::isTypeFile($filepath)) {
                            $filetime         = FileInfo::fileMTime($filepath);
                            $lists[$filename] = $filetime;
                        } else if (FileInfo::isTypeDirectory($filepath)) {
                            if (self::scanAutoCleanTmp($appConfig, $isCleanAllNotCheck, $filepath))
                                $lists[$filename] = 0;
                        }
                    }
                }

                $count       = count($lists);
                $countDelete = $count;

                if ($count <= 0)
                    return true;

                asort($lists);

                foreach ($lists AS $filename => $filetime) {
                    $filepath = FileInfo::filterPaths($directory . SP . $filename);

                    if (FileInfo::isTypeDirectory($filepath) == false) {
                        if ($isCleanAllNotCheck || $time - $filetime >= $lifetime || $countDelete >= $limit) {
                            $countDelete--;

                            if (FileInfo::isTypeFile($filepath))
                                FileInfo::unlink($filepath);
                        }
                    } else {
                        $countDelete--;
                    }
                }

                if ($countDelete <= 0)
                    return FileInfo::rrmdirSystem($filepath);

                return false;
            }
        }

        private static function scanAutoCleanToken($appConfig, $isCleanAllNotCheck)
        {
            $directory  = env('app.path.token');
            $tokenIgone = null;

            if (AppUser::getInstance()->isLogin())
                $tokenIgone = AppUser::getInstance()->getTokenValue();

            if (FileInfo::isTypeDirectory($directory)) {
                $time   = time();
                $handle = FileInfo::scanDirectory($directory);

                if ($handle === false)
                    return false;

                $tokenMaxLive = intval(AppConfig::getInstance()->get('login.time_login'));

                foreach ($handle AS $filename) {
                    if ($filename != '.' && $filename != '..') {
                        $filepath = FileInfo::filterPaths($directory . SP . $filename);

                        if (FileInfo::isTypeFile($filepath)) {
                            $tokenBuffer = FileInfo::fileReadContents($filepath);
                            $tokenArray = @unserialize($tokenBuffer);

                            if ($tokenBuffer === false || $tokenArray === false) {
                                FileInfo::unlink($filepath);
                            } else {
                                $tokenUserLive = intval($tokenArray[AppUser::TOKEN_ARRAY_KEY_USER_LIVE]);

                                if (($isCleanAllNotCheck || $time - $tokenUserLive >= $tokenMaxLive) && strcmp($tokenIgone, $filename) !== 0)
                                    FileInfo::unlink($filepath);
                            }
                        }
                    }
                }
            }
        }

        private static function scanAutoCleanCache($appConfig, $isCleanAllNotCheck)
        {
            $directory = env('app.path.cache');

            if (FileInfo::isTypeDirectory($directory)) {
                $time   = time();
                $handle = FileInfo::scanDirectory($directory);

                if ($handle === false)
                    return false;

                $cacheLifetime = AppConfig::getInstance()->get('cache.lifetime');

                foreach ($handle AS $filename) {
                    if ($filename != '.' && $filename != '..') {
                        $filepath = FileInfo::filterPaths($directory . SP . $filename);

                        if (FileInfo::isTypeFile($filepath)) {
                            $filemime      = FileInfo::extFile($filename);
                            $filetime      = FileInfo::fileMTime($filepath);

                            if ($isCleanAllNotCheck || $time - $filetime >= $cacheLifetime)
                                FileInfo::unlink($filepath);
                        }
                    }
                }
            }
        }

        private static function scanAutoCleanUser($appConfig, $isCleanAllNotCheck)
        {
            if (AppUserConfig::getInstance()->hasEntryConfigArraySystem()) {
                $arrays    = AppUserConfig::getInstance()->getConfigArraySystem();
                $directory = env('app.path.user');
                $handle    = FileInfo::scanDirectory($directory);

                if ($handle === false)
                    return false;

                foreach ($handle AS $idDirectory) {
                    if ($idDirectory != '.' && $idDirectory != '..') {
                        $userPath = FileInfo::filterPaths($directory . SP . $idDirectory);

                        if (array_key_exists($idDirectory, $arrays) === false) {

                            if (FileInfo::isTypeDirectory($userPath))
                                FileInfo::rrmdirSystem($userPath);
                            else
                                FileInfo::unlink($userPath);
                        }
                    }
                }
            }

            return true;
        }

    }

?>