<?php

    define('LOADED',                  1);
    define('DISABLE_CHECK_LOGIN',     1);
    define('SESSION_NAME_LOCK_COUNT', 'login_lock_count');
    define('SESSION_NAME_LOCK_TIME',  'login_lock_time');

    require_once('global.php');

    use Librarys\App\AppUser;
    use Librarys\App\AppAlert;
    use Librarys\App\AppJson;
    use Librarys\App\Config\AppConfig;
    use Librarys\Http\Request;
    use Librarys\Http\Secure\Captcha;

    if (AppUser::getInstance()->isLogin() && AppUser::getInstance()->isUserBand(null, true) == false) {
        if (isset($_POST['init']) == false || Request::isDesktop() == false) {
            AppJson::getInstance()->setResponseCodeSystem(DEKSTOP_CODE_IS_LOGIN_ALREADY);
            AppAlert::info(lng('user.login.alert.login_already'), ALERT_INDEX, env('app.http.host'));
        }
    }

    $title = lng('user.login.title_page');
    AppAlert::setID(ALERT_USER_LOGIN);
    require_once('..' . SP . 'incfiles' . SP . 'header.php');

    $username = null;
    $password = null;
    $captcha  = null;

    $captchaEnable       = AppConfig::getInstance()->getSystem('login.enable_captcha_secure');
    $isEnabledLockCount  = AppConfig::getInstance()->get('login.enable_lock_count_failed');
    $maxLockCountFailed  = AppConfig::getInstance()->get('login.max_lock_count');
    $timeLockCountFailed = AppConfig::getInstance()->get('login.time_lock');
    $currentTimeNow      = intval($_SERVER['REQUEST_TIME']);
    $isLockCountStatus   = false;
    $currentCountLock    = 0;
    $currentTimeLock     = 0;

    if ($isEnabledLockCount) {
        if (Request::session()->has(SESSION_NAME_LOCK_COUNT))
            $currentCountLock = intval(addslashes(Request::session()->get(SESSION_NAME_LOCK_COUNT)));

        if (Request::session()->has(SESSION_NAME_LOCK_TIME))
            $currentTimeLock = intval(addslashes(Request::session()->get(SESSION_NAME_LOCK_TIME)));

        if ($currentTimeNow - $currentTimeLock > $timeLockCountFailed) {
            if ($currentCountLock >= $maxLockCountFailed && $currentTimeLock > 0 && isset($_SESSION[SESSION_NAME_LOCK_COUNT]) && isset($_SESSION[SESSION_NAME_LOCK_TIME]))
               AppAlert::success(lng('user.login.alert.unlock_count'));

            Request::session()->remove(SESSION_NAME_LOCK_COUNT);
            Request::session()->remove(SESSION_NAME_LOCK_TIME);

            $currentCountLock  = 0;
            $currentTimeLock   = $currentTimeNow;
            $isLockCountStatus = false;
        } else {
            $isLockCountStatus = $currentCountLock >= $maxLockCountFailed;
        }
    } else {
        if (Request::session()->has(SESSION_NAME_LOCK_COUNT))
            Request::session()->remove(SESSION_NAME_LOCK_COUNT);

        if (Request::session()->has(SESSION_NAME_LOCK_TIME))
            Request::session()->remove(SESSION_NAME_LOCK_TIME);
    }

    if ($isEnabledLockCount && $isLockCountStatus) {
        $timeLockCalc = ($currentTimeLock + $timeLockCountFailed) - $currentTimeNow;

        if ($timeLockCalc < 60) {
            $timeLockCalc = $timeLockCalc . 's';
        } else if ($timeLockCalc >= 60 && $timeLockCalc < 3600) {
            $timeLockCalcMinute = floor($timeLockCalc / 60);
            $timeLockCalcSecond = $timeLockCalc - ($timeLockCalcMinute * 60);
            $timeLockCalc       = $timeLockCalcMinute . ':' . $timeLockCalcSecond . 's';
        } else {
            $timeLockCalcHour   = floor($timeLockCalc / 3600);
            $timeLockCalcMinute = floor(($timeLockCalc - ($timeLockCalcHour * 3600)) / 60);
            $timeLockCalcSecond = $timeLockCalc - (($timeLockCalcHour * 3600) + ($timeLockCalcMinute * 60));
            $timeLockCalc       = $timeLockCalcHour . ':' . $timeLockCalcMinute . ':' . $timeLockCalcSecond . 's';
        }

        if (isset($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'], 'post') === 0)
            Request::redirect('login.php');

        AppAlert::danger(lng('user.login.alert.lock_count_failed', 'count', $currentCountLock, 'time', $timeLockCalc));
    } else if (isset($_POST['submit'])) {
        $user     = null;
        $username = addslashes($_POST['username']);
        $password = addslashes($_POST['password']);

        if (isset($_POST['captcha']))
            $captcha  = addslashes($_POST['captcha']);

        if (empty($username) || empty($password)) {
            AppAlert::danger(lng('user.login.alert.not_input_username_or_password'));
        } else if ($captchaEnable && empty($captcha)) {
            AppAlert::danger(lng('user.login.alert.not_input_captcha'));
        } else if ($captchaEnable && strcmp($captcha, Request::session()->get(Captcha::SESSION_NAME)) !== 0) {
           AppAlert::danger(lng('user.login.alert.captcha_wrong'));
        } else if (($idUser = AppUser::getInstance()->isUser($username, $password)) === false) {
            AppAlert::danger(lng('user.login.alert.username_or_password_wrong'));
        } else if ($idUser === null || empty($idUser)) {
            AppAlert::danger(lng('user.login.alert.user_not_exists'));
        } else if (AppUser::getInstance()->isUserBand($idUser, false)) {
            AppAlert::danger(lng('user.login.alert.user_is_band'));
        } else if (AppUser::getInstance()->createSessionUser($idUser) == false) {
            AppAlert::danger(lng('user.login.alert.login_failed'));
        } else {
            if (Request::session()->has(SESSION_NAME_LOCK_COUNT))
                Request::session()->remove(SESSION_NAME_LOCK_COUNT);

            if (Request::session()->has(SESSION_NAME_LOCK_TIME))
                Request::session()->remove(SESSION_NAME_LOCK_TIME);

          AppAlert::success(lng('user.login.alert.login_success'), ALERT_INDEX, env('app.http.host'));
        }

        Request::session()->put(SESSION_NAME_LOCK_COUNT, intval(++$currentCountLock));
        Request::session()->put(SESSION_NAME_LOCK_TIME,  intval(time()));

        $captcha = null;
    }

    if (Request::isDesktop()) {
        if (AppUser::getInstance()->isLogin())
            AppJson::getInstance()->setResponseCodeSystem(DESKTOP_CODE_IS_LOGIN);
        else
            AppJson::getInstance()->setResponseCodeSystem(DESKTOP_CODE_IS_NOT_LOGIN);

        AppJson::getInstance()->setResponseDataSystem([
            'is_login' => AppUser::getInstance()->isLogin()
        ]);

        AppJson::getInstance()->toResult();
    }

    if (Request::isUseManagerDemo())
        AppAlert::info(lng('user.login.alert.is_use_manager_demo'));
?>

    <div id="container-login">
        <?php AppAlert::display(); ?>

        <?php if ($isLockCountStatus == false) { ?>
            <div id="login">
                <form action="<?php echo env('app.http.host'); ?>/user/login.php" method="post" id="login-form" autocomplete="off" class="not-autoload">
                    <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

                    <ul>
                        <li class="input">
                            <input type="text" name="username" value="<?php echo stripslashes(htmlspecialchars($username)); ?>" placeholder="<?php echo lng('user.login.form.input_username_placeholder'); ?>" autofocus="autofocus"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>/>
                            <span class="icomoon icon-user"></span>
                        </li>
                        <li class="input">
                            <input type="password" name="password" value="<?php echo stripslashes(htmlspecialchars($password)); ?>" placeholder="<?php echo lng('user.login.form.input_password_placeholder'); ?>"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>/>
                            <span class="icomoon icon-key"></span>
                        </li>
                        <?php if ($captchaEnable) { ?>
                            <li class="input captcha">
                                <input type="text" name="captcha" value="<?php echo stripslashes(htmlspecialchars($captcha)); ?>" placeholder="<?php echo lng('user.forgot_password.form.input_captcha_placeholder'); ?>" autofocus="autofocus"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>/>
                                <span class="icomoon icon-secure"></span>
                                <img src="<?php echo Captcha::create()->exportBase64(); ?>" alt="Captcha"/>
                            </li>
                        <?php } ?>
                        <li class="button">
                            <?php if (AppConfig::getInstance()->getSystem('login.enable_forgot_password')) { ?>
                                <a href="forgot_password.php" id="forgot-password">
                                    <span><?php echo lng('user.login.form.forgot_password'); ?></span>
                                </a>
                            <?php } ?>
                            <button type="submit" name="submit"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>>
                                <span><?php echo lng('user.login.form.button_login'); ?></span>
                            </button>
                        </li>
                    </ul>
                </form>
            </div>
        <?php } ?>
    </div>

<?php require_once('..' .SP . 'incfiles' . SP . 'footer.php'); ?>