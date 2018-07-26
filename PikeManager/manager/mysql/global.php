<?php

    if (defined('LOADED') == false)
        exit;

    define('MYSQL_REQUIRE',                   1);
    define('PARAMETER_DATABASE_URL',          'database');
    define('PARAMETER_TABLE_URL',             'table');
    define('PARAMETER_COLUMN_URL',            'column');
    define('PARAMETER_DATA_KEY_URL',          'data_key');
    define('PARAMETER_DATA_VALUE_URL',        'data_value');
    define('PARAMETER_IS_REFERER_LIST_TABLE', 'is_referer_list_table');
    define('PARAMETER_PAGE_DATA_URL',         'page_data');
    define('PARAMETER_ORDER_DATA_URL',        'order_data');
    define('ROOT',                            '..' . DIRECTORY_SEPARATOR);

    define('ORDER_DATA_DESC', 'desc');
    define('ORDER_DATA_ASC',  'asc');

    require_once(
        realpath(ROOT) .
        DIRECTORY_SEPARATOR . 'incfiles' .
        DIRECTORY_SEPARATOR . 'global.php'
    );

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\Mysql\AppMysqlConfig;
    use Librarys\App\Mysql\AppMysqlConnect;

    $appMysqlConnect = new AppMysqlConnect();
    $appMysqlConnect->setDatabaseExtensionDefault('Librarys\Database\Extension\DatabaseExtensionMysql');
    $appMysqlConnect->setDatabaseExtensionRuntime('Librarys\Database\Extension\DatabaseExtensionMysqli');
    $appMysqlConnect->executeInitializing();

    if (AppMysqlConfig::getInstance()->get('mysql_is_connect', false) == true) {
        $appMysqlConnect->setHost    (AppMysqlConfig::getInstance()->get('mysql_host'));
        $appMysqlConnect->setUsername(AppMysqlConfig::getInstance()->get('mysql_username'));
        $appMysqlConnect->setPassword(AppMysqlConfig::getInstance()->get('mysql_password'));

        if (isset($_GET[PARAMETER_DATABASE_URL]) && empty($_GET[PARAMETER_DATABASE_URL]) == false) {
            if (AppMysqlConfig::getInstance()->get('mysql_name') == null || strcmp(AppMysqlConfig::getInstance()->get('mysql_name'), $_GET[PARAMETER_DATABASE_URL]) === 0) {
                $appMysqlConnect->setName(trim(addslashes(AppDirectory::rawDecode($_GET[PARAMETER_DATABASE_URL]))));
                $appMysqlConnect->setDatabaseNameCustom(true);
            } else {
               AppAlert::danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', AppMysqlConfig::getInstance()->get('mysql_name')), ALERT_MYSQL_LIST_TABLE, 'list_table.php');
            }
        } else if (defined('DATABASE_CHECK_MYSQL') && empty($_GET[PARAMETER_DATABASE_URL]) && AppMysqlConfig::getInstance()->get('mysql_name') == null) {
            AppAlert::danger(lng('mysql.home.alert.mysql_connect_database_name_failed', 'name', AppMysqlConfig::getInstance()->get('mysql_name')), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');
        }

        if (AppMysqlConfig::getInstance()->get('mysql_name') != null) {
            $appMysqlConnect->setName(AppMysqlConfig::getInstance()->get('mysql_name'));
            $appMysqlConnect->setDatabaseNameCustom(false);
        }

        $appMysqlConnect->setPort    (AppMysqlConfig::getInstance()->get('mysql_port'));
        $appMysqlConnect->setEncoding(AppMysqlConfig::getInstance()->get('mysql_encoding'));

        $idAlertMysql     = ALERT_MYSQL_LIST_DATABASE;
        $urlRedirectMysql = 'list_database.php';

        if (AppMysqlConfig::getInstance()->get('mysql_name') != null || $appMysqlConnect->isDatabaseNameCustom()) {
            $idAlertMysql     = ALERT_MYSQL_LIST_TABLE;
            $urlRedirectMysql = 'list_table.php';
        }

        if ($appMysqlConnect->openConnect(false) == false) {
            if ($appMysqlConnect->isDatabaseNameCustom())
                AppAlert::danger(lng('mysql.home.alert.mysql_connect_database_name_failed', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, env('app.http.host') . '/mysql/list_database.php');
            else if (defined('MYSQL_HOME') && strtolower($_SERVER['REQUEST_METHOD']) == 'get')
                AppAlert::danger(lng('mysql.home.alert.mysql_connect_failed', 'error', $appMysqlConnect->errorConnect()), ALERT_MYSQL_HOME);
            else if (defined('MYSQL_HOME') == false)
                AppAlert::danger(lng('mysql.home.alert.mysql_connect_failed', 'error', $appMysqlConnect->errorConnect()), ALERT_MYSQL_HOME, env('app.http.host') . '/mysql');
        } else if (defined('MYSQL_HOME')) {
            AppAlert::info(lng('mysql.home.alert.mysql_is_already_connect'), $idAlertMysql, $urlRedirectMysql);
        }

        if (isset($_GET[PARAMETER_TABLE_URL]) && empty($_GET[PARAMETER_TABLE_URL]) == false) {
            $appMysqlConnect->setTableCurrent(AppDirectory::rawDecode($_GET[PARAMETER_TABLE_URL]));

            if ($appMysqlConnect->checkTableCurrent() == false)
                AppAlert::danger(lng('mysql.home.alert.mysql_table_name_not_exists', 'table', $appMysqlConnect->getTableCurrent(), 'database', $appMysqlConnect->getName()), $idAlertMysql, $urlRedirectMysql);
        } else if (defined('TABLE_CHECK_MYSQL') && empty($_GET[PARAMETER_TABLE_URL])) {
            AppAlert::danger(lng('mysql.home.alert.mysql_table_not_exists', 'database', $appMysqlConnect->getName()), $idAlertMysql, $urlRedirectMysql);
        }

        if (isset($_GET[PARAMETER_COLUMN_URL]) && empty($_GET[PARAMETER_COLUMN_URL]) == false) {
            $appMysqlConnect->setColumnCurrent(AppDirectory::rawDecode($_GET[PARAMETER_COLUMN_URL]));
            $appMysqlConnect->checkColumnCurrent();
        }

        unset($idAlertMysql);
        unset($urlRedirectMysql);
    }
