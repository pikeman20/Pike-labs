<?php

    use Librarys\App\AppAssets;
    use Librarys\App\AppUser;
    use Librarys\App\AppAlert;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Mysql\AppMysqlConfig;
    use Librarys\Http\Request;

    if (!defined('LOADED'))
        exit(0);

    if (Request::isDesktop(false) == false) {
        requireDefine('asset');

    $autoload = AppConfig::getInstance()->getSystem('enable_disable.autoload');
    $headers  = array();

    if (function_exists('getallheaders'))
        $headers = getallheaders();

    if (count($headers) > 0) {
        $isSaveData = false;

        foreach ($headers AS $key => $value) {
            if (strcasecmp($key, 'Save-Data') === 0 && (strcasecmp($value, 'On') === 0 || $value === 1)) {
                $isSaveData = true;
                break;
            }
        }

        unset($key);
        unset($value);

        if ($isSaveData) {
            require_once(env('app.path.error') . SP . 'save-data.php');
            exit;
        }

        unset($isSaveData);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php if (isset($title)) echo $title; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow, noodp, nodir"/>
        <meta http-equiv="Cache-Control" content="private, max-age=0, no-cache, no-store, must-revalidate"/>
        <meta http-equiv="Pragma" content="no-cache"/>
        <meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT">
        <link rel="stylesheet" type="text/css" href="<?php echo AppAssets::makeURLResourceTheme(AppConfig::getInstance()->get('theme.directory'), env('resource.filename.theme.app')); ?>" media="all,handheld" />

        <link rel="icon" type="image/png" href="<?php echo AppAssets::makeURLResourceIcon(AppConfig::getInstance()->get('theme.directory'), env('resource.filename.icon.favicon_png')); ?>"/>
        <link rel="icon" type="image/x-icon" href="<?php echo AppAssets::makeURLResourceIcon(AppConfig::getInstance()->get('theme.directory'), env('resource.filename.icon.favicon_ico')); ?>"/>
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo AppAssets::makeURLResourceIcon(AppConfig::getInstance()->get('theme.directory'), env('resource.filename.icon.favicon_ico')); ?>"/>
        <script type="text/javascript" src="<?php echo AppAssets::makeURLResourceJavascript(env('resource.filename.javascript.app')); ?>"></script>
    </head>
    <body>
        <script type="text/javascript">
            if (typeof Main !== "undefined" && Main.OnLoad) {
                <?php if (AppConfig::getInstance()->get('enable_disable.autoload')) { ?>
                    Main.OnLoad.addOnload(function() {
                        Main.initHistoryScript("<?php echo AppAssets::makeURLResourceJavascript(env('resource.filename.javascript.history'), env('resource.filename.javascript.directory.lib')); ?>");
                        Main.LoadAjax.init("<?php echo env('app.http.host'); ?>");

                        Main.LoadAjax.reInitLoadTagA();
                        Main.LoadAjax.reInitLoadTagForm();

                        return false;
                    });

                    Main.OnLoad.addInvoke(function() {
                        Main.LoadAjax.reInitLoadTagA();
                        Main.LoadAjax.reInitLoadTagForm();
                    });
                <?php } ?>

                Main.OnLoad.addOnload(function() {
                    <?php if (AppConfig::getInstance()->get('enable_disable.button_save_on_javascript')) { ?>
                        Main.ButtonSaveOnJs.onload();
                    <?php } ?>

                    <?php if (AppConfig::getInstance()->get('enable_disable.auto_focus_input_last')) { ?>
                        ////Main.AutoFocusInputLast.onload();
                    <?php } ?>

                    Main.CustomInputFile.onload();
                    Main.EditorHighlight.init("editor-highlight");
                    Main.CheckboxCheckAll.init("form-list-checkbox-all", "form-list-checked-all-entry", "form-list-checkall-count");
                    Main.ChmodInput.init("form-input-chmod", "form-input-chmod-checkbox");
                    Main.AutoChooseTypeFolderFile.init("form-create-directory-file");
                });
            }
        </script>

        <span id="progress-bar-body"></span>
        <div id="container">
            <div id="header"<?php if (AppConfig::getInstance()->get('enable_disable.header_fixed') == false) { ?> class="disable-fixed"<?php } ?>>
                <div id="logo">
                    <a href="<?php echo env('app.http.host'); ?>">
                        <span id="logo" class="icomoon icon-home"></span>
                    </a>
                </div>
                <ul id="action">
                    <?php if (AppUser::getInstance()->isLogin()) { ?>
<!--                         <li>
                            <a href="#">
                                <span class="icomoon icon-search"></span>
                            </a>
                        </li>
 -->
                        <?php $url    = env('app.http.host') . '/mysql'; ?>
                        <?php $isShow = true; ?>

                        <?php if (AppMysqlConfig::getInstance()->get('mysql_is_connect', false)) { ?>
                           <?php if (AppMysqlConfig::getInstance()->get('mysql_name', null) == null) { ?>
                                <?php $url   .= '/list_database.php'; ?>
                                <?php $isShow = defined('MYSQL_LIST_DATABASE') == false; ?>
                            <?php } else { ?>
                                <?php $url   .= '/list_table.php'; ?>
                                <?php $isShow = defined('MYSQL_LIST_TABLE') == false; ?>
                            <?php } ?>
                        <?php } else if (defined('MYSQL_REQUIRE')) { ?>
                            <?php $isShow = false; ?>
                        <?php } ?>

                        <?php if ($isShow) { ?>
                            <li>
                                <a href="<?php echo $url; ?>">
                                    <span class="icomoon icon-mysql"></span>
                                </a>

                            </li>
                        <?php } ?>
                        <?php unset($url); ?>
                        <?php unset($isShow); ?>

                        <?php if (defined('SETTING') == false) { ?>
                            <li>
                                <a href="<?php echo env('app.http.host'); ?>/system/setting.php">
                                    <span class="icomoon icon-config"></span>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?php echo env('app.http.host'); ?>/auto.php?id=<?php echo AppAlert::getId(); ?>" class="not-autoload">
                                <span class="icomoon icon-spinner-2<?php if ($autoload == false) { ?> autoload-is-disable<?php } ?>"></span>
                            </a>
                        </li>
                        <?php if (defined('SYSTEM_INFO') == false) { ?>
                            <li>
                                <a href="<?php echo env('app.http.host'); ?>/app/system_info.php">
                                    <span class="icomoon icon-dashboard"></span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if (defined('ABOUT') == false) { ?>
                            <li class="about">
                                <a href="<?php echo env('app.http.host'); ?>/app/about.php">
                                    <span class="icomoon icon-about"></span>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?php echo env('app.http.host'); ?>/user/exit.php" class="not-autoload">
                                <span class="icomoon icon-exit"></span>
                            </a>
                        </li>
                    <?php } else if (defined('ABOUT') == false) { ?>
                        <li>
                            <a href="<?php echo env('app.http.host'); ?>/app/about.php">
                                <span class="icomoon icon-about"></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div id="content">
<?php } ?>
