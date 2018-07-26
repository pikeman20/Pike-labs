<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\AppFileUnzip;
    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\Http\Request;
    use Librarys\Zip\PclZip;

    define('LOADED',               1);
    define('EXISTS_FUNC_OVERRIDE', 1);
    define('EXISTS_FUNC_SKIP',     2);

    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if (AppDirectory::getInstance()->isFileSeparatorNameExists() == false)
        AppAlert::danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if (AppDirectory::getInstance()->isPermissionDenyPath())
        AppAlert::danger(lng('home.alert.path_not_permission', 'path', AppDirectory::getInstance()->getDirectoryAndName()), ALERT_INDEX, env('app.http.host'));

    $appFileUnzip    = new AppFileUnzip();
    $appLocationPath = new AppLocationPath('index.php');
    $appLocationPath->setIsPrintLastEntry(true);
    $appLocationPath->setIsLinkLastEntry(true);

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, AppDirectory::getInstance()->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      AppDirectory::getInstance()->getPage(),            AppDirectory::getInstance()->getPage() > 1);
    $appParameter->add(AppDirectory::PARAMETER_NAME_URL,      AppDirectory::getInstance()->getNameEncode(),      true);

    $fileInfo    = new FileInfo(AppDirectory::getInstance()->getDirectory() . SP . AppDirectory::getInstance()->getName());
    $fileMime    = new FileMime($fileInfo);
    $isDirectory = $fileInfo->isDirectory();

    if ($isDirectory) {
        $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
        AppAlert::danger(lng('home.alert.path_not_exists'), ALERT_INDEX, $appParameter->toString(true));
    }

    if ($fileMime->isFormatArchiveZip() == false) {
        $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
        AppAlert::danger(lng('file_unzip.alert.file_is_not_format_archive_zip'), ALERT_INDEX, $appParameter->toString(true));
    }

    $title   = lng('file_unzip.title_page');
    AppAlert::setID(ALERT_FILE_UNZIP);
    require_once('incfiles' . SP . 'header.php');

    $forms = [
        'path'        => AppDirectory::getInstance()->getDirectory(),
        'exists_func' => EXISTS_FUNC_OVERRIDE
    ];

    if (isset($_POST['browser'])) {
        $forms['path']        = $_POST['path'];
        $forms['exists_func'] = intval($_POST['exists_func']);

        if ($forms['exists_func'] !== EXISTS_FUNC_OVERRIDE && $forms['exists_func'] !== EXISTS_FUNC_SKIP) {
            AppAlert::danger(lng('file_unzip.alert.exists_func_not_validate'));
        } else {
            $appFileUnzip->setSession(AppDirectory::getInstance()->getDirectory(), AppDirectory::getInstance()->getName(), $forms['exists_func']);

            $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
            $appParameter->toString(true);

            Request::redirect('index.php' . $appParameter->toString());
        }
    } else if (isset($_POST['unzip'])) {
        $forms['path']        = $_POST['path'];
        $forms['exists_func'] = intval($_POST['exists_func']);

        $appFileUnzip->clearSession();

        if (empty($forms['path'])) {
            AppAlert::danger(lng('file_unzip.alert.not_input_path_unzip'));
        } else if ($forms['exists_func'] !== EXISTS_FUNC_OVERRIDE && $forms['exists_func'] !== EXISTS_FUNC_SKIP) {
            AppAlert::danger(lng('file_unzip.alert.exists_func_not_validate'));
        } else if (FileInfo::isTypeDirectory(FileInfo::filterPaths($forms['path'])) == false) {
            AppAlert::danger(lng('file_unzip.alert.path_unzip_not_exists'));
        } else if (FileInfo::permissionDenyPath($forms['path'])) {
            AppAlert::danger(lng('file_unzip.alert.not_unzip_file_to_directory_app'));
        } else {
        	$pclZip = new PclZip(FileInfo::filterPaths(AppDirectory::getInstance()->getDirectoryAndName()));
            $isHasFileAppPermission = false;
            $isHasFileSkip          = false;

            function callbackPreExtract($event, $header) {
                global $forms,
                	   $isHasFileAppPermission,
                	   $isHasFileSkip;

                $filePath = FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory() . SP . $header['stored_filename']);

                if (FileInfo::permissionDenyPath($filePath)) {
                    $isHasFileAppPermission = true;
                } else if ($forms['exists_func'] !== EXISTS_FUNC_SKIP) {
                    return 1;
                } else if ($forms['exists_func'] === EXISTS_FUNC_SKIP) {
                	$isHasFileSkip = true;

                	if ($header['folder'] == true && FileInfo::isTypeDirectory($filePath))
                		return 0;
                	else if ($header['folder'] == false && FileInfo::isTypeFile($filePath))
                		return 0;
                	else
                		$isHasFileSkip = false;

                	return 1;
                }

                return 0;
            };

            if ($pclZip->extract(PCLZIP_OPT_PATH, FileInfo::filterPaths($forms['path']), PCLZIP_CB_PRE_EXTRACT, 'callbackPreExtract') != false) {
                if ($isHasFileAppPermission)
                    AppAlert::warning(lng('file_unzip.alert.file_zip_has_file_app'), ALERT_INDEX);

                if ($isHasFileSkip)
                    AppAlert::info(lng('file_unzip.alert.file_zip_has_file_skip'), ALERT_INDEX);

                $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                $appParameter->set(AppDirectory::PARAMETER_DIRECTORY_URL, FileInfo::filterPaths($forms['path']), true);
                $appParameter->toString(true);

                AppAlert::success(lng('file_unzip.alert.unzip_file_success', 'filename', AppDirectory::getInstance()->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString());
            } else {
                AppAlert::danger(lng('file_unzip.alert.unzip_file_failed', 'filename', AppDirectory::getInstance()->getName(), 'error', $pclZip->errorinfo(true)));
            }
        }
    }

    $idAlert = null;

    if (isset($_SERVER['HTTP_REFERER']) && strpos(strtolower($_SERVER['HTTP_REFERER']), 'index.php') !== false)
        $idAlert = ALERT_INDEX;

    if ($appFileUnzip->isSession()) {
        $isChooseDirectoryPathFailed = true;

        if (FileInfo::isTypeDirectory($appFileUnzip->getPath()) == false) {
            AppAlert::danger(lng('file_unzip.alert.path_unzip_not_exists'), $idAlert);
        } else {
            $forms['path']               = $appFileUnzip->getPath();
            $forms['exists_func']        = $appFileUnzip->getExistsFunc();
            $isChooseDirectoryPathFailed = false;

            AppAlert::success(lng('file_unzip.alert.directory_path_choose_is_validate', 'path', $appFileUnzip->getPath()));
        }

        if ($isChooseDirectoryPathFailed)
            $appFileUnzip->clearSession();

        if ($isChooseDirectoryPathFailed && $idAlert === ALERT_INDEX) {
            $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
            $appParameter->toString(true);

            Request::redirect('index.php' . $appParameter->toString());
        }
    }
?>

    <?php AppAlert::display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('file_unzip.title_page'); ?>: <?php echo AppDirectory::getInstance()->getName(); ?></span>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/file_unzip.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('file_unzip.form.input.path_unzip'); ?></span>
                    <input type="text" name="path" value="<?php echo htmlspecialchars($forms['path']); ?>" placeholder="<?php echo lng('file_unzip.form.placeholder.input_path_unzip'); ?>"/>
                </li>
                <li class="radio-choose">
                    <span><?php echo lng('file_unzip.form.input.if_has_entry_is_exists'); ?></span>
                    <ul class="radio-choose-tab">
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_OVERRIDE; ?>" id="exists_func_override"<?php if ($forms['exists_func'] === EXISTS_FUNC_OVERRIDE) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_override">
                                <span><?php echo lng('file_unzip.form.input.exists_func_override'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_SKIP; ?>" id="exists_func_skip"<?php if ($forms['exists_func'] === EXISTS_FUNC_SKIP) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_skip">
                                <span><?php echo lng('file_unzip.form.input.exists_func_skip'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>
                <li class="button">
                    <button type="submit" name="unzip" id="button-save-on-javascript">
                        <span><?php echo lng('file_unzip.form.button.unzip'); ?></span>
                    </button>
                    <!--<button type="submit" name="browser">
                        <span><?php echo lng('file_unzip.form.button.browser'); ?></span>
                    </button>-->
                    <?php if ($idAlert == ALERT_INDEX && $appFileUnzip->isSession()) { ?>
                        <?php $appParameter->set(AppDirectory::PARAMETER_DIRECTORY_URL, FileInfo::filterPaths($appFileUnzip->getPath()), true); ?>
                        <?php $appParameter->toString(true); ?>
                    <?php } ?>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('file_unzip.form.button.cancel'); ?></span>
                    </a>
                    <?php if ($idAlert == ALERT_INDEX && $appFileUnzip->isSession()) { ?>
                        <?php $appParameter->set(AppDirectory::PARAMETER_DIRECTORY_URL, AppDirectory::getInstance()->getDirectory(), true); ?>
                        <?php $appParameter->toString(true); ?>
                    <?php } ?>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
        <li>
            <a href="file_info.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('file_info.menu_action.info'); ?></span>
            </a>
        </li>
        <li>
            <a href="file_download.php<?php echo $appParameter->toString(); ?>" class="not-autoload">
                <span class="icomoon icon-download"></span>
                <span><?php echo lng('file_info.menu_action.download'); ?></span>
            </a>
        </li>

        <?php if ($fileMime->isFormatArchiveZip()) { ?>
            <li>
                <a href="file_viewzip.php<?php echo $appParameter->toString(); ?>">
                    <span class="icomoon icon-archive"></span>
                    <span><?php echo lng('file_info.menu_action.viewzip'); ?></span>
                </a>
            </li>
        <?php } ?>
        <li>
            <a href="file_rename.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-edit"></span>
                <span><?php echo lng('file_info.menu_action.rename'); ?></span>
            </a>
        </li>
        <li>
            <a href="file_copy.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-copy"></span>
                <span><?php echo lng('file_info.menu_action.copy'); ?></span>
            </a>
        </li>
        <li>
            <a href="file_chmod.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-key"></span>
                <span><?php echo lng('file_info.menu_action.chmod'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once('incfiles' . SP . 'footer.php'); ?>