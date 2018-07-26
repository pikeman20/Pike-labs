<?php

    define('LOADED',      1);
    define('SYSTEM_INFO', 1);
    require_once('global.php');

    $title = lng('app.system_info.title_page');
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $onOffToString = function($value) {
        if ($value == 1 || strcasecmp($value, 'on') === 0 || strcasecmp($value, 'true') === 0)
            return 'On';

        return 'Off';
    };

    $nullToString = function($value) {
        if (empty($value) || is_null($value) || $value == null)
            return 'No value';

        return $value;
    };

    $enabledDisabledToString = function($value) {
        if ($value === null)
            return 'Unknown';

        if (!!$value)
            return 'Enabled';

        return 'Disabled';
    };

    $moduleLoad = function($name) {
        if (function_exists('apache_get_modules') == false)
            return null;

        if (in_array($name, apache_get_modules()) !== false)
            return true;

        return false;
    };

    $infos = [
        [
            'title' => 'Infomation',

            'labels' => [
                'System:',
                'Server api:',
                'Loaded Config File:',
                'PHP Path:'
            ],

            'values' => [
                php_uname(),
                php_sapi_name(),
                php_ini_loaded_file(),
                getenv('PATH')
            ]
        ],

        [
            'title' => 'Core',

            'labels' => [
                'PHP Version:',
                'Display errors:',
                'Display startup errors:',
                'File uploads:',
                'Disable functions:',
                'Include path:',
                'Log errors:',
                'Log errors max length:',
                'Max file uploads:',
                'Memory limit:',
                'Open basedir:',
                'Output buffering:',
                'Output encoding:',
                'Output handler:',
                'Post max size:',
                'Upload max file size:',
                'Upload tmp dir:',
                'User dir:'
            ],

            'values' => [
                phpversion(),

                $onOffToString(ini_get('display_errors')),
                $onOffToString(ini_get('display_startup_errors')),
                $onOffToString(ini_get('file_uploads')),

                $nullToString(ini_get('disable_functions')),
                $nullToString(ini_get('include_path')),
                $nullToString(ini_get('log_errors')),
                $nullToString(ini_get('log_errors_max_len')),
                $nullToString(ini_get('max_file_uploads')),
                $nullToString(ini_get('memory_limit')),
                $nullToString(ini_get('open_basedir')),
                $nullToString(ini_get('output_buffering')),
                $nullToString(ini_get('output_encoding')),
                $nullToString(ini_get('output_handler')),
                $nullToString(ini_get('post_max_size')),
                $nullToString(ini_get('upload_max_filesize')),
                $nullToString(ini_get('upload_tmp_dir')),
                $nullToString(ini_get('user_dir'))
            ]
        ],

        [
            'title' => 'CURL',

            'labels' => [
                'CURL support:',
                'CURL Infomation:'
            ],

            'values' => [
                $enabledDisabledToString(extension_loaded('curl')),
                extension_loaded('curl') ? curl_version()['version'] : 'Null'
            ]
        ],

        [
            'title' => 'Date',

            'labels' => [
                'Date/Time support:',
                'Default timezone:'
            ],

            'values' => [
                $enabledDisabledToString(date_default_timezone_get() != null),
                date_default_timezone_get()
            ]
        ],

        [
            'title' => 'Module apache load',

            'labels' => [
                'deflate:',
                'dir:',
                'filter:',
                'rewrite:',
            ],

            'values' => [
                $enabledDisabledToString($moduleLoad('mod_deflate')),
                $enabledDisabledToString($moduleLoad('mod_dir')),
                $enabledDisabledToString($moduleLoad('mod_filter')),
                $enabledDisabledToString($moduleLoad('mod_rewrite'))
            ]
        ],

        [
            'title' => 'Extension load',

            'labels' => [
                'exif:',
                'fileinfo:',
                'filter:',
                'ftp:',
                'gd:',
                'gettext:',
                'hash:',
                'iconv:',
                'json:',
                'libxml:',
                'mbstring:',
                'mhash:',
                'mysql:',
                'mysqli:',
                'mysqlnd:',
                'openssh:',
                'pcre:',
                'pdo:',
                'phar:',
                'session:',
                'shmop:',
                'sockets:',
                'sysvmsg:',
                'tokenizer:',
                'xmlrpc:',
                'zlib:'
            ],

            'values' => [
                $enabledDisabledToString(extension_loaded('exif')),
                $enabledDisabledToString(extension_loaded('fileinfo')),
                $enabledDisabledToString(extension_loaded('filter')),
                $enabledDisabledToString(extension_loaded('ftp')),
                $enabledDisabledToString(extension_loaded('gd')),
                $enabledDisabledToString(extension_loaded('gettext')),
                $enabledDisabledToString(extension_loaded('hash')),
                $enabledDisabledToString(extension_loaded('iconv')),
                $enabledDisabledToString(extension_loaded('json')),
                $enabledDisabledToString(extension_loaded('libxml')),
                $enabledDisabledToString(extension_loaded('mbstring')),
                $enabledDisabledToString(extension_loaded('mhash')),
                $enabledDisabledToString(extension_loaded('mysql')),
                $enabledDisabledToString(extension_loaded('mysqli')),
                $enabledDisabledToString(extension_loaded('mysqlnd')),
                $enabledDisabledToString(extension_loaded('openssh')),
                $enabledDisabledToString(extension_loaded('pcre')),
                $enabledDisabledToString(extension_loaded('pdo')),
                $enabledDisabledToString(extension_loaded('phar')),
                $enabledDisabledToString(extension_loaded('session')),
                $enabledDisabledToString(extension_loaded('shmop')),
                $enabledDisabledToString(extension_loaded('sockets')),
                $enabledDisabledToString(extension_loaded('sysvmsg')),
                $enabledDisabledToString(extension_loaded('tokenizer')),
                $enabledDisabledToString(extension_loaded('xmlrpc')),
                $enabledDisabledToString(extension_loaded('zlib')),
            ]
        ]
    ];
?>

    <?php foreach ($infos AS $arrs) { ?>
        <div id="about">
            <h1 class="small"><?php echo $arrs['title']; ?></h1>
            <ul>
                <li class="label">
                    <ul>
                        <?php foreach ($arrs['labels'] AS $label) { ?>
                            <li><span><?php echo $label; ?></span></li>
                        <?php } ?>
                    </ul>
                </li>
                <li class="value">
                    <ul>
                        <?php foreach ($arrs['values'] AS $value) { ?>
                            <?php $classHighlight = null; ?>
                            <?php if (strpos('on off enabled disabled unknown', strtolower($value)) !== false) $classHighlight = ' class="status-' . strtolower($value) . '"'; ?>

                            <li><span<?php echo $classHighlight; ?>><?php echo $value; ?></span></li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    <?php } ?>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>