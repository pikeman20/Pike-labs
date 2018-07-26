<?php

    define('LOADED',  1);
    define('SETTING', 1);

    use Librarys\App\AppAlert;
    use Librarys\App\AppUser;
    use Librarys\App\Config\AppConfig;

    require_once('global.php');

    if (AppUser::getInstance()->getPosition() !== AppUser::POSTION_ADMINSTRATOR)
        AppAlert::warning(lng('system.setting.alert.user_not_permission'), ALERT_SYSTEM_SETTING, env('app.http.host') . '/system/setting.php');

    $title = lng('system.setting_system.title_page');
    AppAlert::setID(ALERT_SYSTEM_SETTING_SYSTEM);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $forms = [
        'http_referer' => null,

        'cache' => [
            'lifetime' => AppConfig::getInstance()->getSystem('cache.lifetime')
        ],

        'tmp' => [
            'lifetime' => AppConfig::getInstance()->getSystem('tmp.lifetime'),
            'limit'    => AppConfig::getInstance()->getSystem('tmp.limit')
        ],

        'login' => [
            'enable_forgot_password'   => AppConfig::getInstance()->getSystem('login.enable_forgot_password'),
            'enable_lock_count_failed' => AppConfig::getInstance()->getSystem('login.enable_lock_count_failed'),
            'enable_captcha_secure'    => AppConfig::getInstance()->getSystem('login.enable_captcha_secure'),
            'max_lock_count'           => AppConfig::getInstance()->getSystem('login.max_lock_count'),
            'time_lock'                => AppConfig::getInstance()->getSystem('login.time_lock'),
            'time_login'               => AppConfig::getInstance()->getSystem('login.time_login')
        ],

        'enable_disable' => [
            'check_password_default' => AppConfig::getInstance()->getSystem('enable_disable.check_password_default'),
            'development'            => AppConfig::getInstance()->getSystem('enable_disable.development')
        ],

        'check_update' => [
            'enable' => AppConfig::getInstance()->getSystem('check_update.enable'),
            'time' => AppConfig::getInstance()->getSystem('check_update.time'),
        ]
    ];

    if (isset($_POST['save'])) {
        $isFailed      = false;
        $isFailedCheck = false;

        $listCheck = [
            'login.max_lock_count' => [
                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.login_max_lock_count_is_small', 'count', 3);
                    },

                    'callback' => function($value) {
                        return $value < 3;
                    }
                ],

                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.login_max_lock_count_is_large', 'count', 10);
                    },

                    'callback' => function($value) {
                        return $value > 10;
                    }
                ]
            ],

            'login.time_lock' => [
                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.login_time_lock_is_small', 'time', 30);
                    },

                    'callback' => function($value) {
                        return $value < 30;
                    }
                ],

                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.login_time_lock_is_large', 'time', 86400);
                    },

                    'callback' => function($value) {
                        return $value > 86400;
                    }
                ]
            ],

            'login.time_login' => [
                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.login_time_login_is_small', 'time', 300);
                    },

                    'callback' => function($value) {
                        return $value < 300;
                    }
                ]
            ],

            'cache.lifetime' => [
                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.cache_lifetime_is_small', 'time', 300);
                    },

                    'callback' => function($value) {
                        return $value < 300;
                    }
                ],

                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.cache_lifetime_is_large', 'time', 86400);
                    },

                    'callback' => function($value) {
                        return $value > 86400;
                    }
                ]
            ],

            'tmp.lifetime' => [
                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.tmp_lifetime_is_small', 'time', 300);
                    },

                    'callback' => function($value) {
                        return $value < 300;
                    }
                ],

                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.tmp_lifetime_is_large', 'time', 86400);
                    },

                    'callback' => function($value) {
                        return $value > 86400;
                    }
                ]
            ],

            'tmp.limit' => [
                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.tmp_limit_is_small', 'count', 10);
                    },

                    'callback' => function($value) {
                        return $value < 10;
                    }
                ],

                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.tmp_limit_is_large', 'count', 100);
                    },

                    'callback' => function($value) {
                        return $value > 100;
                    }
                ]
            ],

            'check_update.time' => [
                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.check_update_time_is_small', 'time', 86400);
                    },

                    'callback' => function($value) {
                        return $value < 86400;
                    }
                ],

                [
                    'alert' => function($value) {
                        return lng('system.setting_system.alert.check_update_time_is_large', 'time', 86400 * 7);
                    },

                    'callback' => function($value) {
                        return $value > (7 * 86400);
                    }
                ]
            ]
        ];

        foreach ($forms['login'] AS $key => &$value) {
            $envKey          = 'login.' . $key;
            $formKey         = 'login_' . $key;
            $isEnableDisable = isset($_POST[$formKey]) && strpos($formKey, 'login_enable_') === 0;

            if ($isEnableDisable)
                $formKey = 'login_' . $key;

            if (isset($_POST[$formKey])) {
                if ($isEnableDisable)
                    $value = boolval(addslashes($_POST[$formKey]));
                else
                    $value = intval(addslashes($_POST[$formKey]));
            } else {
                if ($isEnableDisable)
                    $value = false;
                else
                    $value = 0;
            }

            if (array_key_exists($envKey, $listCheck)) {
                foreach ($listCheck[$envKey] AS $arrs) {
                    if ($arrs['callback']($value)) {
                        $isFailedCheck = true;

                        AppAlert::danger($arrs['alert']($value));
                        break;
                    }
                }
            }

            if ($isFailedCheck == false && AppConfig::getInstance()->setSystem($envKey, $value) == false) {
                $isFailed = true;
                AppAlert::danger(lng('system.setting_system.alert.save_setting_failed'));

                break;
            }
        }

        if ($isFailed == false && $isFailedCheck == false) {
            foreach ($forms['cache'] AS $key => &$value) {
                $envKey  = 'cache.' . $key;
                $formKey = 'cache_' . $key;

                if (isset($_POST[$formKey]))
                    $value = intval(addslashes($_POST[$formKey]));
                else
                    $value = false;

                if (array_key_exists($envKey, $listCheck)) {
                    foreach ($listCheck[$envKey] AS $arrs) {
                        if ($arrs['callback']($value)) {
                            $isFailedCheck = true;

                            AppAlert::danger($arrs['alert']($value));
                            break;
                        }
                    }
                }

                if ($isFailedCheck == false && AppConfig::getInstance()->setSystem($envKey, $value) == false) {
                    $isFailed = true;
                    AppAlert::danger(lng('system.setting_system.alert.save_setting_failed'));

                    break;
                }
            }
        }

        if ($isFailed == false && $isFailedCheck == false) {
            foreach ($forms['tmp'] AS $key => &$value) {
                $envKey  = 'tmp.' . $key;
                $formKey = 'tmp_' . $key;

                if (isset($_POST[$formKey]))
                    $value = intval(addslashes($_POST[$formKey]));
                else
                    $value = false;

                if (array_key_exists($envKey, $listCheck)) {
                    foreach ($listCheck[$envKey] AS $arrs) {
                        if ($arrs['callback']($value)) {
                            $isFailedCheck = true;

                            AppAlert::danger($arrs['alert']($value));
                            break;
                        }
                    }
                }

                if ($isFailedCheck == false && AppConfig::getInstance()->setSystem($envKey, $value) == false) {
                    $isFailed = true;
                    AppAlert::danger(lng('system.setting_system.alert.save_setting_failed'));

                    break;
                }
            }
        }

        if ($isFailed == false && $isFailedCheck == false) {
            foreach ($forms['enable_disable'] AS $key => &$value) {
                $envKey  = 'enable_disable.' . $key;
                $formKey = 'enable_disable_' . $key;

                if (isset($_POST[$formKey]))
                    $value = boolval(addslashes($_POST[$formKey]));
                else
                    $value = false;

                if (array_key_exists($envKey, $listCheck)) {
                    foreach ($listCheck[$envKey] AS $arrs) {
                        if ($arrs['callback']($value)) {
                            $isFailedCheck = true;

                            AppAlert::danger($arrs['alert']($value));
                            break;
                        }
                    }
                }

                if ($isFailedCheck == false && AppConfig::getInstance()->setSystem($envKey, $value) == false) {
                    $isFailed = true;
                    AppAlert::danger(lng('system.setting_system.alert.save_setting_failed'));

                    break;
                }
            }
        }

        if ($isFailed == false && $isFailedCheck == false) {
            foreach ($forms['check_update'] AS $key => &$value) {
                $envKey  = 'check_update.' . $key;
                $formKey = 'check_update_' . $key;

                if (isset($_POST[$formKey]))
                    $value = boolval(addslashes($_POST[$formKey]));
                else
                    $value = false;

                if (array_key_exists($envKey, $listCheck)) {
                    foreach ($listCheck[$envKey] AS $arrs) {
                        if ($arrs['callback']($value)) {
                            $isFailedCheck = true;

                            AppAlert::danger($arrs['alert']($value));
                            break;
                        }
                    }
                }

                if ($isFailedCheck == false && AppConfig::getInstance()->setSystem($envKey, $value) == false) {
                    $isFailed = true;
                    AppAlert::danger(lng('system.setting_system.alert.save_setting_failed'));

                    break;
                }
            }
        }

        if ($isFailed == false && $isFailedCheck == false) {
            if (AppConfig::getInstance()->write(true))
                AppAlert::success(lng('system.setting_system.alert.save_setting_success'));
            else
                AppAlert::danger(lng('system.setting_system.alert.save_setting_failed'));
        }
    }

    $settingTextInputs = [
        [
            'config_key'      => 'login.max_lock_count',
            'label_lng'       => 'system.setting_system.form.input.login_max_lock_count',
            'placeholder_lng' => 'system.setting_system.form.placeholder.input_login_max_lock_count',
            'name_input'      => 'login_max_lock_count',
            'type_input'      => 'number',
            'value_input'     => $forms['login']['max_lock_count']
        ],

        [
            'config_key'      => 'login.time_lock',
            'label_lng'       => 'system.setting_system.form.input.login_time_lock',
            'placeholder_lng' => 'system.setting_system.form.placeholder.input_login_time_lock',
            'name_input'      => 'login_time_lock',
            'type_input'      => 'number',
            'value_input'     => $forms['login']['time_lock']
        ],

        [
            'config_key'      => 'login.time_login',
            'label_lng'       => 'system.setting_system.form.input.login_time_login',
            'placeholder_lng' => 'system.setting_system.form.placeholder.input_login_time_login',
            'name_input'      => 'login_time_login',
            'type_input'      => 'number',
            'value_input'     => $forms['login']['time_login']
        ],

        [
            'config_key'      => 'cache.lifetime',
            'label_lng'       => 'system.setting_system.form.input.cache_lifetime',
            'placeholder_lng' => 'system.setting_system.form.placeholder.input_cache_lifetime',
            'name_input'      => 'cache_lifetime',
            'type_input'      => 'number',
            'value_input'     => $forms['cache']['lifetime']
        ],

        [
            'config_key'      => 'tmp.lifetime',
            'label_lng'       => 'system.setting_system.form.input.tmp_lifetime',
            'placeholder_lng' => 'system.setting_system.form.placeholder.input_tmp_lifetime',
            'name_input'      => 'tmp_lifetime',
            'type_input'      => 'number',
            'value_input'     => $forms['tmp']['lifetime']
        ],

        [
            'config_key'      => 'tmp.limit',
            'label_lng'       => 'system.setting_system.form.input.tmp_limit',
            'placeholder_lng' => 'system.setting_system.form.placeholder.input_tmp_limit',
            'name_input'      => 'tmp_limit',
            'type_input'      => 'number',
            'value_input'     => $forms['tmp']['limit']
        ],

        [
            'config_key'      => 'check_update.time',
            'label_lng'       => 'system.setting_system.form.input.check_update_time',
            'placeholder_lng' => 'system.setting_system.form.placeholder.input_check_update_time',
            'name_input'      => 'check_update_time',
            'type_input'      => 'number',
            'value_input'     => $forms['check_update']['time']
        ]
    ];

    $settingCheckboxInputs = [
        'enable_disable' => [
            [
                'config_key'      => 'login.enable_forgot_password',
                'label_lng'       => 'system.setting_system.form.input.enable_forgot_password',
                'id_input'        => 'login-enable-forgot-password',
                'name_input'      => 'login_enable_forgot_password',
                'value_input'     => $forms['login']['enable_forgot_password']
            ],

            [
                'config_key'      => 'login.enable_lock_count_failed',
                'label_lng'       => 'system.setting_system.form.input.enable_lock_count_failed',
                'id_input'        => 'login-enable-lock-count-failed',
                'name_input'      => 'login_enable_lock_count_failed',
                'value_input'     => $forms['login']['enable_lock_count_failed']
            ],

            [
                'config_key'      => 'login.enable_captcha_secure',
                'label_lng'       => 'system.setting_system.form.input.enable_captcha_secure',
                'id_input'        => 'login-enable-captcha-secure',
                'name_input'      => 'login_enable_captcha_secure',
                'value_input'     => $forms['login']['enable_captcha_secure']
            ],

            [
                'config_key'      => 'enable_disable.check_password_default',
                'label_lng'       => 'system.setting_system.form.input.enable_check_password_default',
                'id_input'        => 'enable-disable-check-password-default',
                'name_input'      => 'enable_disable_check_password_default',
                'value_input'     => $forms['enable_disable']['check_password_default']
            ],

            [
                'config_key'      => 'enable_disable.development',
                'label_lng'       => 'system.setting_system.form.input.enable_development',
                'id_input'        => 'enable-disable-development',
                'name_input'      => 'enable_disable_development',
                'value_input'     => $forms['enable_disable']['development']
            ]
        ],

        'check_update' => [
            [
                'config_key'      => 'check_update.enable',
                'label_lng'       => 'system.setting_system.form.input.check_update_enable',
                'id_input'        => 'check-update-enable',
                'name_input'      => 'check_update_enable',
                'value_input'     => $forms['check_update']['enable']
            ]
        ]
    ];

?>

    <?php AppAlert::display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('system.setting.title_page'); ?></span>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/system/setting_system.php" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <?php foreach ($settingTextInputs AS $inputs) { ?>
                    <li class="input">
                        <span><?php echo lng($inputs['label_lng']); ?></span>
                        <input type="<?php echo $inputs['type_input']; ?>" name="<?php echo $inputs['name_input']; ?>" value="<?php echo $inputs['value_input']; ?>" placeholder="<?php echo lng($inputs['placeholder_lng']); ?>"/>
                    </li>
                <?php } ?>
                <li class="checkbox">
                    <span><?php echo lng('system.setting_system.form.input.enable_disable_label'); ?></span>
                    <ul>
                        <?php foreach ($settingCheckboxInputs['enable_disable'] AS $checkboxs) { ?>
                            <li>
                                <input type="checkbox" id="<?php echo $checkboxs['id_input']; ?>" name="<?php echo $checkboxs['name_input']; ?>" value="1"<?php if ($checkboxs['value_input'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="<?php echo $checkboxs['id_input']; ?>">
                                    <span><?php echo lng($checkboxs['label_lng']); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                        <?php foreach ($settingCheckboxInputs['check_update'] AS $checkboxs) { ?>
                            <li>
                                <input type="checkbox" id="<?php echo $checkboxs['id_input']; ?>" name="<?php echo $checkboxs['name_input']; ?>" value="1"<?php if ($checkboxs['value_input'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="<?php echo $checkboxs['id_input']; ?>">
                                    <span><?php echo lng($checkboxs['label_lng']); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
                <li class="button">
                    <button type="submit" name="save" id="button-save-on-javascript">
                        <span><?php echo lng('system.setting_system.form.button.save'); ?></span>
                    </button>
                    <a href="<?php echo env('app.http.host'); ?>">
                        <span><?php echo lng('system.setting_system.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
        <li class="info"><span><?php echo lng('system.setting_system.alert.tips'); ?></span></li>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="<?php echo env('app.http.host'); ?>/system/setting.php">
                <span class="icomoon icon-config"></span>
                <span><?php echo lng('system.setting.menu_action.setting_manager'); ?></span>
            </a>
        </li>
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