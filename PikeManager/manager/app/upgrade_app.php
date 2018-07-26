<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppUser;
    use Librarys\App\AppUpdate;
    use Librarys\App\AppUpgrade;
    use Librarys\App\Config\AppAboutConfig;
    use Librarys\App\Config\AppUpgradeConfig;
    use Librarys\File\FileInfo;
    use Librarys\Parse\MarkdownParse;

    define('LOADED',                   1);
    define('DISABLE_ALERT_HAS_UPDATE', 1);
    define('PARAMETER_INSTALL_URL',    'install');

    require_once('global.php');

    if (AppUser::getInstance()->isPositionAdminstrator() == false)
        AppAlert::danger(lng('user.default.alert.not_permission_access_feature'), ALERT_INDEX, env('app.http.host'));

    $title      = lng('app.upgrade_app.title_page');
    $themes     = [ env('resource.filename.theme.about'), env('resource.filename.theme.markdown') ];
    $appUpdate  = new AppUpdate(AppAboutConfig::getInstance());
    $appUpgrade = new AppUpgrade(AppAboutConfig::getInstance());
    $servers    = $appUpdate->getServers();
    AppAlert::setID(ALERT_APP_UPGRADE_APP);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $hasUpgrade = $appUpgrade->checkHasUpgradeLocal($errorCheckUpgrade);

    if ($hasUpgrade == false && $errorCheckUpgrade === AppUpgrade::ERROR_CHECK_UPGRADE_NONE)
        AppAlert::info(lng('app.check_update.alert.version_is_latest', 'version_current', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION)), ALERT_APP_ABOUT, 'about.php');
    else if ($errorCheckUpgrade === AppUpgrade::ERROR_CHECK_UPGRADE_FILE_NOT_FOUND)
        AppAlert::danger(lng('app.upgrade_app.alert.error_check_upgrade_file_not_found'), ALERT_APP_CHECK_UPDATE, 'check_update.php');
    else if ($errorCheckUpgrade === AppUpgrade::ERROR_CHECK_UPGRADE_ADDITIONAL_UPDATE_NOT_FOUND)
        AppAlert::danger(lng('app.upgrade_app.alert.error_check_upgrade_additional_update_not_found'), ALERT_APP_CHECK_UPDATE, 'check_update.php');
    else if ($errorCheckUpgrade === AppUpgrade::ERROR_CHECK_UPGRADE_FILE_DATA_ERROR)
        AppAlert::danger(lng('app.upgrade_app.alert.error_check_upgrade_file_data_error'), ALERT_APP_CHECK_UPDATE, 'check_update.php');
    else if ($errorCheckUpgrade === AppUpgrade::ERROR_CHECK_UPGRADE_FILE_DATA_ADDITIONAL_UPDATE_ERROR)
        AppAlert::danger(lng('app.upgrade_app.alert.error_check_upgrade_file_data_additional_update_error'), ALERT_APP_CHECK_UPDATE, 'check_update.php');
    else if ($errorCheckUpgrade === AppUpgrade::ERROR_CHECK_UPGRADE_MD5_CHECK_FAILED)
        AppAlert::danger(lng('app.upgrade_app.alert.error_check_upgrade_md5_check_failed'), ALERT_APP_CHECK_UPDATE, 'check_update.php');
    else if ($errorCheckUpgrade === AppUpgrade::ERROR_CHECK_UPGRADE_MD5_ADDITIONAL_UPDATE_CHECK_FAILED)
        AppAlert::danger(lng('app.upgrade_app.alert.error_check_upgrade_md5_additional_update_check_failed'), ALERT_APP_CHECK_UPDATE, 'check_update.php');

    if (isset($_GET[PARAMETER_INSTALL_URL])) {
        $errorZipExtract = null;

        if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_UPGRADE && $appUpgrade->installUpgradeNow(false, $errorZipExtract, $errorUpgrade) == false) {
            if ($errorZipExtract === AppUpgrade::ERROR_ZIP_NOT_OPEN_FILE_UPGRADE)
                AppAlert::danger(lng('app.upgrade_app.alert.error_zip_not_open_file_upgrade'));
            else if ($errorZipExtract === AppUpgrade::ERROR_ZIP_EXTRACT_FILE_UPGRADE)
                AppAlert::danger(lng('app.upgrade_app.alert.error_zip_extract_file_upgrade'));
            else if ($errorUpgrade === AppUpgrade::ERROR_UPGRADE_NOT_LIST_FILE_APP)
                AppAlert::danger(lng('app.upgrade_app.alert.error_upgrade_not_list_file_app'));
            else
                AppAlert::danger(lng('app.upgrade_app.alert.error_unknown'));
        } else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_ADDITIONAL && $appUpgrade->installAdditionalNow(false, $errorZipExtract) == false) {
            if ($errorZipExtract === AppUpgrade::ERROR_ZIP_NOT_OPEN_FILE_ADDITIONAL)
                AppAlert::danger(lng('app.upgrade_app.alert.error_zip_not_open_file_additional'));
            else if ($errorZipExtract === AppUpgrade::ERROR_ZIP_EXTRACT_FILE_ADDITIONAL)
                AppAlert::danger(lng('app.upgrade_app.alert.error_zip_extract_file_additional'));
            else
                AppAlert::danger(lng('app.upgrade_app.alert.error_unknown'));
        } else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_UPGRADE) {
            AppAlert::success(lng('app.upgrade_app.alert.install_upgrade_app_success', 'version', AppUpgradeConfig::getInstance()->get(AppUpdate::ARRAY_DATA_KEY_VERSION)), ALERT_APP_CHECK_UPDATE, 'check_update.php');
        } else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_ADDITIONAL) {
            AppAlert::success(lng('app.upgrade_app.alert.install_additional_app_success', 'version', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION)), ALERT_APP_CHECK_UPDATE, 'check_update.php');
        }
    }
?>

    <?php AppAlert::display(); ?>

    <div class="form-action">
        <div id="about" class="no-box-shadow">
            <h1><?php echo AppAboutConfig::getInstance()->get('name'); ?></h1>

            <ul>
                <li class="label">
                    <ul>
                        <li><span><?php echo lng('app.upgrade_app.info.label_server_name'); ?></span></li>
                        <li><span><?php echo lng('app.upgrade_app.info.label_version'); ?></span></li>
                        <li><span><?php echo lng('app.upgrade_app.info.label_build_last'); ?></span></li>
                        <li><span><?php echo lng('app.upgrade_app.info.label_type_bin'); ?></span></li>
                        <li><span><?php echo lng('app.upgrade_app.info.label_md5_bin_check'); ?></span></li>
                        <li><span><?php echo lng('app.upgrade_app.info.label_data_length'); ?></span></li>
                    </ul>
                </li>

                <li class="value">
                    <ul>
                        <li><span><?php echo AppUpgradeConfig::getInstance()->get(AppUpdate::ARRAY_DATA_KEY_SERVER_NAME); ?></span></li>
                        <li><span><?php echo AppUpgradeConfig::getInstance()->get(AppUpdate::ARRAY_DATA_KEY_VERSION); ?></span></li>
                        <li><span><?php echo date('d.m.Y - H:i:s', intval(AppUpgradeConfig::getInstance()->get(AppUpdate::ARRAY_DATA_KEY_BUILD_LAST))); ?></span></li>

                        <?php if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_UPGRADE) { ?>
                            <li><span><?php echo lng('app.upgrade_app.info.value_type_bin_install_upgrdae'); ?></span></li>
                            <li><span><?php echo AppUpgradeConfig::getInstance()->get(AppUpdate::ARRAY_DATA_KEY_MD5_BIN_CHECK); ?></span></li>
                            <li><span><?php echo FileInfo::fileSize(AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_BIN_FILENAME), true); ?></span></li>
                        <?php } else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_ADDITIONAL) { ?>
                            <li><span><?php echo lng('app.upgrade_app.info.value_type_bin_install_additional'); ?></span></li>
                            <li><span><?php echo AppUpgradeConfig::getInstance()->get(AppUpdate::ARRAY_DATA_KEY_MD5_ADDITIONAL_CHECK); ?></span></li>
                            <li><span><?php echo FileInfo::fileSize(AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_ADDITIONAL_FILENAME), true); ?></span></li>
                        <?php } ?>
                    </ul>
                </li>

                <?php $markdownParse    = new MarkdownParse(); ?>
                <?php $changelogContent = FileInfo::fileReadContents(AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_CHANGELOG_FILENAME)); ?>
                <?php $readmeContent    = FileInfo::fileReadContents(AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_README_FILENAME)); ?>

                <?php if ($readmeContent !== false && $readmeContent !== null && empty($readmeContent) == false) { ?>
                    <li class="message show-hidden divider-top">
                        <div class="title"><span><?php echo lng('app.upgrade_app.info.label_readme'); ?></span></div>
                        <div class="markdown content" id="show-readme"><?php echo $markdownParse->text($readmeContent); ?></div>
                        <a href="#show-readme" class="show"><span><?php echo lng('app.upgrade_app.info.show_more'); ?></span></a>
                    </li>
                <?php } ?>

                <?php if ($changelogContent !== false && $changelogContent !== null && empty($changelogContent) == false) { ?>
                    <li class="message show-hidden divider-top divider-bottom">
                        <div class="title"><span><?php echo lng('app.upgrade_app.info.label_changelog'); ?></span></div>
                        <div class="markdown content" id="show-changelog"><?php echo $markdownParse->text($changelogContent); ?></div>
                        <a href="#show-changelog" class="show"><span><?php echo lng('app.upgrade_app.info.show_more'); ?></span></a>
                    </li>
                <?php } ?>
            </ul>

            <div class="about-button-check button-action-box center">
                <a href="upgrade_app.php?<?php echo PARAMETER_INSTALL_URL; ?>" class="not-autoload">
                    <?php if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_UPGRADE) { ?>
                        <span><?php echo lng('app.upgrade_app.form.button.upgrade'); ?></span>
                    <?php } else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_ADDITIONAL) { ?>
                        <span><?php echo lng('app.upgrade_app.form.button.additional'); ?></span>
                    <?php } ?>
                </a>
            </div>
        </div>
    </div>

    <ul class="menu-action">
        <li>
            <a href="about.php">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('app.about.menu_action.about'); ?></span>
            </a>
        </li>

        <li>
            <a href="check_update.php">
                <span class="icomoon icon-update"></span>
                <span><?php echo lng('app.about.menu_action.check_update'); ?></span>
            </a>
        </li>

        <li>
            <a href="validate_app.php">
                <span class="icomoon icon-check"></span>
                <span><?php echo lng('app.about.menu_action.validate_app'); ?></span>
            </a>
        </li>
        <li>
            <a href="help.php">
                <span class="icomoon icon-help"></span>
                <span><?php echo lng('app.about.menu_action.help'); ?></span>
            </a>
        </li>
        <li>
            <a href="feedback.php">
                <span class="icomoon icon-feedback"></span>
                <span><?php echo lng('app.about.menu_action.feedback'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>