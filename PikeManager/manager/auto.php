<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\Config\AppConfig;
    use Librarys\Http\Request;

    define('LOADED', 1);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    $autoload    = AppConfig::getInstance()->getSystem('enable_disable.autoload');
    $httpReferer = env('app.http.host');
    $idAlert     = null;

    if (isset($_GET['referer']))
        $httpReferer = AppDirectory::rawDecode(trim($_GET['referer']));
    else if (isset($_SERVER['HTTP_REFERER']))
        $httpReferer = AppDirectory::rawEncode(trim($_SERVER['HTTP_REFERER']));

    if (isset($_GET['id']))
        $idAlert = addslashes($_GET['id']);

    if ($idAlert == null || empty($idAlert))
        $idAlert = ALERT_INDEX;

    sleep(1);

    if (isset($_GET['referer']))
        Request::redirect($httpReferer);

    AppConfig::getInstance()->setSystem('enable_disable.autoload', $autoload == false);
    AppConfig::getInstance()->write(true);

    $httpReferer = 'auto.php?referer=' . $httpReferer . '&id=' . $idAlert;

    if ($autoload)
        AppAlert::info(lng('auto.alert.disable_autoload_success'), $idAlert, $httpReferer);
    else
        AppAlert::info(lng('auto.alert.enable_autoload_success'), $idAlert, $httpReferer);

?>