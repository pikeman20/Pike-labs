<?php

    define('LOADED', 1);
    require_once('global.php');

    use Librarys\App\AppAlert;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Mysql\AppMysqlConfig;

    if (AppMysqlConfig::getInstance()->set('mysql_is_connect', false)) {
        if (AppMysqlConfig::getInstance()->write()) {
            sleep(env('app.mysql.sleep_redirect'));
            AppAlert::success(lng('mysql.home.alert.disconnect_success'), ALERT_MYSQL_HOME, 'index.php');
        }
    }

    AppAlert::danger(lng('mysql.home.alert.disconnect_failed'), ALERT_MYSQL_HOME, 'list_database.php');

?>