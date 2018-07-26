<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\Config\AppConfig;
    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;

    define('LOADED', 1);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if (AppDirectory::getInstance()->isFileExistsDirectorySeparatorName() == false)
        AppAlert::danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if (AppDirectory::getInstance()->isPermissionDenyPath(AppDirectory::getInstance()->getDirectory()))
        AppAlert::danger(lng('home.alert.path_not_permission', 'path', AppDirectory::getInstance()->getDirectoryAndName()), ALERT_INDEX, env('app.http.host'));

    $appLocationPath = new AppLocationPath('index.php');
    $appLocationPath->setIsPrintLastEntry(true);
    $appLocationPath->setIsLinkLastEntry(true);

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, AppDirectory::getInstance()->getDirectoryAndNameEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      AppDirectory::getInstance()->getPage(),                   AppDirectory::getInstance()->getPage() > 1);

    if (isset($_GET[AppDirectory::PARAMETER_LIST_URL]))
        $backParameter = $appParameter->toString();

    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, AppDirectory::getInstance()->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_NAME_URL,      AppDirectory::getInstance()->getNameEncode(),      true);

    if (isset($_GET[AppDirectory::PARAMETER_LIST_URL])) {
        $appParameter->add(AppDirectory::PARAMETER_LIST_URL, 1, true)->toString(true);
    } else {
        $backParameter = $appParameter->toString(true);
    }

    $fileInfo    = new FileInfo(AppDirectory::getInstance()->getDirectory() . SP . AppDirectory::getInstance()->getName());
    $fileMime    = new FileMime($fileInfo);
    $isDirectory = $fileInfo->isDirectory();

    if ($isDirectory)
        $title = lng('file_chmod.title_page_directory');
    else
        $title = lng('file_chmod.title_page_file');

    AppAlert::setID(ALERT_FILE_CHMOD);
    require_once('incfiles' . SP . 'header.php');

    $forms = [
        'chmod' => FileInfo::chmod(FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory() . SP . AppDirectory::getInstance()->getName()))
    ];

    if (isset($_POST['chmod'])) {
        $forms['chmod'] = addslashes($_POST['chmod_permission']);

        if (empty($forms['chmod'])) {
            if ($isDirectory)
                AppAlert::danger(lng('file_chmod.alert.not_input_chmod_permission_directory'));
            else
                AppAlert::danger(lng('file_chmod.alert.not_input_chmod_permission_file'));
        } else {
            if (FileInfo::chmod(FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory() . SP . AppDirectory::getInstance()->getName()), intval($forms['chmod'], 8)) == false) {
                if ($isDirectory)
                    AppAlert::danger(lng('file_chmod.alert.chmod_permission_directory_failed', 'filename', AppDirectory::getInstance()->getName()));
                else
                    AppAlert::danger(lng('file_chmod.alert.chmod_permission_file_failed', 'filename', AppDirectory::getInstance()->getName()));
            } else {
                $idAlert = null;
                $urlGoto = null;

                if (AppConfig::getInstance()->get('auto_redirect.file_chmod')) {
                    $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                    $appParameter->toString(true);

                    $idAlert = ALERT_INDEX;
                    $urlGoto = 'index.php' . $appParameter->toString();
                }

                if ($isDirectory)
                    AppAlert::success(lng('file_chmod.alert.chmod_permission_directory_success', 'filename', AppDirectory::getInstance()->getName()), $idAlert, $urlGoto);
                else
                    AppAlert::success(lng('file_chmod.alert.chmod_permission_file_success', 'filename', AppDirectory::getInstance()->getName()), $idAlert, $urlGoto);
            }
        }

        $forms['chmod'] = stripslashes(htmlspecialchars($forms['chmod']));
    }
?>

    <?php AppAlert::display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <?php if ($isDirectory) { ?>
                <span><?php echo lng('file_chmod.title_page_directory'); ?>: <?php echo AppDirectory::getInstance()->getName(); ?></span>
            <?php } else { ?>
                <span><?php echo lng('file_chmod.title_page_file'); ?>: <?php echo AppDirectory::getInstance()->getName(); ?></span>
            <?php } ?>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/file_chmod.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <?php if ($isDirectory) { ?>
                        <span><?php echo lng('file_chmod.form.input.chmod_directory'); ?></span>
                    <?php } else { ?>
                        <span><?php echo lng('file_chmod.form.input.chmod_file'); ?></span>
                    <?php } ?>
                    <input type="number" name="chmod_permission" value="<?php echo $forms['chmod']; ?>" placeholder="<?php if ($isDirectory) echo lng('file_chmod.form.placeholder.input_chmod_directory'); else echo lng('file_chmod.form.placeholder.input_chmod_file'); ?>" id="form-input-chmod" max="777"/>
                </li>
                <li class="input-chmod">
                    <ul id="form-input-chmod-checkbox">
                        <li>
                        	<span class="icomoon icon-config"></span>
							<span><?php echo lng('file_chmod.form.input.chmod_label_system'); ?></span>
						</li>
                        <li>
							<span class="icomoon icon-users"></span>
							<span><?php echo lng('file_chmod.form.input.chmod_label_group'); ?></span>
						</li>
                        <li>
                        	<span class="icomoon icon-user"></span>
							<span><?php echo lng('file_chmod.form.input.chmod_label_user'); ?></span>
						</li>

                        <?php $group = [ "system", "group", "user" ]; ?>
                        <?php for ($i = 0; $i < 3; ++$i) { ?>
                            <li>
                                <input type="checkbox" name="chmod_read_<?php echo $group[$i]; ?>" value="4" id="chmod-read-<?php echo $group[$i]; ?>"/>
                                <label for="chmod-read-<?php echo $group[$i]; ?>">
                                	<span class="icomoon icon-view"></span>
                                    <span><?php echo lng('file_chmod.form.input.chmod_value_read'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                        <?php for ($i = 0; $i < 3; ++$i) { ?>
                            <li>
                                <input type="checkbox" name="chmod_write_<?php echo $group[$i]; ?>" value="2" id="chmod-write-<?php echo $group[$i]; ?>"/>
                                <label for="chmod-write-<?php echo $group[$i]; ?>">
                                	<span class="icomoon icon-pencil"></span>
                                    <span><?php echo lng('file_chmod.form.input.chmod_value_write'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                        <?php for ($i = 0; $i < 3; ++$i) { ?>
                            <li>
                                <input type="checkbox" name="chmod_execute_<?php echo $group[$i]; ?>" value="1" id="chmod-execute-<?php echo $group[$i]; ?>"/>
                                <label for="chmod-execute-<?php echo $group[$i]; ?>">
                                	<span class="icomoon icon-terminal"></span>
                                    <span><?php echo lng('file_chmod.form.input.chmod_value_execute'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
                <li class="button">
                    <button type="submit" name="chmod" id="button-save-on-javascript">
                        <span><?php echo lng('file_chmod.form.button.chmod'); ?></span>
                    </button>
                    <a href="index.php<?php echo $backParameter; ?>">
                        <span><?php echo lng('file_chmod.form.button.cancel'); ?></span>
                    </a>
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
        <?php if ($fileMime->isFormatTextEdit()) { ?>
            <?php if ($fileMime->isFormatTextAsEdit()) { ?>
                <li>
                    <a href="file_edit_text.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-edit"></span>
                        <span><?php echo lng('file_info.menu_action.edit_as_text'); ?></span>
                    </a>
                </li>
            <?php } else { ?>
                <li>
                    <a href="file_edit_text.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-edit"></span>
                        <span><?php echo lng('file_info.menu_action.edit_text'); ?></span>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
        <?php if ($isDirectory == false) { ?>
            <li>
                <a href="file_download.php<?php echo $appParameter->toString(); ?>" class="not-autoload">
                    <span class="icomoon icon-download"></span>
                    <span><?php echo lng('file_info.menu_action.download'); ?></span>
                </a>
            </li>

            <?php if ($fileMime->isFormatArchiveZip()) { ?>
                <li>
                    <a href="file_unzip.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-archive"></span>
                        <span><?php echo lng('file_info.menu_action.unzip'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="file_viewzip.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-archive"></span>
                        <span><?php echo lng('file_info.menu_action.viewzip'); ?></span>
                    </a>
                </li>
            <?php } ?>
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
            <a href="file_delete.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('file_info.menu_action.delete'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once('incfiles' . SP . 'footer.php'); ?>
