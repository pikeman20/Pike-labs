<?php

    define('LOADED',              1);
    define('DISABLE_CHECK_LOGIN', 1);
    define('ROOT',                '..' . DIRECTORY_SEPARATOR);

    require_once(ROOT . 'incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    use Librarys\App\AppAlert;
    use Librarys\App\AppUser;

    if (AppUser::getInstance()->isLogin()) {
    	if (AppUser::getInstance()->exitSession())
   		   AppAlert::success(lng('user.login.alert.exit_session_success'), ALERT_USER_LOGIN, 'login.php');
        else
       	    AppAlert::danger(lng('user.login.alert.exit_session_failed'), ALERT_INDEX, env('app.http.host'));
    } else {
       AppAlert::danger(lng('user.login.alert.not_login'), ALERT_USER_LOGIN, 'login.php');
    }

?>