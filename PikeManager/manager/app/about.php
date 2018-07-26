<?php

    use Librarys\App\AppUpgrade;
    use Librarys\App\AppAlert;
    use Librarys\App\AppUser;
    use Librarys\App\Config\AppAboutConfig;

    define('LOADED',                   1);
    define('ABOUT',                    1);
    define('DISABLE_CHECK_LOGIN',      1);
    define('DISABLE_ALERT_HAS_UPDATE', 1);

    require_once('global.php');

    $title      = lng('app.about.title_page');
    $themes     = [ env('resource.filename.theme.about') ];
    $appUpgrade = null;
    $hasUpgrade = false;
    AppAlert::setID(ALERT_APP_ABOUT);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    if (AppUser::getInstance()->isPositionAdminstrator()) {
        $appUpgrade = new AppUpgrade(AppAboutConfig::getInstance());
        $hasUpgrade = $appUpgrade->checkHasUpgradeLocal();

        if ($hasUpgrade && AppAlert::hasAlertDisplay() == false) {
            if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_UPGRADE)
                AppAlert::success(lng('app.check_update.alert.version_is_old', 'version_current', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION), 'version_update', $appUpgrade->getVersionUpgrade()));
            else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_ADDITIONAL)
                AppAlert::success(lng('app.check_update.alert.has_additional', 'version_current', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION)));
        }
    }

?>

    <?php AppAlert::display(); ?>

    <div id="about">
        <h1><?php echo AppAboutConfig::getInstance()->get('name'); ?></h1>
        <ul>
        	<li class="label">
        		<ul>
        			<li><span><?php echo lng('app.about.info.label.author'); ?></span></li>
        			<li><span><?php echo lng('app.about.info.label.version'); ?></span></li>
        			<li><span><?php echo lng('app.about.info.label.email'); ?></span></li>
        			<li><span><?php echo lng('app.about.info.label.github'); ?></span></li>
        			<li><span><?php echo lng('app.about.info.label.facebook'); ?></span></li>
        			<li><span><?php echo lng('app.about.info.label.phone'); ?></span></li>
        			<li><span><?php echo lng('app.about.info.label.create_at'); ?></span></li>
                    <li><span><?php echo lng('app.about.info.label.check_at'); ?></span></li>
                    <li><span><?php echo lng('app.about.info.label.upgrade_at'); ?></span></li>
                    <li><span><?php echo lng('app.about.info.label.build_at'); ?></span></li>
    		</ul>
        	</li>
        	<li class="value">
        		<ul>
        			<li><span><?php echo AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_NAME); ?></span></li>
        			<li><span><?php echo AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION); ?> <?php if (AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_IS_BETA)) echo 'beta'; ?></span></li>
        			<li><span><?php echo AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_EMAIL); ?></span></li>
        			<li><span><a href="<?php echo AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_GIT_LINK); ?>" target="_blank"><?php echo AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_GIT_TITLE); ?></a></span></li>
        			<li><span><a href="<?php echo AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_FB_LINK); ?>" target="_blank"><?php echo AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_FB_TITLE); ?></a></span></li>
        			<li><span><?php echo AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_PHONE); ?></span></li>
        			<li><span><?php echo date('d.m.Y - H:i', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_CREATE_AT)); ?></span></li>

                    <?php if (AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_CHECK_AT) <= 0) { ?>
                        <li><span><?php echo lng('app.check_update.info.value.not_last_check_update'); ?></span></li>
                    <?php } else { ?>
                        <li><span><?php echo date('d.m.Y - H:i:s', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_CHECK_AT)); ?></span></li>
                    <?php } ?>

                    <?php if (AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_UPGRADE_AT) <= 0) { ?>
                        <li><span><?php echo lng('app.check_update.info.value.not_last_upgrade'); ?></span></li>
                    <?php } else { ?>
                        <li><span><?php echo date('d.m.Y - H:i:s', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_UPGRADE_AT)); ?></span></li>
                    <?php } ?>

                    <li><span><?php echo date('d.m.Y - H:i:s', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_BUILD_AT)); ?></span></li>
        		</ul>
        	</li>
        </ul>
    </div>

    <?php if (AppUser::getInstance()->isLogin()) { ?>
        <ul class="menu-action">
            <?php if (AppUser::getInstance()->isPositionAdminstrator()) { ?>
                <li>
                    <a href="check_update.php">
                        <span class="icomoon icon-update"></span>
                        <span><?php echo lng('app.about.menu_action.check_update'); ?></span>
                    </a>
                </li>

                <?php if ($hasUpgrade) { ?>
                    <li>
                        <a href="upgrade_app.php">
                            <span class="icomoon icon-update"></span>
                            <span><?php echo lng('app.about.menu_action.upgrade_app'); ?></span>
                        </a>
                    </li>
                <?php } ?>
            <?php } ?>

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
    <?php } ?>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>