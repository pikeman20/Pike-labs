<?php

    use Librarys\App\AppUser;
    use Librarys\App\AppAlert;
    use Librarys\App\Config\AppConfig;
    use Librarys\File\FileInfo;

    define('LOADED', 1);
    require_once('global.php');

    $title = lng('system.setting_theme.title_page');
    AppAlert::setID(ALERT_SYSTEM_SETTING_THEME);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $themeIniName    = 'theme.ini';
    $themeName       = env('resource.filename.theme.app');
    $httpHost        = env('app.http.host');
    $rootDirectory   = env('app.path.root');
    $themeDirectory  = env('app.path.theme');
    $httpThemeUrl    = $httpHost . substr($themeDirectory, strlen($rootDirectory));
    $handleDirectory = FileInfo::scanDirectory($themeDirectory);
    $currentTheme    = AppConfig::getInstance()->get('theme.directory');
    $listThemes      = [];

    if (is_array($handleDirectory) && count($handleDirectory) > 0) {
        foreach ($handleDirectory AS $filename) {
            if ($filename != '.' && $filename != '..') {
                $fileini = FileInfo::filterPaths($themeDirectory . SP . $filename . SP . $themeIniName);

                if (FileInfo::isTypeFile($fileini)) {
                    $listThemes[] = [
                        'directory' => $filename,
                        'ini'       => parse_ini_file($fileini),
                        'base_url'  => separator($httpThemeUrl . '/' . $filename, '/'),
                        'theme_url' => separator($httpThemeUrl . '/' . $filename . '/' . $themeName, '/')
                    ];
                }
            }
        }
    }

    $writeSystem = true;

    if (isset($_POST['choose'])) {
        $theme = addslashes($_POST['theme']);

        if (isset($_POST['write_system']))
            $writeSystem = boolval(addslashes($_POST['write_system']));
        else
            $writeSystem = false;

        if (empty($theme)) {
            AppAlert::danger(lng('system.setting_theme.alert.not_choose_theme'));
        } else if (AppConfig::getInstance()->set('theme.directory', $theme) == false || ($writeSystem && AppConfig::getInstance()->setSystem('theme.directory', $theme) == false)) {
            AppAlert::danger(lng('system.setting_theme.alert.change_theme_failed'));
        } else if (AppConfig::getInstance()->write() == false || ($writeSystem && AppConfig::getInstance()->write(true) == false)) {
            AppAlert::danger(lng('system.setting_theme.alert.change_theme_failed'));
        } else {
            AppAlert::success(lng('system.setting_theme.alert.change_theme_success'));
            $currentTheme = $themeName;
        }
    }
?>

    <?php AppAlert::display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('system.setting_theme.title_page'); ?></span>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/system/setting_theme.php" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="theme-choose">
                <?php foreach ($listThemes AS $theme) { ?>
                    <li>
                        <?php $stylesheetBody        = null; ?>
                        <?php $stylesheetHeader      = null; ?>
                        <?php $stylesheetContent     = null; ?>

                        <?php $backgroundColor       = isset($theme['ini']['css_background_color'])           ? $theme['ini']['css_background_color']        : null; ?>
                        <?php $backgroundImage       = isset($theme['ini']['css_background_image'])           ? $theme['ini']['css_background_image']        : null; ?>
                        <?php $backgroundPosition    = isset($theme['ini']['css_background_position'])        ? $theme['ini']['css_background_position']     : null; ?>
                        <?php $backgroundRepeat      = isset($theme['ini']['css_background_repeat'])          ? $theme['ini']['css_background_repeat']       : null; ?>
                        <?php $headerBackgroundColor = isset($theme['ini']['css_header_background_color'])    ? $theme['ini']['css_header_background_color'] : null; ?>
                        <?php $headerTitleColor      = isset($theme['ini']['css_header_title_color'])         ? $theme['ini']['css_header_title_color']      : null; ?>
                        <?php $contentColor          = isset($theme['ini']['css_content_color'])              ? $theme['ini']['css_content_color']           : null; ?>

                        <?php if ($backgroundColor    != null) $stylesheetBody .= 'background-color: ' . $backgroundColor . ';'; ?>
                        <?php if ($backgroundPosition != null) $stylesheetBody .= 'background-position: ' . $backgroundPosition . ';'; ?>
                        <?php if ($backgroundRepeat   != null) $stylesheetBody .= 'background-repeat: ' . $backgroundRepeat . ';'; ?>
                        <?php if ($backgroundImage    != null) $stylesheetBody .= 'background-image: url(' . $theme['base_url'] . '/' . $backgroundImage . ');'; ?>

                        <?php if ($headerBackgroundColor != null) $stylesheetHeader .= 'background-color: ' . $headerBackgroundColor . ';'; ?>
                        <?php if ($headerTitleColor      != null) $stylesheetHeader .= 'color: ' . $headerTitleColor . ';'; ?>

                        <?php if ($contentColor != null) $stylesheetContent .= 'color: ' . $contentColor . ';'; ?>

                        <input type="radio" name="theme" value="<?php echo $theme['directory']; ?>" id="choose-<?php echo $theme['directory']; ?>"<?php if (strcmp($currentTheme, $theme['directory']) === 0) { ?> checked="checked"<?php } ?>/>
                        <label for="choose-<?php echo $theme['directory']; ?>" onclick="javascript:Main.ChooseTheme.preview('<?php echo $theme['theme_url']; ?>')">
                            <div style="<?php echo $stylesheetBody; ?>">
                                <div style="<?php echo $stylesheetHeader; ?>">
                                    <span class="icomoon icon-home"></span>
                                </div>
                                <div>
                                    <span style="<?php echo $stylesheetContent; ?>"><?php echo $theme['ini']['name']; ?></span>
                                </div>
                            </div>
                        </label>
                    </li>
                <?php } ?>
            </ul>

            <ul class="form-element">
                <?php if (AppUser::getInstance()->getPosition() === AppUser::POSTION_ADMINSTRATOR) { ?>
                    <li class="checkbox">
                        <span><?php echo lng('system.setting_theme.form.input.options'); ?></span>
                        <ul>
                            <li>
                                <input type="checkbox" id="write-system" name="write_system" value="1"<?php if ($writeSystem == true) { ?> checked="checked"<?php } ?>/>
                                <label for="write-system">
                                    <span><?php echo lng('system.setting_theme.form.input.write_system'); ?></span>
                                </label>
                            </li>
                        </ul>
                    </li>
                <?php } ?>
                <li class="button">
                    <button type="submit" name="choose" id="button-save-on-javascript">
                        <span><?php echo lng('system.setting_theme.form.button.choose'); ?></span>
                    </button>
                    <a href="<?php echo env('app.http.host'); ?>">
                        <span><?php echo lng('system.setting_theme.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
        <li>
            <a href="<?php echo env('app.http.host'); ?>/system/setting.php">
                <span class="icomoon icon-config"></span>
                <span><?php echo lng('system.setting.menu_action.setting_manager'); ?></span>
            </a>
        </li>
        <?php if (AppUser::getInstance()->getPosition() === AppUser::POSTION_ADMINSTRATOR) { ?>
            <li>
                <a href="<?php echo env('app.http.host'); ?>/system/setting_system.php">
                    <span class="icomoon icon-config"></span>
                    <span><?php echo lng('system.setting.menu_action.setting_system'); ?></span>
                </a>
            </li>
        <?php } ?>
        <li>
            <a href="<?php echo env('app.http.host'); ?>/user/setting.php">
                <span class="icomoon icon-config"></span>
                <span><?php echo lng('system.setting.menu_action.setting_profile'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>