<?php

    $memoryUsageBegin = @memory_get_usage();

    if (defined('LOADED') == false)
        exit;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('ROOT') == false)
        define('ROOT', '.');

    use Librarys\App\AppJson;
    use Librarys\App\AppUser;
    use Librarys\App\AppAlert;
    use Librarys\App\Config\AppUserConfig;
    use Librarys\Http\Request;

    $directory = realpath(ROOT);

    require_once(
        $directory . SP .
        'librarys' . SP .
        'Bootstrap.php'
    );

    Librarys\Bootstrap::execute(
        $directory . SP . 'assets' . SP . 'config' . SP . 'app.php',
        $directory . SP . 'assets' . SP . 'cache'
    );

    if (AppUserConfig::getInstance()->hasEntryConfigArraySystem() == false) {
        $idAlert = ALERT_USER_LOGIN;
        $urlGoto = env('app.http.host') . '/user/login.php';

        if (AppUser::getInstance()->createFirstUser())
            AppAlert::success(lng('default.global.create_first_user_success', 'username', AppUser::USERNAME_CREATE_FIRST, 'password', AppUser::PASSWORD_CREATE_FIRST), $idAlert, $urlGoto);
        else
            AppAlert::danger(lng('default.global.create_first_user_failed'), $idAlert, $urlGoto);
    }

    if (defined('DISABLE_CHECK_LOGIN') == false) {
        $isLogin = false;

        if (AppUser::getInstance()->isLogin() == false)
            AppAlert::danger(lng('user.login.alert.not_login'), ALERT_USER_LOGIN, env('app.http.host') . '/user/login.php');
        else if (AppUser::getInstance()->isUserBand())
            AppAlert::danger(lng('user.login.alert.user_is_band'), ALERT_USER_LOGIN, env('app.http.host') . '/user/login.php');
        else
            $isLogin = true;

        if (Request::isDesktop(false)) {
            if ($isLogin == false)
                AppJson::getInstance()->setResponseCodeSystem(DESKTOP_CODE_IS_NOT_LOGIN);

            AppJson::getInstance()->setResponseDataSystem([
                'is_login' => $isLogin
            ]);
        }
    }

    if (defined('INDEX') == false && Request::isDesktop(false)) {
        if (Request::isMethodPost() == false)
            Request::redirect(env('app.http.host'));
    }

    unset($httpHostApp);
    unset($httpHostConfig);
