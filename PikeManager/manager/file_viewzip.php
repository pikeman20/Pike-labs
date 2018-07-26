<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\AppPaging;
    use Librarys\App\Config\AppConfig;
    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\Zip\PclZip;
    use Librarys\Zip\ZipList;

    define('LOADED',             1);
    define('PARAMETER_ZIP_PATH', 'directory_zip');
    define('PARAMETER_ZIP_PAGE', 'page_zip');

    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if (AppDirectory::getInstance()->isFileSeparatorNameExists() == false)
        AppAlert::danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if (AppDirectory::getInstance()->isPermissionDenyPath())
        AppAlert::danger(lng('home.alert.path_not_permission', 'path', AppDirectory::getInstance()->getDirectoryAndName()), ALERT_INDEX, env('app.http.host'));

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
        AppAlert::danger(lng('file_viewzip.alert.file_is_not_format_archive_zip'), ALERT_INDEX, $appParameter->toString(true));
    }

    $title   = lng('file_viewzip.title_page');
    AppAlert::setID(ALERT_FILE_VIEWZIP);
    require_once('incfiles' . SP . 'header.php');

    $zipList = new ZipList(AppDirectory::getInstance()->getDirectoryAndName());

    if (isset($_GET[PARAMETER_ZIP_PATH]) && empty($_GET[PARAMETER_ZIP_PATH]) == false)
        $zipList->setPathContentInZip(AppDirectory::rawDecode($_GET[PARAMETER_ZIP_PATH]));

    if (isset($_GET[PARAMETER_ZIP_PAGE]) && empty($_GET[PARAMETER_ZIP_PAGE]) == false)
        $zipList->setPageCurrent($_GET[PARAMETER_ZIP_PAGE]);

    $zipList->putEntryAppLocationPath($appLocationPath, AppDirectory::getInstance()->getName());
    $zipList->execute();

    $zipListArrayEntrys     = $zipList->getLists();
    $zipListCountArrayEntry = $zipList->getCountLists();

    $appParameter->add(PARAMETER_ZIP_PATH, $zipList->getDirectoryZipOrigin(), $zipList->getDirectoryZipOrigin() != null);
    $appParameter->toString(true);

    $appPaging = new AppPaging(
        'file_viewzip.php' . $appParameter->toString(),
        'file_viewzip.php' . $appParameter->toString() . '&' . PARAMETER_ZIP_PAGE . '='
    );

    $appParameter->remove(PARAMETER_ZIP_PATH);
    $appParameter->toString(true);

    $bufferBack = null;

    if ($zipList->getDirectoryZipOrigin() != null && preg_replace('|[a-zA-Z]+:|', '', separator($zipList->getDirectoryZipOrigin(), $zipList->getSeparator())) != $zipList->getSeparator()) {
        $backPath      = strrchr($zipList->getDirectoryZipOrigin(), $zipList->getSeparator());
        $backDirectory = $backPath;

        if ($backPath !== false) {
            $backPath = substr($zipList->getDirectoryZipOrigin(), 0, strlen($zipList->getDirectoryZipOrigin()) - strlen($backPath));
            $backPath = 'file_viewzip.php' . $appParameter->toString() . '&' . PARAMETER_ZIP_PATH . '=' . AppDirectory::rawEncode($backPath);

            if (strpos($backDirectory, $zipList->getSeparator()) !== false)
                $backDirectory = str_replace($zipList->getSeparator(), null, $backDirectory);
        } else {
            $backPath      = 'file_viewzip.php' . $appParameter->toString();
            $backDirectory = $zipList->getDirectoryZipOrigin();
        }

        $bufferBack .= '<li class="back">';
            $bufferBack .= '<a href="' . $backPath . '">';
                $bufferBack .= '<span class="icomoon icon-folder-open"></span>';
                $bufferBack .= '<strong>' . $backDirectory . '</strong>';
            $bufferBack .= '</a>';
        $bufferBack .= '</li>';
    } else {
        $bufferBack .= '<li class="back">';
            $bufferBack .= '<a href="index.php?directory=' . AppDirectory::getInstance()->getDirectoryEncode() . '">';
                $bufferBack .= '<span class="icomoon icon-folder-open"></span>';
                $bufferBack .= '<strong>' . AppDirectory::getInstance()->getName() . '</strong>';
            $bufferBack .= '</a>';
        $bufferBack .= '</li>';
    }
?>

    <?php AppAlert::display(); ?>
    <?php $appLocationPath->display(); ?>
    <?php $classEndListOptions = $zipList->isPaging() ? ' is-end-list-option' : null; ?>

    <ul class="list<?php if (AppConfig::getInstance()->get('enable_disable.list_file_double') == false) { ?> not-double<?php } ?>">
        <?php if ($zipListArrayEntrys == null || $zipListCountArrayEntry <= 0) { ?>
            <li class="empty">
                <span class="icomoon icon-folder-o"></span>
                <span><?php echo lng('home.directory_empty'); ?></span>
            </li>
        <?php } else { ?>
            <?php echo $bufferBack; ?>

            <?php for ($i = $zipList->getPageBeginLoop(); $i < $zipList->getPageEndLoop(); ++$i) { ?>
                <?php $entry = $zipListArrayEntrys[$i]; ?>

                <?php if ($entry['is_dir']) { ?>
                    <li class="directory has-first-not-entry<?php echo $classEndListOptions; ?>">
                        <div class="icon">
                            <a href="#">
                                <span class="icomoon icon-folder"></span>
                            </a>
                        </div>
                        <a href="file_viewzip.php<?php echo $appParameter->toString(); ?>&<?php echo PARAMETER_ZIP_PATH; ?>=<?php echo AppDirectory::rawEncode($entry['path']); ?>" class="file-name">
                            <span><?php echo $entry['name']; ?></span>
                        </a>
                    </li>
                <?php } else { ?>
                    <li class="file has-first-not-entry <?php echo $classEndListOptions; ?>">
                        <div class="icon">
                            <span class="icomoon <?php echo $entry['icon']; ?>"></span>
                        </div>
                        <a href="" class="file-name">
                            <span><?php echo $entry['name']; ?></span>
                        </a>
                        <div class="chmod-size">
                            <span class="size"><?php echo FileInfo::sizeToString($entry['size']); ?></span>
                        </div>
                    </li>
                <?php } ?>
            <?php } ?>

            <?php if ($zipList->isPaging()) { ?>
                <li class="end-list-option">
                    <div class="paging">
                        <?php echo $appPaging->display($zipList->getPageCurrent(), $zipList->getPageTotal()); ?>
                    </div>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>

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
                <a href="file_unzip.php<?php echo $appParameter->toString(); ?>">
                    <span class="icomoon icon-archive"></span>
                    <span><?php echo lng('file_info.menu_action.unzip'); ?></span>
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