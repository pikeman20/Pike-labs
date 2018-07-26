<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;

    define('LOADED', 1);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if (AppDirectory::getInstance()->isFileExistsDirectorySeparatorName() == false)
        AppAlert::danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if (AppDirectory::getInstance()->isPermissionDenyPath())
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
        $title = lng('file_delete.title_page_directory');
    else
        $title = lng('file_delete.title_page_file');

    AppAlert::setID(ALERT_FILE_DELETE);
    require_once('incfiles' . SP . 'header.php');

    if (isset($_POST['delete'])) {
        if ($isDirectory) {
            $isHasFileAppPermission = false;

            if (FileInfo::rrmdir(FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory() . SP . AppDirectory::getInstance()->getName()), null, $isHasFileAppPermission) && $isHasFileAppPermission == false) {
                $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                AppAlert::success(lng('file_delete.alert.delete_directory_success', 'filename', AppDirectory::getInstance()->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString(true));
            } else {
                if ($isHasFileAppPermission) {
                    $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                    $appParameter->toString(true);

                    AppAlert::warning(lng('file_delete.alert.not_delete_file_app', 'filename', AppDirectory::getInstance()->getName()), ALERT_INDEX);
                    AppAlert::success(lng('file_delete.alert.delete_entry_in_directory_success', 'filename', AppDirectory::getInstance()->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString());
                } else {
                    AppAlert::danger(lng('file_delete.alert.delete_directory_failed', 'filename', AppDirectory::getInstance()->getName()));
                }
            }
        } else {
            if (FileInfo::unlink(FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory() . SP . AppDirectory::getInstance()->getName()))) {
                $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                AppAlert::success(lng('file_delete.alert.delete_file_success', 'filename', AppDirectory::getInstance()->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString(true));
            } else {
                AppAlert::danger(lng('file_delete.alert.delete_file_failed', 'filename', AppDirectory::getInstance()->getName()));
            }
        }
    }
?>

    <?php AppAlert::display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <?php if ($isDirectory) { ?>
                <span><?php echo lng('file_delete.title_page_directory'); ?>: <?php echo AppDirectory::getInstance()->getName(); ?></span>
            <?php } else { ?>
                <span><?php echo lng('file_delete.title_page_file'); ?>: <?php echo AppDirectory::getInstance()->getName(); ?></span>
            <?php } ?>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/file_delete.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <li class="accept">
                    <?php if ($isDirectory) { ?>
                        <span><?php echo lng('file_delete.form.accept_delete_directory', 'filename', AppDirectory::getInstance()->getName()); ?></span>
                    <?php } else { ?>
                        <span><?php echo lng('file_delete.form.accept_delete_file', 'filename', AppDirectory::getInstance()->getName()); ?></span>
                    <?php } ?>
                </li>
                <li class="button">
                    <button type="submit" name="delete" id="button-save-on-javascript">
                        <span><?php echo lng('file_delete.form.button.delete'); ?></span>
                    </button>
                    <a href="index.php<?php echo $backParameter; ?>">
                        <span><?php echo lng('file_delete.form.button.cancel'); ?></span>
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
            <a href="file_chmod.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-key"></span>
                <span><?php echo lng('file_info.menu_action.chmod'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once('incfiles' . SP . 'footer.php'); ?>
