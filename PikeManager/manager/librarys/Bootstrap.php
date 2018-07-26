<?php

    namespace Librarys;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('LOADED') == false)
        exit;

    require_once(__DIR__ . SP . 'Autoload.php');
    require_once(__DIR__ . SP . 'Boot.php');

    use Librarys\Boot;
    use Librarys\Autoload;
    use Librarys\App\AppClean;
    use Librarys\App\AppChecker;
    use Librarys\App\AppUser;
    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Config\AppUserConfig;
    use Librarys\App\Config\AppAboutConfig;
    use Librarys\App\Mysql\AppMysqlConfig;
    use Librarys\Http\Request;
    use Librarys\Http\Validate;
    use Librarys\Http\Secure\CFSRToken;
    use Librarys\Exception\RuntimeException;

    class Bootstrap
    {

        public static function execute($configPath, $cacheDirectory)
        {
            Autoload::getInstance()->execute();
            Boot::getInstance($configPath, $cacheDirectory, defined('ENABLE_CUSTOM_HEADER'));
            AppAboutConfig::updateBuildDev();

            if (Validate::ip(($ip = Request::ip())) == false)
                die(lng('default.global.ip_not_validate', 'ip', $ip));

            unset($directory);

            if (AppChecker::getInstance()->execute()->isAccept() == false) {
                if (AppChecker::getInstance()->isInstallDirectory() == false)
                    die(lng('default.global.app_is_install_root'));
                else if (AppChecker::getInstance()->isDirectoryPermissionExecute() == false)
                    die(lng('default.global.host_is_not_premission'));
                else if (AppChecker::getInstance()->isConfigValidate() == false)
                    die(lng('default.global.config_app_not_found'));
                else
                    die(lng('default.global.unknown_error_app'));
            }

            if (Request::isDesktop(false))
                requireDefine('desktop');

            // Get config system
            AppConfig::getInstance()->execute();
            AppUser::getInstance()->execute();
            AppAlert::getInstance()->execute();

            // Get config user
            AppConfig::getInstance()->execute(AppUser::getInstance());
            AppConfig::getInstance()->requireEnvProtected(env('resource.config.manager_dis'));

            if (CFSRToken::getInstance()->validatePost() !== true)
                die(lng('default.global.cfsr_not_validate'));

            AppDirectory::getInstance()->execute();
            AppMysqlConfig::getInstance()->execute(AppUser::getInstance());

            $httpHostApp    = env('app.http.host');
            $httpHostConfig = AppConfig::getInstance()->get('http_host');

            if (strcmp($httpHostApp, $httpHostConfig) !== 0) {
                if (AppConfig::getInstance()->setSystem('http_host', $httpHostApp))
                    AppConfig::getInstance()->write(true);

                AppClean::scanAutoClean(false, false, true, true);
            } else {
                AppClean::scanAutoClean();
            }
        }

    }
