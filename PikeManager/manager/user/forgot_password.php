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
    use Librarys\App\Config\AppUserConfig;
    use Librarys\Http\Request;
    use Librarys\Http\Validate;
    use Librarys\Http\Secure\Captcha;

    if (AppUser::getInstance()->isLogin() && AppUser::getInstance()->isUserBand(null, true) == false) {
        if (isset($_POST['init']) == false || Request::isDesktop() == false) {
            AppJson::getInstance()->setResponseCodeSystem(DEKSTOP_CODE_IS_LOGIN_ALREADY);
            AppAlert::info(lng('user.login.alert.login_already'), ALERT_INDEX, env('app.http.host'));
        }
    }

    if (AppConfig::getInstance()->getSystem('login.enable_forgot_password') == false)
        AppAlert::danger(lng('user.forgot_password.alert.forgot_password_not_enable'), ALERT_USER_LOGIN, 'login.php');

    $title = lng('user.forgot_password.title_page');
    AppAlert::setID(ALERT_USER_FORGOT_PASSWORD);
    require_once('..' . SP . 'incfiles' . SP . 'header.php');

    $isEnabledLockCount  = AppConfig::getInstance()->get('login.enable_lock_count_failed');
    $maxLockCountFailed  = AppConfig::getInstance()->get('login.max_lock_count');
    $timeLockCountFailed = AppConfig::getInstance()->get('login.time_lock');
    $currentTimeNow      = intval($_SERVER['REQUEST_TIME']);
    $isLockCountStatus   = false;
    $currentCountLock    = 0;
    $currentTimeLock     = 0;

    $username         = null;
    $captcha          = null;
    $secretQuestion   = null;
    $secretAnswerForm = null;
    $secretAnswerUser = null;
    $isCheckSuccess   = false;

    if ($isEnabledLockCount) {
        if (Request::session()->has(SESSION_NAME_LOCK_COUNT))
            $currentCountLock = intval(addslashes(Request::session()->get(SESSION_NAME_LOCK_COUNT)));

        if (Request::session()->has(SESSION_NAME_LOCK_TIME))
            $currentTimeLock = intval(addslashes(Request::session()->get(SESSION_NAME_LOCK_TIME)));

        if ($currentTimeNow - $currentTimeLock > $timeLockCountFailed) {
            if ($currentCountLock >= $maxLockCountFailed && $currentTimeLock > 0 && isset($_SESSION[SESSION_NAME_LOCK_COUNT]) && isset($_SESSION[SESSION_NAME_LOCK_TIME]))
                AppAlert::success(lng('user.forgot_password.alert.unlock_count'));

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
            Request::redirect('forgot_password.php');

        AppAlert::danger(lng('user.forgot_password.alert.lock_count_failed', 'count', $currentCountLock, 'time', $timeLockCalc));
    } else if (isset($_POST['check']) || isset($_POST['forgot'])) {
        $username = addslashes($_POST['username']);
        $captcha  = addslashes($_POST['captcha']);

        $infoUser = null;
        $idUser   = null;

        if (empty($username)) {
            AppAlert::danger(lng('user.forgot_password.alert.not_input_username'));
        } else if (isset($_POST['check']) && empty($captcha)) {
            AppAlert::danger(lng('user.forgot_password.alert.not_input_captcha'));
        } else if (isset($_POST['check']) &&  strcmp($captcha, Request::session()->get(Captcha::SESSION_NAME)) !== 0) {
            AppAlert::danger(lng('user.forgot_password.alert.captcha_wrong'));
        } else if (($idUser = AppUser::getInstance()->isUser($username, false, $infoUser)) === false) {
            AppAlert::danger(lng('user.forgot_password.alert.username_wrong'));
        } else if ($idUser === null || $infoUser == null || empty($idUser) || is_array($infoUser) == false) {
            AppAlert::danger(lng('user.forgot_password.alert.user_not_exists'));
        } else if (AppUser::getInstance()->isUserBand($idUser, false)) {
            AppAlert::danger(lng('user.forgot_password.alert.user_is_band'));
        } else {
            if (isset($_POST['check'])) {
                $currentCountLock = 0;

                if (Request::session()->has(SESSION_NAME_LOCK_COUNT))
                    Request::session()->remove(SESSION_NAME_LOCK_COUNT);

                if (Request::session()->has(SESSION_NAME_LOCK_TIME))
                    Request::session()->remove(SESSION_NAME_LOCK_TIME);
            }

            $isCheckSuccess   = true;

            $secretQuestion   = base64_decode($infoUser[AppUserConfig::ARRAY_KEY_SECRET_QUESTION]);
            $secretAnswerUser = base64_decode($infoUser[AppUserConfig::ARRAY_KEY_SECRET_ANSWER]);

            AppAlert::info(lng('user.forgot_password.alert.secret_question', 'secret_question', $secretQuestion));

            if (isset($_POST['forgot'])) {
                $passwordReset = null;

                if (isset($_POST['secret_answer']))
                    $secretAnswerForm = addslashes($_POST['secret_answer']);

                if (empty($captcha)) {
                    AppAlert::danger(lng('user.forgot_password.alert.not_input_captcha'));
                } else if (empty($secretAnswerForm)) {
                    AppAlert::danger(lng('user.forgot_password.alert.not_input_secret_answer'));
                } else if (strcmp($captcha, Request::session()->get(Captcha::SESSION_NAME)) !== 0) {
                    AppAlert::danger(lng('user.forgot_password.alert.captcha_wrong'));
                } else if (strcasecmp($secretAnswerForm, $secretAnswerUser) !== 0) {
                    AppAlert::danger(lng('user.forgot_password.alert.secret_answer_wrong'));
                } else if (AppUser::getInstance()->resetPassword($idUser, $passwordReset) == false) {
                    AppAlert::danger(lng('user.forgot_password.alert.reset_password_failed'));
                } else {
                    $currentCountLock = 0;

                    if (Request::session()->has(SESSION_NAME_LOCK_COUNT))
                        Request::session()->remove(SESSION_NAME_LOCK_COUNT);

                    if (Request::session()->has(SESSION_NAME_LOCK_TIME))
                        Request::session()->remove(SESSION_NAME_LOCK_TIME);

                    AppAlert::success(lng('user.forgot_password.alert.reset_password_success', 'username', $infoUser[AppUserConfig::ARRAY_KEY_USERNAME], 'password', $passwordReset), ALERT_USER_LOGIN, 'login.php');
                }
            }
        }

        Request::session()->put(SESSION_NAME_LOCK_COUNT, intval(++$currentCountLock));
        Request::session()->put(SESSION_NAME_LOCK_TIME,  intval(time()));

        $captcha = null;
    }
?>

    <div id="container-login">
        <?php AppAlert::display(); ?>

        <?php if ($isLockCountStatus == false) { ?>
            <div id="login">
                <form action="<?php echo env('app.http.host'); ?>/user/forgot_password.php" method="post" id="login-form">
                    <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

                    <ul>
                        <li class="input">
                            <input type="text" name="username" value="<?php echo stripslashes(htmlspecialchars($username)); ?>" placeholder="<?php echo lng('user.forgot_password.form.input_username_placeholder'); ?>" autofocus="autofocus"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>/>
                            <span class="icomoon icon-user"></span>
                        </li>
                        <?php if ($isCheckSuccess) { ?>
                            <li class="input">
                                <input type="text" name="secret_answer" value="<?php echo stripslashes(htmlspecialchars($secretAnswerForm)); ?>" placeholder="<?php echo lng('user.forgot_password.form.input_secret_answer_placeholder'); ?>" autofocus="autofocus"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>/>
                                <span class="icomoon icon-question"></span>
                            </li>
                        <?php } ?>
                        <li class="input captcha">
                            <input type="text" name="captcha" value="<?php echo stripslashes(htmlspecialchars($captcha)); ?>" placeholder="<?php echo lng('user.forgot_password.form.input_captcha_placeholder'); ?>" autofocus="autofocus"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>/>
                            <span class="icomoon icon-secure"></span>
                            <img src="<?php echo Captcha::create()->exportBase64(); ?>" alt="Captcha"/>
                        </li>
                        <li class="button">
                            <a href="login.php" id="login">
                                <span><?php echo lng('user.forgot_password.form.login'); ?></span>
                            </a>
                            <?php if ($isCheckSuccess) { ?>
                                <button type="submit" name="forgot"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>>
                                    <span><?php echo lng('user.forgot_password.form.button_forgot_password'); ?></span>
                                </button>
                            <?php } else { ?>
                                <button type="submit" name="check"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>>
                                    <span><?php echo lng('user.forgot_password.form.button_check_user'); ?></span>
                                </button>
                            <?php } ?>
                        </li>
                    </ul>
                </form>
            </div>
        <?php } ?>
    </div>

<?php require_once('..' .SP . 'incfiles' . SP . 'footer.php'); ?>