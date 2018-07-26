<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\App\AppPaging;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Mysql\AppMysqlConfig;
    use Librarys\File\FileInfo;
    use Librarys\Http\Request;

    define('LOADED',               1);
    define('MYSQL_LIST_DATA',      1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    require_once('global.php');

    use Librarys\App\AppAlert;

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $mysqlBy      = addslashes($appMysqlConnect->getColumnKey($appMysqlConnect->getTableCurrent()));
    $mysqlStr     = null;
    $mysqlEmptyBy = empty($mysqlBy);
    $mysqlTable   = addslashes($appMysqlConnect->getTableCurrent());
    $mysqlNums  = 0;

    if ($mysqlEmptyBy == false)
        $mysqlNums = $appMysqlConnect->numRows('SELECT * FROM `' . $mysqlTable . '`');

    if ($mysqlNums <= 0 && isset($_SERVER['HTTP_REFERER']) && stripos( $_SERVER['HTTP_REFERER'], 'list_table.php') !== false)
        Request::redirect('list_column.php' . $appParameter->toString());
    else if ($mysqlNums <= 0)
        AppAlert::warning(lng('mysql.list_data.alert.data_is_empty_not_view'), ALERT_MYSQL_LIST_COLUMN, 'list_column.php' . $appParameter->toString());

    $title  = lng('mysql.list_data.title_page');
    AppAlert::setID(ALERT_MYSQL_LIST_DATA);
    require_once(ROOT . 'incfiles' . SP . 'header.php');
    requireDefine('mysql_action_data');

    $orders = [
        'key'   => ORDER_DATA_DESC,
        'query' => strtoupper(ORDER_DATA_DESC)
    ];

    $pagings = [
        'current'     => 0,
        'total'       => 0,
        'begin_query' => 0,
        'end_query'   => $mysqlNums,
        'row_on_page' => $mysqlNums,
        'max'         => AppConfig::getInstance()->get('paging.mysql_list_data')
    ];

    if (isset($_GET[PARAMETER_ORDER_DATA_URL]) && empty($_GET[PARAMETER_ORDER_DATA_URL]) == false) {
        $orders['key'] = trim(addslashes($_GET[PARAMETER_ORDER_DATA_URL]));

        if ($orders['key'] != ORDER_DATA_DESC && $orders['key'] != ORDER_DATA_ASC)
            $orders['key'] = ORDER_DATA_DESC;

        $orders['query'] = strtoupper($orders['key']);
    }

    if (isset($_GET[PARAMETER_PAGE_DATA_URL]) && empty($_GET[PARAMETER_PAGE_DATA_URL]) == false)
        $pagings['current'] = intval(addslashes($_GET[PARAMETER_PAGE_DATA_URL]));

    if ($pagings['current'] <= 0)
        $pagings['current'] = 1;

    if ($pagings['max'] > 0) {
        $pagings['begin_query'] = ($pagings['current']     * $pagings['max']) - $pagings['max'];
        $pagings['end_query']   = ($pagings['begin_query'] + $pagings['max']);
        $pagings['row_on_page'] = ($pagings['end_query'] - $pagings['begin_query']);
        $pagings['end_query']   = $pagings['max'];
        $pagings['total']       = ceil($mysqlNums / $pagings['max']);
    }

    $pagings['is_paging'] = $pagings['max'] > 0 && $pagings['total'] > 1;

    $urlBeginPaging = 'list_data.php?' . PARAMETER_DATABASE_URL   . '=' . AppDirectory::rawEncode($appMysqlConnect->getName()) .
                                   '&' . PARAMETER_TABLE_URL      . '=' . AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()) .
                                   '&' . PARAMETER_ORDER_DATA_URL . '=' . AppDirectory::rawEncode($orders['key']);

    $pagePaging = new AppPaging(
        $urlBeginPaging,
        $urlBeginPaging . '&' . PARAMETER_PAGE_DATA_URL . '='
    );

    if ($pagings['max'] > 0 && $mysqlEmptyBy == false) {
        $mysqlStr = 'SELECT * ' .
                    'FROM `' .     $mysqlTable . '` ' .
                    'ORDER BY `' . $mysqlBy . '` ' . $orders['query'] . ' ' .
                    'LIMIT ' . $pagings['begin_query'] . ', ' . $pagings['end_query'];
    } else if ($mysqlEmptyBy == false) {
        $mysqlStr = 'SELECT * ' .
                    'FROM `' . $mysqlTable . '` ' .
                    'ORDER BY `' . $mysqlBy . '` ' . $orders['query'];
    }

    $mysqlQuery          = $appMysqlConnect->query($mysqlStr);
    $mysqlQueryColumn    = $appMysqlConnect->query('SHOW COLUMNS FROM `' . addslashes($appMysqlConnect->getTableCurrent()) . '`');
    $isCountCheckBoxList = AppConfig::getInstance()->get('enable_disable.count_checkbox_file_javascript');
?>

    <?php echo AppAlert::display(); ?>

    <div class="mysql-query-string">
        <span><?php echo $appMysqlConnect->getMysqlQueryExecStringCurrent(); ?></span>
    </div>

    <?php $appParameter->add(PARAMETER_ORDER_DATA_URL, $orders['key'],      true); ?>
    <?php $appParameter->add(PARAMETER_PAGE_DATA_URL,  $pagings['current'], true); ?>

    <form action="<?php echo env('app.http.host'); ?>/mysql/action_data.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-checkbox-all">
        <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>
        <input type="hidden" name="by" value="<?php echo AppDirectory::rawEncode($mysqlBy); ?>"/>

        <ul class="list<?php if (AppConfig::getInstance()->get('enable_disable.list_database_double') == false) { ?> not-double<?php } ?>">
            <li class="database back">
                <a href="info_table.php<?php echo $appParameter->toString(); ?>">
                    <span class="icomoon icon-table"></span>
                </a>
                <a href="list_table.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>&<?php echo PARAMETER_TABLE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()); ?>">
                    <span><?php echo $appMysqlConnect->getTableCurrent(); ?></span>
                </a>
            </li>
            <?php if ($mysqlNums <= 0) { ?>
                <li class="has-first-not-entry empty">
                    <span class="icomoon icon-column"></span>
                    <span><?php echo lng('mysql.list_data.alert.empty_list_data'); ?></span>
                </li>
            <?php } else { ?>
                <li class="has-first-not-entry order">
                    <?php if ($orders['key'] == ORDER_DATA_ASC) { ?>
                        <span class="current"><?php echo strtoupper(ORDER_DATA_ASC); ?></span>
                        <span class="text">|</span>
                        <a href="list_data.php<?php echo $appParameter->toString(); ?>&<?php echo PARAMETER_ORDER_DATA_URL; ?>=<?php echo ORDER_DATA_DESC; ?>">
                            <span class="choose"><?php echo strtoupper(ORDER_DATA_DESC); ?></span>
                        </a>
                    <?php } else { ?>
                        <a href="list_data.php<?php echo $appParameter->toString(); ?>&<?php echo PARAMETER_ORDER_DATA_URL; ?>=<?php echo ORDER_DATA_ASC; ?>">
                            <span class="choose"><?php echo strtoupper(ORDER_DATA_ASC); ?></span>
                        </a>
                        <span class="text">|</span>
                        <span class="current"><?php echo strtoupper(ORDER_DATA_DESC); ?></span>
                    <?php } ?>
                </li>

                <li class="data">
                    <div>
                        <table>
                            <tr>
                                <td>
                                    <input type="checkbox" name="checked_all_entry" id="form-list-checked-all-entry" onclick="javascript:Main.CheckboxCheckAll.onCheckAll();"/>
                                    <label for="form-list-checked-all-entry" class="not-content"></label>
                                </td>

                                <?php while ($mysqlAssocColumn = $appMysqlConnect->fetchAssoc($mysqlQueryColumn)) { ?>
                                    <td>
                                        <span><?php echo $mysqlAssocColumn['Field']; ?></span>
                                    </td>
                                <?php } ?>
                            </tr>

                            <?php $index = 0; ?>
                            <?php while ($mysqlAssoc = $appMysqlConnect->fetchAssoc($mysqlQuery)) { ?>
                                <?php $appParameter->add(PARAMETER_DATA_KEY_URL,   AppDirectory::rawEncode($mysqlBy), true); ?>
                                <?php $appParameter->add(PARAMETER_DATA_VALUE_URL, AppDirectory::rawEncode($mysqlAssoc[$mysqlBy]), true); ?>

                                <tr onclick="javascript:window.location.href='edit_data.php<?php echo $appParameter->toString(true); ?>'">
                                    <?php $id = 'data-' . ($index++) . '-' . $mysqlAssoc[$mysqlBy]; ?>
                                    <td>
                                        <input
                                            type="checkbox"
                                            name="datas[]"
                                            id="<?php echo $id; ?>"
                                            value="<?php echo $mysqlAssoc[$mysqlBy]; ?>"
                                            onclick="event.stopPropagation();"
                                            <?php if ($isCountCheckBoxList) { ?> onclick="javascript:Main.CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                                        <label for="<?php echo $id; ?>" class="not-content" onclick="event.stopPropagation();"></label>
                                    </td>

                                    <?php foreach ($mysqlAssoc AS $data) { ?>
                                        <td>
                                            <span><?php echo substr($data, 0, 100); ?></span>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>

                            <?php $appParameter->remove(PARAMETER_DATA_KEY_URL); ?>
                            <?php $appParameter->remove(PARAMETER_DATA_VALUE_URL); ?>
                            <?php $appParameter->toString(true); ?>
                        </table>
                    </div>
                </li>

                <?php if ($pagings['is_paging']) { ?>
                    <li class="end-list-option">
                        <div class="paging">
                            <?php echo $pagePaging->display($pagings['current'], $pagings['total']); ?>
                        </div>
                    </li>
                <?php } ?>
            <?php } ?>
        </ul>

        <ul class="action-multi">
            <li>
                <button type="submit" name="action" value="<?php echo MYSQL_ACTION_DATA_DELETE_MULTI; ?>">
                    <span class="icomoon icon-trash"></span>
                    <span class="label"><?php echo lng('mysql.list_data.action_multi.delete'); ?></span>
                </button>
            </li>
        </ul>
    </form>
    <?php $appParameter->remove(PARAMETER_ORDER_DATA_URL); ?>
    <?php $appParameter->remove(PARAMETER_PAGE_DATA_URL); ?>

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