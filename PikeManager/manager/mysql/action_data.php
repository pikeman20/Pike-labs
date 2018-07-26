<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Mysql\AppMysqlConfig;
    use Librarys\Database\DatabaseBackupRestore;
    use Librarys\File\FileInfo;

    define('LOADED',               1);
    define('MYSQL_LIST_DATA',      1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    require_once('global.php');

    AppAlert::setID(ALERT_MYSQL_ACTION_DATA);
    requireDefine('mysql_action_data');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $pageData  = null;
    $orderData = null;
    $byData    = null;

    if (isset($_GET[PARAMETER_PAGE_DATA_URL]))
        $pageData = intval($_GET[PARAMETER_PAGE_DATA_URL]);

    if (isset($_POST['by']))
        $byData = addslashes(AppDirectory::rawDecode($_POST['by']));

    if ($pageData <= 0)
        $pageData = 1;
    else if ($pageData > 1)
        $appParameter->add(PARAMETER_PAGE_DATA_URL, $pageData, true);

    if (isset($_GET[PARAMETER_ORDER_DATA_URL])) {
        $orderData = addslashes($_GET[PARAMETER_ORDER_DATA_URL]);
        $appParameter->add(PARAMETER_ORDER_DATA_URL, $orderData, true);
    }


    if (empty($byData))
        AppAlert::danger(lng('mysql.action_data.alert.not_data_select'), ALERT_MYSQL_LIST_DATA, 'list_data.php' . $appParameter->toString());

    $listDatas  = array();
    $countDatas = 0;

    if (isset($_POST['datas']) && is_array($_POST['datas'])) {
        $listDatas  = $_POST['datas'];
        $countDatas = count($listDatas);
    }

    if ($countDatas <= 0)
        AppAlert::danger(lng('mysql.action_data.alert.not_data_select'), ALERT_MYSQL_LIST_DATA, 'list_data.php' . $appParameter->toString());

    $listEntrys = AppDirectory::rawDecodes($listDatas);

    if (isset($_POST['action']) && empty($_POST['action']) == false)
        $nameAction = addslashes(trim($_POST['action']));

    if ($nameAction == MYSQL_ACTION_DATA_DELETE_MULTI)
        $title = 'delete';
    else
        AppAlert::danger(lng('mysql.action_data.alert.action_not_validate'), ALERT_MYSQL_LIST_DATA, 'list_table.php' . $appParameter->toString());

    $title = lng('mysql.action_data.title.' . $title);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    if (isset($_POST['delete_button'])) {
        $isFailed     = false;
        $countSuccess = $countDatas;

        foreach ($listDatas AS $entry) {
            if ($appMysqlConnect->query('DELETE FROM `' . addslashes($appMysqlConnect->getTableCurrent()) . '` WHERE `' . $byData . '`="' . addslashes($entry) . '" LIMIT 1') == false) {
                $isFailed = true;
                $countSuccess--;
                AppAlert::danger(lng('mysql.action_data.alert.delete.delete_data_failed', 'name', $entry, 'error', $appMysqlConnect->error()));
            }
        }

        if ($isFailed == false)
            AppAlert::success(lng('mysql.action_data.alert.delete.delete_success'), ALERT_MYSQL_LIST_DATA, 'list_data.php' . $appParameter->toString());
        else if ($countDatas > 1 && $countSuccess > 0)
            AppAlert::success(lng('mysql.action_data.alert.delete.delete_failed'));

    }

    $isCountCheckBoxList = AppConfig::getInstance()->get('enable_disable.count_checkbox_mysql_javascript');
?>

    <?php AppAlert::display(); ?>

    <form action="<?php echo env('app.http.host'); ?>/mysql/action_data.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-checkbox-all">
        <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>
        <input type="hidden" name="action" value="<?php echo $nameAction; ?>"/>
        <input type="hidden" name="by" value="<?php echo AppDirectory::rawEncode($byData); ?>"/>

        <div class="form-action">
            <div class="title">
                <span><?php echo $title; ?></span>
            </div>

            <ul class="list no-box-shadow<?php if (AppConfig::getInstance()->get('enable_disable.list_database_double') == false) { ?> not-double<?php } ?>">
                <?php foreach ($listEntrys AS $key => $entry) { ?>
                    <?php $entry = AppDirectory::rawDecode($entry); ?>

                    <li class="is-end-list-option column">
                        <div class="icon">
                            <?php $id = 'entry-' . $key; ?>

                            <input
                                type="checkbox"
                                name="datas[]"
                                id="<?php echo $id; ?>"
                                value="<?php echo AppDirectory::rawDecode($entry); ?>"
                                checked="checked"
                                <?php if ($isCountCheckBoxList) { ?> onclick="javascript:Main.CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>
                            <label for="<?php echo $id; ?>" class="not-content"></label>
                            <span class="icomoon icon-storage"></span>
                        </div>
                        <a href="list_data.php<?php echo $appParameter->toString(); ?>" class="name">
                            <span><?php echo $entry; ?></span>
                        </a>
                    </li>
                <?php } ?>

                <li class="end-list-option">
                    <div class="checkbox-all">
                        <input type="checkbox" name="checked_all_entry" id="form-list-checked-all-entry" onclick="javascript:Main.CheckboxCheckAll.onCheckAll();" checked="checked"/>
                        <label for="form-list-checked-all-entry">
                            <span><?php echo lng('mysql.action_data.form.input.checkbox_all_entry'); ?></span>
                            <?php if ($isCountCheckBoxList) { ?>
                                <span id="form-list-checkall-count"></span>
                            <?php } ?>
                        </label>
                    </div>
                </li>
            </ul>

            <ul class="form-element">
                <?php if ($nameAction == MYSQL_ACTION_DATA_DELETE_MULTI) { ?>
                    <li class="accept">
                        <span><?php echo lng('mysql.action_data.form.input.delete.accept_message'); ?></span>
                    </li>

                    <li class="button">
                        <button type="submit" name="delete_button" id="button-save-on-javascript">
                            <span><?php echo lng('mysql.action_data.form.button.delete'); ?></span>
                        </button>
                        <a href="list_table.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('mysql.action_data.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } ?>

            </ul>
        </div>
    </form>

    <ul class="menu-action">
        <li>
            <a href="info_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('mysql.list_table.menu_action.info_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="create_column.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_table.menu_action.create_column'); ?></span>
            </a>
        </li>
        <li>
            <a href="create_data.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_table.menu_action.create_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="truncate_data.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_table.menu_action.truncate_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="rename_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-edit"></span>
                <span><?php echo lng('mysql.list_table.menu_action.rename_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="delete_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_table.menu_action.delete_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_data.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-storage"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_column.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-column"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_column'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-table"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_table'); ?></span>
            </a>
        </li>

        <?php if ($appMysqlConnect->isDatabaseNameCustom()) { ?>
            <li>
                <a href="list_database.php">
                    <span class="icomoon icon-mysql"></span>
                    <span><?php echo lng('mysql.list_table.menu_action.list_database'); ?></span>
                </a>
            </li>
        <?php } ?>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>