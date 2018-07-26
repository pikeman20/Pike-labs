<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppPaging;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Mysql\AppMysqlConfig;
    use Librarys\App\Mysql\AppMysqlConfigWrite;

    define('LOADED',     1);
    define('MYSQL_HOME', 1);

    require_once('global.php');

    $title = lng('mysql.home.title_page');
    AppAlert::setID(ALERT_MYSQL_HOME);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $forms = [
        'mysql_host'     => AppMysqlConfig::getInstance()->get('mysql_host'),
        'mysql_username' => AppMysqlConfig::getInstance()->get('mysql_username'),
        'mysql_password' => AppMysqlConfig::getInstance()->get('mysql_password'),
        'mysql_name'     => AppMysqlConfig::getInstance()->get('mysql_name'),
        'mysql_port'     => AppMysqlConfig::getInstance()->get('mysql_port'),
        'mysql_encoding' => AppMysqlConfig::getInstance()->get('mysql_encoding')
    ];

    if (isset($_POST['connect'])) {
        $forms['mysql_host']     = addslashes($_POST['mysql_host']);
        $forms['mysql_username'] = addslashes($_POST['mysql_username']);
        $forms['mysql_password'] = addslashes($_POST['mysql_password']);
        $forms['mysql_name']     = addslashes($_POST['mysql_name']);

        if (empty($forms['mysql_host'])) {
            AppAlert::danger(lng('mysql.home.alert.not_input_mysql_host'));
        } else if (empty($forms['mysql_username'])) {
            AppAlert::danger(lng('mysql.home.alert.not_input_mysql_username'));
        } else {
            $appMysqlConnect->setHost    ($forms['mysql_host']);
            $appMysqlConnect->setUsername($forms['mysql_username']);
            $appMysqlConnect->setPassword($forms['mysql_password']);
            $appMysqlConnect->setName    ($forms['mysql_name']);
            $appMysqlConnect->setPort    ($forms['mysql_port']);
            $appMysqlConnect->setEncoding($forms['mysql_encoding']);

            $isFailed = false;

            foreach ($forms AS $envKey => $envValue) {
                if (AppMysqlConfig::getInstance()->set($envKey, $envValue) == false) {
                    $isFailed = true;
                    AppAlert::danger(lng('mysql.home.alert.mysql_write_config_failed'));

                    break;
                }
            }

            if ($isFailed == false) {
                if (AppMysqlConfig::getInstance()->write()) {
                    if ($appMysqlConnect->openConnect(false)) {
                        AppMysqlConfig::getInstance()->set('mysql_is_connect', true);

                        if (AppMysqlConfig::getInstance()->write() == false) {
                            AppAlert::danger(lng('mysql.home.alert.mysql_write_config_failed'));
                        } else {
                            sleep(env('app.mysql.sleep_redirect'));

                            if (empty($forms['mysql_name']))
                                AppAlert::success(lng('mysql.home.alert.mysql_connect_success'), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');
                            else
                                AppAlert::success(lng('mysql.home.alert.mysql_connect_success'), ALERT_MYSQL_LIST_TABLE, 'list_table.php');
                        }
                    } else {
                        AppAlert::danger(lng('mysql.home.alert.mysql_connect_failed', 'error', $appMysqlConnect->errorConnect()));
                    }
                } else {
                    AppAlert::danger(lng('mysql.home.alert.mysql_write_config_failed'));
                }
            }
        }

        foreach ($forms AS $key => $value)
            $forms[$key] = stripslashes($value);
    }
?>

    <?php AppAlert::display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.home.title_page'); ?></span>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/mysql/index.php" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('mysql.home.form.input.host'); ?></span>
                    <input type="text" name="mysql_host" value="<?php echo $forms['mysql_host']; ?>" placeholder="<?php echo lng('mysql.home.form.placeholder.input_host'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('mysql.home.form.input.username'); ?></span>
                    <input type="text" name="mysql_username" value="<?php echo $forms['mysql_username']; ?>" placeholder="<?php echo lng('mysql.home.form.placeholder.input_username'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('mysql.home.form.input.password'); ?></span>
                    <input type="password" name="mysql_password" value="<?php echo $forms['mysql_password']; ?>" placeholder="<?php echo lng('mysql.home.form.placeholder.input_password'); ?>" auto_complete="off"/>
                </li>
                <li class="input">
                    <span><?php echo lng('mysql.home.form.input.name'); ?></span>
                    <input type="text" name="mysql_name" value="<?php echo $forms['mysql_name']; ?>" placeholder="<?php echo lng('mysql.home.form.placeholder.input_name'); ?>"/>
                </li>
                <li class="button">
                    <button type="submit" name="connect">
                        <span><?php echo lng('mysql.home.form.button.connect'); ?></span>
                    </button>
                    <a href="<?php echo env('app.http.host'); ?>">
                        <span><?php echo lng('mysql.home.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
        <li class="info"><span><?php echo lng('mysql.home.alert.tips'); ?></span></li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>