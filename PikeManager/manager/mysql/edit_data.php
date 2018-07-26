<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\App\AppPaging;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Mysql\AppMysqlConfig;
    use Librarys\File\FileInfo;
    use Librarys\Http\Request;

    use Librarys\App\Mysql\AppMysqlDataType;
    use Librarys\App\Mysql\AppMysqlQueryCreate;

    define('LOADED',               1);
    define('MYSQL_LIST_DATA',      1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    require_once('global.php');

    use Librarys\App\AppAlert;

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $title  = lng('mysql.edit_data.title_page');
    AppAlert::setID(ALERT_MYSQL_EDIT_DATA);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $pageData  = null;
    $orderData = null;

    if (isset($_GET[PARAMETER_PAGE_DATA_URL]))
        $pageData = intval($_GET[PARAMETER_PAGE_DATA_URL]);

    if ($pageData <= 0)
        $pageData = 1;
    else if ($pageData > 1)
        $appParameter->add(PARAMETER_PAGE_DATA_URL, $pageData, true);

    if (isset($_GET[PARAMETER_ORDER_DATA_URL])) {
        $orderData = addslashes($_GET[PARAMETER_ORDER_DATA_URL]);
        $appParameter->add(PARAMETER_ORDER_DATA_URL, $orderData, true);
    }

    if (isset($_GET[PARAMETER_DATA_KEY_URL]) == false || isset($_GET[PARAMETER_DATA_VALUE_URL]) == false)
        AppAlert::danger(lng('mysql.edit_data.alert.row_data_not_found'), ALERT_MYSQL_LIST_DATA, 'list_data.php' . $appParameter->toString());

    $dataKey   = addslashes(AppDirectory::rawDecode($_GET[PARAMETER_DATA_KEY_URL]));
    $dataValue = addslashes(AppDirectory::rawDecode($_GET[PARAMETER_DATA_VALUE_URL]));

    $mysqlTable = addslashes($appMysqlConnect->getTableCurrent());
    $mysqlQuery = $appMysqlConnect->query('SELECT * FROM `' . $mysqlTable . '` WHERE `' . $dataKey . '`="' . $dataValue . '" LIMIT 1');

    if ($appMysqlConnect->numRows($mysqlQuery) <= 0)
        AppAlert::danger(lng('mysql.edit_data.alert.row_data_not_found'), ALERT_MYSQL_LIST_DATA, 'list_data.php' . $appParameter->toString());

    $backParameter = $appParameter->toString();
    $appParameter->add(PARAMETER_DATA_KEY_URL,   AppDirectory::rawEncode($dataKey),   true);
    $appParameter->add(PARAMETER_DATA_VALUE_URL, AppDirectory::rawEncode($dataValue), true);
    $appParameter->toString(true);

    $mysqlAsscoc       = $appMysqlConnect->fetchAssoc($mysqlQuery);
    $mysqlQueryColumns = $appMysqlConnect->query('SHOW COLUMNS FROM `' . addslashes($appMysqlConnect->getTableCurrent()) . '`');
    $listFields        = array();
    $listDatas         = array();

    while ($mysqlAssocColumns = $appMysqlConnect->fetchAssoc($mysqlQueryColumns)) {
        $listFields[$mysqlAssocColumns['Field']] = $mysqlAssocColumns;
        $listDatas[$mysqlAssocColumns['Field']]  = $mysqlAsscoc[$mysqlAssocColumns['Field']];
    }

    if (isset($_POST['save'])) {
        $mysqlQuery = new AppMysqlQueryCreate(AppMysqlQueryCreate::COMMAND_UPDATE, $appMysqlConnect->getTableCurrent());

        foreach ($listDatas AS $fieldKey => &$fieldValue) {
            $fieldValue = addslashes($_POST[$fieldKey]);
            $mysqlQuery->addData($fieldKey, $fieldValue);
        }

        $mysqlQuery->addWhere($dataKey, $dataValue);
        $mysqlQuery->setLimit(1);
        $mysqlQuery = $mysqlQuery->query();

        if ($mysqlQuery == false)
            AppAlert::danger(lng('mysql.edit_data.alert.edit_data_failed', 'error', $appMysqlConnect->error()));
        else
            AppAlert::success(lng('mysql.edit_data.alert.edit_data_success'), ALERT_MYSQL_LIST_DATA, 'list_data.php' . $backParameter);

        foreach ($listDatas AS $fieldKey => &$fieldValue)
            $fieldValue = stripslashes($fieldValue);
    }
?>

    <?php echo AppAlert::display(); ?>

    <div class="mysql-query-string">
        <span><?php echo $appMysqlConnect->getMysqlQueryExecStringCurrent(); ?></span>
    </div>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.edit_data.title_page'); ?></span>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/mysql/edit_data.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <?php foreach ($listFields AS $fieldKey => $fieldAssoc) { ?>
                    <?php if (preg_match('/^([a-zA-Z0-9\-_]+)(\(+|\s+|\\b)/', $fieldAssoc['Type'], $matches) && AppMysqlDataType::isHasLength($matches[1]) == false) { ?>
                        <li class="textarea">
                            <span><?php echo lng('mysql.edit_data.form.input.column_name', 'name', $fieldKey, 'type', $fieldAssoc['Type']); ?></span>
                            <textarea name="<?php echo $fieldKey; ?>" placeholder="<?php echo lng('mysql.edit_data.form.placeholder.input_column_data', 'name', $fieldKey); ?>" rows="5"><?php echo htmlspecialchars($listDatas[$fieldKey]); ?></textarea>
                        </li>
                    <?php } else { ?>
                        <li class="input">
                            <span><?php echo lng('mysql.edit_data.form.input.column_name', 'name', $fieldKey, 'type', $fieldAssoc['Type']); ?></span>
                            <input type="<?php if (AppMysqlDataType::isNumeric($matches[1])) { ?>number<?php } else { ?>text<?php } ?>" name="<?php echo $fieldKey; ?>" value="<?php echo htmlspecialchars($listDatas[$fieldKey]); ?>" placeholder="<?php echo lng('mysql.edit_data.form.placeholder.input_column_data', 'name', $fieldKey); ?>"/>
                        </li>
                    <?php } ?>
                <?php } ?>

                <li class="button">
                    <button type="submit" name="save" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.edit_data.form.button.save'); ?></span>
                    </button>
                    <a href="list_data.php<?php echo $backParameter; ?>">
                        <span><?php echo lng('mysql.create_data.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
        <li>
            <a href="info_table.php<?php echo $backParameter; ?>">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('mysql.list_table.menu_action.info_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="create_column.php<?php echo $backParameter; ?>">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_table.menu_action.create_column'); ?></span>
            </a>
        </li>
        <li>
            <a href="create_data.php<?php echo $backParameter; ?>">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_table.menu_action.create_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="truncate_data.php<?php echo $backParameter; ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_table.menu_action.truncate_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="rename_table.php<?php echo $backParameter; ?>">
                <span class="icomoon icon-edit"></span>
                <span><?php echo lng('mysql.list_table.menu_action.rename_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="delete_table.php<?php echo $backParameter; ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_table.menu_action.delete_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_data.php<?php echo $backParameter; ?>">
                <span class="icomoon icon-storage"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_column.php<?php echo $backParameter; ?>">
                <span class="icomoon icon-column"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_column'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_table.php<?php echo $backParameter; ?>">
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