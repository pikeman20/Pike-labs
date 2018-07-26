<?php

    define('LOADED',  1);
    define('SETTING', 1);

    use Librarys\App\AppAlert;
    use Librarys\App\AppUser;
    use Librarys\App\Config\AppConfig;

    require_once('global.php');

    $title = lng('system.setting.title_page');
    AppAlert::setID(ALERT_SYSTEM_SETTING);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $forms = [
        'http_referer' => null,

        'paging' => [
            'file_home_list'      => AppConfig::getInstance()->get('paging.file_home_list'),
            'file_view_zip'       => AppConfig::getInstance()->get('paging.file_view_zip'),
            'file_edit_text'      => AppConfig::getInstance()->get('paging.file_edit_text'),
            'mysql_list_data'     => AppConfig::getInstance()->get('paging.mysql_list_data')
        ],

        'login' => [
            'enable_forgot_password' => AppConfig::getInstance()->get('login.enable_forgot_password')
        ],

        'enable_disable' => [
            'button_save_on_javascript'       => AppConfig::getInstance()->get('enable_disable.button_save_on_javascript'),
            'auto_focus_input_last'           => AppConfig::getInstance()->get('enable_disable.auto_focus_input_last'),
            'count_checkbox_file_javascript'  => AppConfig::getInstance()->get('enable_disable.count_checkbox_file_javascript'),
            'count_checkbox_mysql_javascript' => AppConfig::getInstance()->get('enable_disable.count_checkbox_mysql_javascript'),
            'list_file_double'                => AppConfig::getInstance()->get('enable_disable.list_file_double'),
            'list_database_double'            => AppConfig::getInstance()->get('enable_disable.list_database_double'),
            'header_fixed'                    => AppConfig::getInstance()->get('enable_disable.header_fixed')
        ],

        'auto_redirect' => [
            'file_rename' => AppConfig::getInstance()->get('auto_redirect.file_rename'),
            'file_chmod'  => AppConfig::getInstance()->get('auto_redirect.file_chmod'),

            'create_directory' => AppConfig::getInstance()->get('auto_redirect.create_directory'),
            'create_file'      => AppConfig::getInstance()->get('auto_redirect.create_file'),
            'create_database'  => AppConfig::getInstance()->get('auto_redirect.create_database'),
            'rename_database'  => AppConfig::getInstance()->get('auto_redirect.rename_database')
        ],
    ];

    if (isset($_SERVER['HTTP_REFERER']))
        $forms['http_referer'] = trim($_SERVER['HTTP_REFERER']);

    if (isset($_POST['save'])) {
        $forms['http_referer'] = trim($_POST['http_referer']);
        $isFailed              = false;

        foreach ($forms['paging'] AS $key => &$value) {
            $envKey  = 'paging.' . $key;
            $formKey = 'paging_' . $key;

            if (isset($_POST[$formKey]))
                $value = intval(addslashes($_POST[$formKey]));
            else
                $value = 0;

            if (AppConfig::getInstance()->set($envKey, $value) == false) {
                $isFailed = true;
                AppAlert::danger(lng('system.setting.alert.save_setting_failed'));

                break;
            }
        }

        if ($isFailed == false) {
            if (isset($_POST['login_enable_forgot_password']))
                $forms['login']['enable_forgot_password'] = boolval(addslashes($_POST['login_enable_forgot_password']));
            else
                $forms['login']['enable_forgot_password'] = false;

            if (AppConfig::getInstance()->set('login.enable_forgot_password', $forms['login']['enable_forgot_password']) == false) {
                $isFailed = true;
                AppAlert::danger(lng('system.setting.alert.save_setting_failed'));
            }
        }

        if ($isFailed == false) {
            foreach ($forms['enable_disable'] AS $key => &$value) {
                $envKey  = 'enable_disable.' . $key;
                $formKey = 'enable_disable_' . $key;

                if (isset($_POST[$formKey]))
                    $value = boolval(addslashes($_POST[$formKey]));
                else
                    $value = false;

                if (AppConfig::getInstance()->set($envKey, $value) == false) {
                    $isFailed = true;
                    AppAlert::danger(lng('system.setting.alert.save_setting_failed'));

                    break;
                }
            }
        }

        if ($isFailed == false) {
            foreach ($forms['auto_redirect'] AS $key => &$value) {
                $envKey  = 'auto_redirect.' . $key;
                $formKey = 'auto_redirect_' . $key;

                if (isset($_POST[$formKey]))
                    $value = boolval(addslashes($_POST[$formKey]));
                else
                    $value = false;

                if (AppConfig::getInstance()->set($envKey, $value) == false) {
                    $isFailed = true;
                    AppAlert::danger(lng('system.setting.alert.save_setting_failed'));

                    break;
                }
            }
        }

        if ($isFailed == false) {
        	if (AppConfig::getInstance()->write())
                AppAlert::success(lng('system.setting.alert.save_setting_success'), null);
    	    else
                AppAlert::danger(lng('system.setting.alert.save_setting_failed'));
        }
    }

    if ($forms['http_referer'] == null || empty($forms['http_referer']))
        $forms['http_referer'] = env('app.http.host');
    else if (strpos($forms['http_referer'], $_SERVER['PHP_SELF']) !== false)
        $forms['http_referer'] = env('app.http.host');

    $settingTextInputs = [
        [
            'config_key'      => 'paging.file_home_list',
            'label_lng'       => 'system.setting.form.input.paging_file_home_list',
            'placeholder_lng' => 'system.setting.form.placeholder.input_paging_file_home_list',
            'name_input'      => 'paging_file_home_list',
            'type_input'      => 'number',
            'value_input'     => $forms['paging']['file_home_list']
        ],

        [
            'config_key'      => 'paging.file_view_zip',
            'label_lng'       => 'system.setting.form.input.paging_file_view_zip',
            'placeholder_lng' => 'system.setting.form.placeholder.input_paging_file_view_zip',
            'name_input'      => 'paging_file_view_zip',
            'type_input'      => 'number',
            'value_input'     => $forms['paging']['file_view_zip']
        ],

        [
            'config_key'      => 'paging.file_edit_text',
            'label_lng'       => 'system.setting.form.input.paging_file_edit_text',
            'placeholder_lng' => 'system.setting.form.placeholder.input_paging_file_edit_text',
            'name_input'      => 'paging_file_edit_text',
            'type_input'      => 'number',
            'value_input'     => $forms['paging']['file_edit_text']
        ],

        [
            'config_key'      => 'paging_mysql_list_data',
            'label_lng'       => 'system.setting.form.input.paging_mysql_list_data',
            'placeholder_lng' => 'system.setting.form.placeholder.input_paging_mysql_list_data',
            'name_input'      => 'paging_mysql_list_data',
            'type_input'      => 'number',
            'value_input'     => $forms['paging']['mysql_list_data']
        ]
    ];

    $settingCheckboxInputs = [
        'enable_disable' => [
            [
                'config_key'      => 'login.enable_forgot_password',
                'label_lng'       => 'system.setting.form.input.enable_forgot_password',
                'id_input'        => 'login-enable-forgot-password',
                'name_input'      => 'login_enable_forgot_password',
                'value_input'     => $forms['login']['enable_forgot_password']
            ],

            [
                'config_key'      => 'enable_disable.button_save_on_javascript',
                'label_lng'       => 'system.setting.form.input.enable_disable_button_save_on_javascript',
                'id_input'        => 'enable-disable-button-save-on-javascript',
                'name_input'      => 'enable_disable_button_save_on_javascript',
                'value_input'     => $forms['enable_disable']['button_save_on_javascript']
            ],

            [
                'config_key'      => 'enable_disable.auto_focus_input_last',
                'label_lng'       => 'system.setting.form.input.enable_disable_auto_focus_input_last',
                'id_input'        => 'enable-disable-auto-focus-input-last',
                'name_input'      => 'enable_disable_auto_focus_input_last',
                'value_input'     => $forms['enable_disable']['auto_focus_input_last']
            ],

            [
                'config_key'      => 'enable_disable.count_checkbox_file_javascript',
                'label_lng'       => 'system.setting.form.input.enable_disable_count_checkbox_file_javascript',
                'id_input'        => 'enable-disable-count-checkbox-file-javascript',
                'name_input'      => 'enable_disable_count_checkbox_file_javascript',
                'value_input'     => $forms['enable_disable']['count_checkbox_file_javascript']
            ],

            [
                'config_key'      => 'enable_disable.count_checkbox_mysql_javascript',
                'label_lng'       => 'system.setting.form.input.enable_disable_count_checkbox_mysql_javascript',
                'id_input'        => 'enable-disable-count-checkbox-mysql-javascript',
                'name_input'      => 'enable_disable_count_checkbox_mysql_javascript',
                'value_input'     => $forms['enable_disable']['count_checkbox_mysql_javascript']
            ],

            [
                'config_key'      => 'enable_disable.list_file_double',
                'label_lng'       => 'system.setting.form.input.enable_disable_list_file_double',
                'id_input'        => 'enable-disable-list-file-double',
                'name_input'      => 'enable_disable_list_file_double',
                'value_input'     => $forms['enable_disable']['list_file_double']
            ],

            [
                'config_key'      => 'enable_disable.list_database_double',
                'label_lng'       => 'system.setting.form.input.enable_disable_list_database_double',
                'id_input'        => 'enable-disable-list-database-double',
                'name_input'      => 'enable_disable_list_database_double',
                'value_input'     => $forms['enable_disable']['list_database_double']
            ],

            [
                'config_key'      => 'enable_disable.header_fixed',
                'label_lng'       => 'system.setting.form.input.enable_disable_header_fixed',
                'id_input'        => 'enable-disable-hedader-fixed',
                'name_input'      => 'enable_disable_header_fixed',
                'value_input'     => $forms['enable_disable']['header_fixed']
            ]
        ],

        'auto_redirect' => [
            [
                'config_key'      => 'auto_redirect.file_rename',
                'label_lng'       => 'system.setting.form.input.enable_auto_redirect_file_rename',
                'id_input'        => 'auto-redirect-file-rename',
                'name_input'      => 'auto_redirect_file_rename',
                'value_input'     => $forms['auto_redirect']['file_rename']
            ],

            [
                'config_key'      => 'auto_redirect.file_chmod',
                'label_lng'       => 'system.setting.form.input.enable_auto_redirect_file_chmod',
                'id_input'        => 'auto-redirect-file-chmod',
                'name_input'      => 'auto_redirect_file_chmod',
                'value_input'     => $forms['auto_redirect']['file_chmod']
            ],

            [
                'config_key'      => 'auto_redirect.create_directory',
                'label_lng'       => 'system.setting.form.input.enable_auto_redirect_create_directory',
                'id_input'        => 'auto-redirect-create-directory',
                'name_input'      => 'auto_redirect_create_directory',
                'value_input'     => $forms['auto_redirect']['create_directory']
            ],

            [
                'config_key'      => 'auto_redirect.create_file',
                'label_lng'       => 'system.setting.form.input.enable_auto_redirect_create_file',
                'id_input'        => 'auto-redirect-create-file',
                'name_input'      => 'auto_redirect_create_file',
                'value_input'     => $forms['auto_redirect']['create_file']
            ],

            [
                'config_key'      => 'auto_redirect.create_database',
                'label_lng'       => 'system.setting.form.input.enable_auto_redirect_create_database',
                'id_input'        => 'auto-redirect-create-database',
                'name_input'      => 'auto_redirect_create_database',
                'value_input'     => $forms['auto_redirect']['create_database']
            ],

            [
                'config_key'      => 'auto_redirect.rename_database',
                'label_lng'       => 'system.setting.form.input.enable_auto_redirect_rename_database',
                'id_input'        => 'auto-redirect-rename-database',
                'name_input'      => 'auto_redirect_rename_database',
                'value_input'     => $forms['auto_redirect']['rename_database']
            ]
        ]
    ];
?>

    <?php AppAlert::display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('system.setting.title_page'); ?></span>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/system/setting.php" method="post" class="not-autoload">
	        <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>
            <input type="hidden" name="http_referer" value="<?php echo $forms['http_referer']; ?>"/>

            <ul class="form-element">
                <?php foreach ($settingTextInputs AS $inputs) { ?>
                    <?php if (AppConfig::getInstance()->isEnvEnabled($inputs['config_key'])) { ?>
                        <li class="input">
                            <span><?php echo lng($inputs['label_lng']); ?></span>
                            <input type="<?php echo $inputs['type_input']; ?>" name="<?php echo $inputs['name_input']; ?>" value="<?php echo $inputs['value_input']; ?>" placeholder="<?php echo lng($inputs['placeholder_lng']); ?>"/>
                        </li>
                    <?php } ?>
                <?php } ?>
                <li class="checkbox">
                    <span><?php echo lng('system.setting.form.input.enable_disable_label'); ?></span>
                    <ul>
                        <?php foreach ($settingCheckboxInputs['enable_disable'] AS $checkboxs) { ?>
                            <?php if (AppConfig::getInstance()->isEnvEnabled($checkboxs['config_key'])) { ?>
                                <li>
                                    <input type="checkbox" id="<?php echo $checkboxs['id_input']; ?>" name="<?php echo $checkboxs['name_input']; ?>" value="1"<?php if ($checkboxs['value_input'] == true) { ?> checked="checked"<?php } ?>/>
                                    <label for="<?php echo $checkboxs['id_input']; ?>">
                                        <span><?php echo lng($checkboxs['label_lng']); ?></span>
                                    </label>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </li>
                <li class="checkbox">
                    <span><?php echo lng('system.setting.form.input.auto_redirect_label'); ?></span>
                	<ul>
                        <?php foreach ($settingCheckboxInputs['auto_redirect'] AS $checkboxs) { ?>
                            <?php if (AppConfig::getInstance()->isEnvEnabled($checkboxs['config_key'])) { ?>
                                <li>
                                    <input type="checkbox" id="<?php echo $checkboxs['id_input']; ?>" name="<?php echo $checkboxs['name_input']; ?>" value="1"<?php if ($checkboxs['value_input'] == true) { ?> checked="checked"<?php } ?>/>
                                    <label for="<?php echo $checkboxs['id_input']; ?>">
                                        <span><?php echo lng($checkboxs['label_lng']); ?></span>
                                    </label>
                                </li>
                            <?php } ?>
                        <?php } ?>
                	</ul>
                </li>
                <li class="button">
                    <button type="submit" name="save" id="button-save-on-javascript">
                        <span><?php echo lng('system.setting.form.button.save'); ?></span>
                    </button>
                    <a href="<?php echo $forms['http_referer']; ?>">
                        <span><?php echo lng('system.setting.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
    	<li class="info">
    		<span><?php echo lng('system.setting.alert.tips'); ?></span>
    	</li>
    </ul>

    <ul class="menu-action">
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
        <li>
            <a href="<?php echo env('app.http.host'); ?>/system/setting_theme.php">
                <span class="icomoon icon-theme"></span>
                <span><?php echo lng('system.setting.menu_action.setting_theme'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>