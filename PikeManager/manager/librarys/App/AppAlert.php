<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\File\FileInfo;
    use Librarys\App\AppUser;
    use Librarys\App\AppUpdate;
    use Librarys\App\AppUpgrade;
    use Librarys\App\AppJson;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Config\AppAboutConfig;
    use Librarys\Http\Request;

    class AppAlert
    {

        private static $instance;
        private static $id;
        private static $langMsg;

        const SESSION_NAME_PREFIX = 'ALERT_';

        const DANGER  = 'danger';
        const SUCCESS = 'success';
        const WARNING = 'warning';
        const INFO    = 'info';
        const NONE    = 'none';

        protected function __construct()
        {
            if (FileInfo::isTypeFile(env('resource.define.alert')))
                require_once(env('resource.define.alert'));
        }

        protected function __wakeup()
        {

        }

        protected function __clone()
        {

        }

        public function execute()
        {

        }

        public static function getInstance()
        {
            if (null === self::$instance)
                self::$instance = new AppAlert();

            return self::$instance;
        }

        public static function danger($message, $id = null, $urlGoto = null)
        {
            return self::add($message, self::DANGER, $id, $urlGoto);
        }

        public static function success($message, $id = null, $urlGoto = null)
        {
            return self::add($message, self::SUCCESS, $id, $urlGoto);
        }

        public static function warning($message, $id = null, $urlGoto = null)
        {
            return self::add($message, self::WARNING, $id, $urlGoto);
        }

        public static function info($message, $id = null, $urlGoto = null)
        {
            return self::add($message, self::INFO, $id, $urlGoto);
        }

        public static function add($message, $type = self::DANGER, $id = null, $urlGoto = null)
        {
            if ($id == null) {
                if (self::$id == null)
                    self::$id = time();

                $id = self::$id;
            }

            if ($message == null && self::$langMsg != null)
                $message = self::$langMsg;

            if (Request::isDesktop(false)) {
                AppJson::getInstance()->addAlert($message, $type, $id);

                return self::$instance;
            }

            Request::session()->put(self::SESSION_NAME_PREFIX . $id, [
                'message' => $message,
                'type'    => $type
            ], true);

            if ($urlGoto !== null)
                Request::redirect($urlGoto);

            return self::$instance;
        }

        public static function display()
        {
            if (AppConfig::getInstance()->getSystem('enable_disable.check_password_default') && AppUser::getInstance()->checkUserIsUsePasswordDefault())
                self::warning(lng('home.alert.password_user_is_equal_default', 'time', AppUser::TIME_SHOW_WARNING_PASSWORD_DEFAULT));

            if (AppUser::getInstance()->checkEmptySecret())
                self::warning(lng('home.alert.secret_is_empty'));

            if (AppUser::getInstance()->isPositionAdminstrator() && defined('DISABLE_ALERT_HAS_UPDATE') == false && self::hasAlertDisplay() == false && AppConfig::getInstance()->get('check_update.enable', false) == true) {
                $timeCurrent    = time();
                $timeShow       = 300;
                $timeCheck      = AppConfig::getInstance()->get('check_update.time');
                $checkLast      = AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_CHECK_AT, $timeCurrent);

                if ($timeCurrent - $checkLast >= $timeCheck) {
                    $appUpdate = new AppUpdate(AppAboutConfig::getInstance());

                    if ($appUpdate->checkUpdate() === true) {
                        $updateStatus = $appUpdate->getUpdateStatus();

                        if ($updateStatus === AppUpdate::RESULT_VERSION_IS_OLD)
                            self::success(lng('app.check_update.alert.version_is_old', 'version_current', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION), 'version_update', $appUpdate->getVersionUpdate()));
                        else if ($updateStatus === AppUpdate::RESULT_HAS_ADDITIONAL)
                            self::success(lng('app.check_update.alert.has_additional', 'version_current', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION)));
                        else
                            self::info(lng('app.check_update.alert.version_is_latest', 'version_current', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION)));
                    }
                } else {
                    $appUpgrade = new AppUpgrade(AppAboutConfig::getInstance());

                    if ($appUpgrade->checkHasUpgradeLocal()) {
                        if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_UPGRADE)
                            self::success(lng('app.check_update.alert.version_is_old', 'version_current', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION), 'version_update', $appUpgrade->getVersionUpgrade()));
                        else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_ADDITIONAL)
                            self::success(lng('app.check_update.alert.has_additional', 'version_current', AppAboutConfig::getInstance()->get(AppAboutConfig::ARRAY_KEY_VERSION)));
                    }
                }
            }

            if (self::$id != null && Request::session()->has(self::SESSION_NAME_PREFIX . self::$id) && count(Request::session()->get(self::SESSION_NAME_PREFIX . self::$id)) > 0) {
                $array  = Request::session()->get(self::SESSION_NAME_PREFIX . self::$id);
                $buffer = '<ul class="alert">';

                foreach ($array AS $index => $alert) {
                    if (is_object($alert['message']))
                        $alert['message'] = 'Object';
                    else if (is_array($alert['message']))
                        $alert['message'] = 'Array';

                    $buffer .= '<li class="' . $alert['type'] . '">';
                    $buffer .= '<span>' . $alert['message'] . '</span>';
                    $buffer .= '</li>';
                }

                $buffer .= '</ul>';

                echo($buffer);
                Request::session()->remove(self::SESSION_NAME_PREFIX . self::$id);
            }
        }

        public static function setID($id)
        {
            self::$id = $id;
        }

        public static function setLangMsg($key)
        {
            $args = func_get_args();
            $nums = func_num_args();

            if ($nums <= 1)
                $args = [];
            else
                $args = array_splice($args, 1, $nums);

            self::$langMsg = lng($key, $args);
        }

        public static function removeLangMsg()
        {
            self::$langMsg = null;
        }

        public static function getLangMsg()
        {
            return self::$langMsg;
        }

        public static function getId()
        {
            return self::$id;
        }

        public static function clean($id = null)
        {
            if ($id == null)
                $id = self::$id;

            if ($id == null)
                return;

            Request::session()->remove(self::SESSION_NAME_PREFIX . $id);
        }

        public static function hasAlertDisplay($id = null)
        {
            if ($id == null)
                $id = self::$id;

            if ($id == null)
                return false;

            return Request::session()->has(self::SESSION_NAME_PREFIX . $id) && count(Request::session()->get(self::SESSION_NAME_PREFIX . $id)) > 0;
        }

    }

?>