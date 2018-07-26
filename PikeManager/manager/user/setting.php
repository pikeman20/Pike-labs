<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppUser;
    use Librarys\App\Config\AppUserConfig;
    use Librarys\Http\Validate;
    use Librarys\Http\Request;

    define('LOADED', 1);
    require_once('global.php');

    $title = lng('user.setting.title_page');
    AppAlert::setID(ALERT_USER_SETTING);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $forms = [
        'username'        => AppUser::getInstance()->getUsername(),
        'email'           => AppUser::getInstance()->getEmail(),
        'secret_question' => AppUser::getInstance()->getSecretQuestion(),
        'secret_answer'   => AppUser::getInstance()->getSecretAnswer(),
        'password_old'    => null,
        'password_new'    => null,
        'password_verify' => null
    ];

    if (isset($_POST['change'])) {
        $forms['username']        = addslashes($_POST['username']);
        $forms['email']           = addslashes($_POST['email']);
        $forms['password_old']    = addslashes($_POST['password_old']);
        $forms['password_new']    = addslashes($_POST['password_new']);
        $forms['password_verify'] = addslashes($_POST['password_verify']);
        $forms['secret_question'] = addslashes($_POST['secret_question']);
        $forms['secret_answer']   = addslashes($_POST['secret_answer']);

        $usernameConfig = AppUser::getInstance()->get('username');
        $emailConfig    = AppUser::getInstance()->get('email');
        $passwordConfig = AppUser::getInstance()->get('password');
        $secretQuestion = AppUser::getInstance()->get('secret_question');
        $secretAnswer   = AppUser::getInstance()->get('secret_answer');

        if (empty($forms['username'])) {
            AppAlert::danger(lng('user.setting.alert.not_input_username'));
        } else if (empty($forms['email'])) {
            AppAlert::danger(lng('user.setting.alert.not_input_email'));
        } else if (empty($forms['secret_question'])) {
            AppAlert::danger(lng('user.setting.alert.not_input_secret_question'));
        } else if (empty($forms['secret_answer'])) {
            AppAlert::danger(lng('user.setting.alert.not_input_secret_answer'));
        } else if (empty($forms['password_old'])) {
            AppAlert::danger(lng('user.setting.alert.not_input_password_old'));
        } else if (empty($forms['password_new']) == false && empty($forms['password_verify'])) {
            AppAlert::danger(lng('user.setting.alert.not_input_password_verify'));
        } else if (AppUser::isValidateUsername($forms['username']) == false) {
            AppAlert::danger(lng('user.setting.alert.username_not_validate', 'validate', AppUser::USERNAME_VALIDATE));
        } else if (Validate::email($forms['email']) == false) {
            AppAlert::danger(lng('user.setting.alert.email_not_validate'));
        } else if (empty($passwordConfig) == false && AppUser::getInstance()->checkPassword($passwordConfig, $forms['password_old']) === false) {
            AppAlert::danger(lng('user.setting.alert.password_old_wrong'));
        } else if (empty($forms['password_new']) == false && strcmp($forms['password_new'], $forms['password_verify']) !== 0) {
            AppAlert::danger(lng('user.setting.alert.password_new_not_equal_password_verify'));
        } else if (
            strcasecmp($usernameConfig, $forms['username'])        === 0 &&
            strcasecmp($emailConfig,    $forms['email'])           === 0 &&
            strcasecmp($secretQuestion, $forms['secret_question']) === 0 &&
            strcasecmp($secretAnswer,   $forms['secret_answer'])   === 0 &&

            (
                empty($forms['password_new']) ||
                AppUser::checkPassword($passwordConfig, $forms['password_new'])
            )
        ) {
            AppAlert::danger(lng('user.setting.alert.nothing_change'));
        } else {
            $time = time();

            if (
                AppUser::getInstance()->setConfig(AppUserConfig::ARRAY_KEY_USERNAME,                      $forms['username'])         == false ||
                AppUser::getInstance()->setConfig(AppUserConfig::ARRAY_KEY_EMAIL,                         $forms['email'])            == false ||
                AppUser::getInstance()->setConfig(AppUserConfig::ARRAY_KEY_SECRET_QUESTION, base64_encode($forms['secret_question'])) == false ||
                AppUser::getInstance()->setConfig(AppUserConfig::ARRAY_KEY_SECRET_ANSWER,   base64_encode($forms['secret_answer']))   == false ||
                AppUser::getInstance()->setConfig(AppUserConfig::ARRAY_KEY_MODIFY_AT,                     time())                     == false
            ) {
                AppAlert::danger(lng('user.setting.alert.change_config_info_failed'));
            } else if (
                empty($forms['password_new']) == false &&
                AppUser::getInstance()->setConfig(AppUserConfig::ARRAY_KEY_PASSWORD, AppUser::createPassword($forms['password_new'])) == false
            ) {
                AppAlert::danger(lng('user.setting.alert.change_config_password_failed'));
            } else if (Request::isUseManagerDemo()) {
                AppUser::getInstance()->exitSession();
                AppAlert::warning(lng('user.setting.alert.save_change_config_demo_not_permission'), ALERT_USER_LOGIN, 'login.php');
            } else if (AppUser::getInstance()->writeConfig(true) == false) {
                AppAlert::danger(lng('user.setting.alert.save_change_config_failed'));
            } else {
                AppAlert::success(lng('user.setting.alert.save_change_config_success'), ALERT_USER_LOGIN, 'login.php');
            }
        }

        $forms['username']        = stripslashes(htmlspecialchars($forms['username']));
        $forms['email']           = stripslashes(htmlspecialchars($forms['email']));
        $forms['password_old']    = stripslashes(htmlspecialchars($forms['password_old']));
        $forms['password_new']    = stripslashes(htmlspecialchars($forms['password_new']));
        $forms['password_verify'] = stripslashes(htmlspecialchars($forms['password_verify']));
        $forms['secret_question'] = stripslashes(htmlspecialchars($forms['secret_question']));
        $forms['secret_question'] = stripslashes(htmlspecialchars($forms['secret_question']));
        $forms['secret_answer']   = stripslashes(htmlspecialchars($forms['secret_answer']));
    }
?>

    <?php AppAlert::display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('user.setting.title_page'); ?></span>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/user/setting.php" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.username'); ?></span>
                    <input type="text" name="username" value="<?php echo $forms['username']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_username'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.email'); ?></span>
                    <input type="email" name="email" value="<?php echo $forms['email']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_email'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.secret_question'); ?></span>
                    <input type="text" name="secret_question" value="<?php echo $forms['secret_question']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_secret_question'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.secret_answer'); ?></span>
                    <input type="text" name="secret_answer" value="<?php echo $forms['secret_answer']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_secret_answer'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.password_old'); ?></span>
                    <input type="password" name="password_old" value="<?php echo $forms['password_old']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_password_old'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.password_new'); ?></span>
                    <input type="password" name="password_new" value="<?php echo $forms['password_new']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_password_new'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.password_verify'); ?></span>
                    <input type="password" name="password_verify" value="<?php echo $forms['password_verify']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_password_verify'); ?>"/>
                </li>
                <li class="button">
                    <button type="submit" name="change">
                        <span><?php echo lng('user.setting.form.button.change'); ?></span>
                    </button>
                    <a href="<?php echo env('app.http.host'); ?>">
                        <span><?php echo lng('user.setting.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
        <li class="info"><?php echo lng('user.setting.alert.tips'); ?></li>
    </ul>

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
            <a href="<?php echo env('app.http.host'); ?>/system/setting_theme.php">
                <span class="icomoon icon-theme"></span>
                <span><?php echo lng('system.setting.menu_action.setting_theme'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>