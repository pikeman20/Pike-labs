<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppPaging;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\AppFileCopy;
    use Librarys\App\AppAlert;
    use Librarys\App\AppJson;
    use Librarys\App\Config\AppConfig;
    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\Http\Request;

    define('LOADED', 1);
    define('INDEX',  1);

    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');
    requireDefine('file_action');

    if (Request::isDesktop(false) && Request::isMethodGet()) {
        if (isset($_GET) && count($_GET) > 0)
            Request::redirect(env('app.http.host'));

        require_once('desktop.php');
        exit(255);
    }

    $title = lng('home.title_page_root');
    AppAlert::setID(ALERT_INDEX);

    require_once('incfiles' . SP . 'header.php');

    $handler              = null;
    $isPermissionDenyPath = AppDirectory::getInstance()->isPermissionDenyPath();

    if ($isPermissionDenyPath)
        AppAlert::danger(lng('home.alert.path_not_permission', 'path', AppDirectory::getInstance()->getDirectory()));

    if ($isPermissionDenyPath || FileInfo::isReadable(AppDirectory::getInstance()->getDirectory()) == false || FileInfo::isTypeDirectory(AppDirectory::getInstance()->getDirectory()) == false) {
        AppDirectory::getInstance()->setDirectory(env('SERVER.DOCUMENT_ROOT'));
        $handler = FileInfo::scanDirectory(AppDirectory::getInstance()->getDirectory());

        if ($isPermissionDenyPath == false)
            AppAlert::danger(lng('home.alert.path_not_exists'));
    } else {
        $handler = FileInfo::scanDirectory(AppDirectory::getInstance()->getDirectory());
    }

    if ($handler === false || $handler == null) {
        AppDirectory::getInstance()->setDirectory(env('SERVER.DOCUMENT_ROOT'));
        $handler = FileInfo::scanDirectory(AppDirectory::getInstance()->getDirectory());
        AppAlert::danger(lng('home.alert.path_not_receiver_list'));
    }

    if (is_array($handler) == false)
        $handler = array();

    $handlerCount = count($handler);
    $handlerList  = array();

    $arrayFolder = array();
    $arrayFile   = array();

    $developmentMode = AppConfig::getInstance()->getSystem('enable_disable.development');
    $isPermission    = FileInfo::permissionDenyPath(AppDirectory::getInstance()->getDirectory(), false, true);

    foreach ($handler AS $entry) {
        if ($entry != '.' && $entry != '..') {
            $isEntryApp = false;

            if ($isPermission)
                $isEntryApp = true;
            else if (strcmp($entry, env('application.directory')) === 0 && AppDirectory::getInstance()->isAccessParentPath())
                $isEntryApp = true;

            if ($developmentMode || $isEntryApp == false) {
                if (FileInfo::isTypeDirectory(AppDirectory::getInstance()->getDirectory() . SP . $entry))
                    $arrayFolder[$entry] = [ 'name' => $entry, 'is_directory' => true, 'is_app'  => $isEntryApp ];
                else
                    $arrayFile[$entry]   = [ 'name' => $entry, 'is_directory' => false, 'is_app' => $isEntryApp ];
            }
        }
    }

    $takeInfo = function(&$array) {
        $entrySize     = 0;
        $entryPath     = FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory() . SP . $array['name']);
        $chmodPerms    = FileInfo::getChmodPermission($entryPath);
        $entryInfo     = new FileInfo($entryPath);
        $entryMime     = null;
        $entryIcon     = null;
        $entryEditHref = null;
        $entryIsEdit   = false;

        if ($array['is_directory'] == false) {
            $entrySize = FileInfo::fileSize($entryPath);
            $entryMime = new FileMime($entryInfo);

            $entryIcon     = null;
            $entryIsEdit   = false;
            $entryEditHref = 'file_edit_text.php';

            if ($entryMime->isFormatText()) {
                $entryIcon   = 'icon-file-text';
                $entryIsEdit = true;
            } else if ($entryMime->isFormatCode()) {
                $entryIcon   = 'icon-file-code';
                $entryIsEdit = true;
            } else if ($entryMime->isFormatArchive()) {
                $entryIcon   = 'icon-file-archive';
                $entryIsEdit = false;

                if ($entryMime->isFormatArchiveZip()) {
                    $entryIsEdit   = true;
                    $entryEditHref = 'file_unzip.php';
                }
            } else if ($entryMime->isFormatAudio()) {
                $entryIcon   = 'icon-file-audio';
                $entryIsEdit = false;
            } else if ($entryMime->isFormatVideo()) {
                $entryIcon   = 'icon-file-video';
                $entryIsEdit = false;
            } else if ($entryMime->isFormatDocument()) {
                $entryIcon   = 'icon-file-document';
                $entryIsEdit = false;
            } else if ($entryMime->isFormatImage()) {
                $entryIcon   = 'icon-file-image';
                $entryIsEdit = false;
            } else if ($entryMime->isFormatSource()) {
                $entryIcon   = 'icon-file-code';
                $entryIsEdit = true;
            } else {
                $entryIcon   = 'icon-file';
                $entryIsEdit = true;
            }
        }

        $array['size']      = $entrySize;
        $array['path']      = $entryPath;
        $array['icon']      = $entryIcon;
        $array['is_edit']   = $entryIsEdit;
        $array['edit_href'] = $entryEditHref;
        $array['format']    = $entryInfo->getFileExt();
        $array['perms']     = $chmodPerms;

        return $array;
    };

    if (count($arrayFolder) > 0) {
        ksort($arrayFolder);

        foreach ($arrayFolder AS $entry)
            $handlerList[] = $takeInfo($entry);
    }

    if (count($arrayFile) > 0) {
        ksort($arrayFile);

        foreach ($arrayFile AS $entry)
            $handlerList[] = $takeInfo($entry);
    }

    if (Request::isDesktop()) {
        AppJson::getInstance()->setResponseData([
            'list' => $handlerList,
            'path' => AppDirectory::getInstance()->getDirectory()
        ]);

        AppJson::getInstance()->toResult();
    }

    $handlerIsOdd = false;
    $handlerCount = count($handlerList);
    $handlerPage  = array(
        'current'       => AppDirectory::getInstance()->getPage(),
        'begin'         => 0,
        'end'           => $handlerCount,
        'total'         => 0,
        'entry_on_page' => $handlerCount,
        'list_max'      => AppConfig::getInstance()->get('paging.file_home_list')
    );

    if ($handlerCount > 0 && $handlerPage['list_max'] > 0 && $handlerCount > $handlerPage['list_max']) {
        $handlerPage['total'] = ceil($handlerCount / $handlerPage['list_max']);

        if ($handlerPage['total'] <= 0 || $handlerPage['current'] > $handlerPage['total'])
            $handlerPage['current'] = 1;

        $handlerPage['begin'] = ($handlerPage['current'] * $handlerPage['list_max']) - $handlerPage['list_max'];
        $handlerPage['end']   = ($handlerPage['begin'] + $handlerPage['list_max']);

        if ($handlerPage['end'] > $handlerCount)
            $handlerPage['end'] = $handlerCount;

        if (($handlerPage['end'] - $handlerPage['begin']) % 2 !== 0)
            $handlerIsOdd = true;

        $handlerPage['entry_on_page'] = $handlerPage['end'] - $handlerPage['begin'];
    }

    if ($handlerPage['entry_on_page'] % 2 !== 0)
        $handlerIsOdd = true;

    $bufferBack       = null;
    $isRoot           = true;
    $currentDirectory = null;

    if (preg_replace('|[a-zA-Z]+:|', '', FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory())) != SP) {
        $backPath      = strrchr(AppDirectory::getInstance()->getDirectory(), SP);
        $backDirectory = $backPath;
        $isRoot        = false;

        if ($backPath !== false) {
            $backPath = substr(AppDirectory::getInstance()->getDirectory(), 0, strlen(AppDirectory::getInstance()->getDirectory()) - strlen($backPath));
            $backPath = 'index.php?' . AppDirectory::PARAMETER_DIRECTORY_URL . '=' . AppDirectory::rawEncode($backPath);

            if (strpos($backDirectory, SP) !== false)
                $backDirectory = str_replace(SP, null, $backDirectory);
        } else {
            $backPath      = 'index.php';
            $backDirectory = AppDirectory::getInstance()->getDirectory();
        }

        $bufferBack .= '<li class="back">';
            $bufferBack .= '<a href="' . $backPath . '" class="file-name">';
                $bufferBack .= '<span class="icomoon icon-folder-open"></span>';

                if ($isPermission)
                    $bufferBack .= '<strong class="file-name-of-app">' . $backDirectory . '</strong>';
                else
                    $bufferBack .= '<strong>' . $backDirectory . '</strong>';

            $bufferBack .= '</a>';
        $bufferBack .= '</li>';

        $currentDirectory = $backDirectory;
    }

    $appLocationPath = new AppLocationPath('index.php');
    $pagePaging      = new AppPaging(
        'index.php?' . AppDirectory::PARAMETER_DIRECTORY_URL . '=' . AppDirectory::getInstance()->getDirectoryEncode(),

        'index.php?' . AppDirectory::PARAMETER_DIRECTORY_URL . '=' . AppDirectory::getInstance()->getDirectoryEncode() .
                 '&' . AppDirectory::PARAMETER_PAGE_URL      . '='
    );

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, AppDirectory::getInstance()->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      $handlerPage['current'],            $handlerPage['current'] > 1);

    if (isset($_GET[AppDirectory::PARAMETER_LIST_URL]))
        $appParameter->add(AppDirectory::PARAMETER_LIST_URL, 1, true);

    $appFileCopy = new AppFileCopy();

    if ($appFileCopy->isSession()) {
        $isDirectoryCopy         = FileInfo::isTypeDirectory(FileInfo::filterPaths($appFileCopy->getDirectory() . SP . $appFileCopy->getName()));
        $isDirectoryCurrentEqual = false;

        if (isset($_GET['cancel_copy_move'])) {
            if (addslashes($_GET['cancel_copy_move']) === md5(date('H', time()))) {
                $appFileCopy->clearSession();

                if ($isDirectoryCopy) {
                    if ($appFileCopy->isMove() == false)
                        AppAlert::setLangMsg('file_copy.alert.cancel_copy_directory_success', 'filename', $appFileCopy->getName());
                    else
                        AppAlert::setLangMsg('file_copy.alert.cancel_move_directory_success', 'filename', $appFileCopy->getName());
                } else {
                    if ($appFileCopy->isMove() == false)
                        AppAlert::setLangMsg('file_copy.alert.cancel_copy_file_success', 'filename', $appFileCopy->getName());
                    else
                        AppAlert::setLangMsg('file_copy.alert.cancel_move_file_success', 'filename', $appFileCopy->getName());
                }

                AppAlert::success(null, ALERT_INDEX, 'index.php' . $appParameter->toString());
            } else {
                if ($isDirectoryCopy) {
                    if ($appFileCopy->isMove() == false)
                        AppAlert::setLangMsg('file_copy.alert.cancel_copy_directory_failed', 'filename', $appFileCopy->getName());
                    else
                        AppAlert::setLangMsg('file_copy.alert.cancel_move_directory_failed', 'filename', $appFileCopy->getName());
                } else {
                    if ($appFileCopy->isMove() == false)
                        AppAlert::setLangMsg('file_copy.alert.cancel_copy_file_failed', 'filename', $appFileCopy->getName());
                    else
                        AppAlert::setLangMsg('file_copy.alert.cancel_move_file_failed', 'filename', $appFileCopy->getName());
                }

                AppAlert::danger(null);
            }
        }

        $appFileCopyHrefParameter = new AppParameter();
        $appFileCopyHrefParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, $appFileCopy->getDirectory(), true);
        $appFileCopyHrefParameter->add(AppDirectory::PARAMETER_NAME_URL,      $appFileCopy->getName(),      true);

        if (isset($_GET[AppDirectory::PARAMETER_LIST_URL]))
            $appFileCopyHrefParameter->add(AppDirectory::PARAMETER_LIST_URL, 1, true);

        $appFileCopyHref = 'file_copy.php' . $appFileCopyHrefParameter->toString(true);

        if (FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory() . SP . $appFileCopy->getName()) == FileInfo::filterPaths($appFileCopy->getDirectory() . SP . $appFileCopy->getName())) {
            if ($appFileCopy->isMove() == false)
                AppAlert::danger(lng('file_copy.alert.path_copy_is_equal_path_current'));
            else
                AppAlert::danger(lng('file_copy.alert.path_move_is_equal_path_current'));

            $isDirectoryCurrentEqual = true;
        }

        $appParameter->add('cancel_copy_move', md5(date('H', time())), true);
        $appFileCopyHrefCancel = 'index.php' . $appParameter->toString(true);

        if ($appFileCopy->isMove() == false) {
            if ($isDirectoryCurrentEqual) {
                if ($isDirectoryCopy)
                    AppAlert::info(lng(
                        'file_copy.alert.choose_directory_path_for_copy_directory_cancel',
                        'filename', $appFileCopy->getName(),
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
                else
                    AppAlert::info(lng(
                        'file_copy.alert.choose_directory_path_for_copy_file_cancel',
                        'filename', $appFileCopy->getName(),
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
            } else {
                if ($isDirectoryCopy)
                    AppAlert::info(lng(
                        'file_copy.alert.choose_directory_path_for_copy_directory_href',
                        'filename', $appFileCopy->getName(),
                        'href', $appFileCopyHref,
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
                else
                    AppAlert::info(lng('file_copy.alert.choose_directory_path_for_copy_file_href',
                        'filename', $appFileCopy->getName(),
                        'href', $appFileCopyHref,
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
            }
        } else {
            if ($isDirectoryCurrentEqual) {
                if ($isDirectoryCopy)
                    AppAlert::info(lng('file_copy.alert.choose_directory_path_for_move_directory_href',
                        'filename', $appFileCopy->getName(),
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
                else
                    AppAlert::info(lng('file_copy.alert.choose_directory_path_for_move_file_href',
                        'filename', $appFileCopy->getName(),
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
            } else {
                if ($isDirectoryCopy)
                    AppAlert::info(lng('file_copy.alert.choose_directory_path_for_move_directory_href',
                        'filename', $appFileCopy->getName(),
                        'href', $appFileCopyHref,
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
                else
                    AppAlert::info(lng('file_copy.alert.choose_directory_path_for_move_file_href',
                        'filename', $appFileCopy->getName(),
                        'href', $appFileCopyHref,
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
            }
        }

        $appParameter->remove('cancel_copy_move');
        $appParameter->toString(true);

        $appFileCopy->setPath(AppDirectory::getInstance()->getDirectory());
        $appFileCopy->flushSession();
    }

    $listParameter       = null;
    $isCountCheckBoxList = AppConfig::getInstance()->get('enable_disable.count_checkbox_file_javascript');

    if (isset($_GET[AppDirectory::PARAMETER_LIST_URL]))
        $listParameter = AppDirectory::PARAMETER_LIST_URL . '=1';
?>

    <?php echo AppAlert::display(); ?>
    <?php echo $appLocationPath->display(); ?>

    <form action="<?php echo env('app.http.host'); ?>/file_action.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-checkbox-all">
        <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

        <ul class="list<?php if (AppConfig::getInstance()->get('enable_disable.list_file_double') == false) { ?> not-double<?php } ?>">
            <?php echo $bufferBack; ?>

            <?php if ($handlerCount > 0) { ?>
                <?php for ($i = $handlerPage['begin']; $i < $handlerPage['end']; ++$i) { ?>
                    <?php $entry        = $handlerList[$i]; ?>
                    <?php $entryPath    = $entry['path']; ?>
                    <?php $chmodPerms   = $entry['perms']; ?>
                    <?php $urlParameter = $appParameter->toString() . '&' . AppDirectory::PARAMETER_NAME_URL . '=' . AppDirectory::rawEncode($entry['name']); ?>

                    <?php if ($entry['is_directory']) { ?>
                        <li class="has-first-not-entry is-end-list-option directory is-has-checkbox">
                            <div class="icon">
                                <?php $id = 'folder-' . AppDirectory::rawEncode($entry['name']); ?>

                                <input
                                        type="checkbox"
                                        name="entrys[]"
                                        id="<?php echo $id; ?>"
                                        value="<?php echo AppDirectory::rawEncode($entry['name']); ?>"
                                        <?php if ($isCountCheckBoxList) { ?> onclick="javascript:Main.CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                                <label for="<?php echo $id; ?>" class="not-content"></label>
                                <a href="file_info.php<?php echo $urlParameter; ?>">
                                    <span class="icomoon icon-folder"></span>
                                </a>
                            </div>
                            <a href="index.php?<?php echo AppDirectory::PARAMETER_DIRECTORY_URL . '=' . AppDirectory::rawEncode($entryPath) . '&' . $listParameter; ?>" class="file-name<?php if ($entry['is_app']) { ?> file-name-of-app<?php } ?>">
                                <span><?php echo $entry['name']; ?></span>
                            </a>
                            <a href="file_chmod.php<?php echo $urlParameter; ?>" class="chmod-permission">
                                <span><?php echo $entry['perms']; ?></span>
                            </a>
                        </li>
                    <?php } else { ?>
                        <li class="has-first-not-entry is-end-list-option file">
                            <div class="icon">
                                <?php $id = 'file-' . AppDirectory::rawEncode($entry['name']); ?>

                                <input
                                        type="checkbox"
                                        name="entrys[]"
                                        id="<?php echo $id; ?>"
                                        value="<?php echo AppDirectory::rawEncode($entry['name']); ?>"
                                        <?php if ($isCountCheckBoxList) { ?> onclick="javascript:Main.CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                                <label for="<?php echo $id; ?>" class="not-content"></label>

                                <?php if ($entry['is_edit']) { ?><a href="<?php echo $entry['edit_href'] . $urlParameter; ?>"><?php } ?>
                                    <span class="icomoon <?php echo $entry['icon']; ?>"></span>
                                <?php if ($entry['is_edit']) { ?></a><?php } ?>
                            </div>
                            <a href="file_info.php<?php echo $urlParameter; ?>" class="file-name<?php if ($entry['is_app']) { ?> file-name-of-app<?php } ?>">
                                <span><?php echo $entry['name']; ?></span>
                            </a>
                            <div class="chmod-size">
                                <span class="size"><?php echo FileInfo::sizeToString($entry['size']); ?></span><span>,</span>
                                <a href="file_chmod.php<?php echo $urlParameter; ?>" class="chmod-permission">
                                    <span><?php echo $entry['perms']; ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                <?php } ?>

                <li class="end-list-option">
                    <div class="checkbox-all">
                        <input type="checkbox" name="checked_all_entry" id="form-list-checked-all-entry" onclick="javascript:Main.CheckboxCheckAll.onCheckAll();"/>
                        <label for="form-list-checked-all-entry">
                            <span><?php echo lng('home.checkbox_all_entry'); ?></span>
                            <?php if ($isCountCheckBoxList) { ?>
                                <span id="form-list-checkall-count"></span>
                            <?php } ?>
                        </label>
                    </div>

                    <?php if (AppConfig::getInstance()->get('paging.file_home_list') > 0 && $handlerPage['total'] > 1) { ?>
                        <div class="paging">
                            <?php echo $pagePaging->display($handlerPage['current'], $handlerPage['total']); ?>
                        </div>
                    <?php } ?>
                </li>
            <?php } else { ?>
                <li class="empty">
                    <span class="icomoon icon-folder-o"></span>
                    <span><?php echo lng('home.directory_empty'); ?></span>
                </li>
            <?php } ?>
        </ul>

        <?php if ($handlerCount > 0) { ?>
            <ul class="action-multi">
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_RENAME_MULTI; ?>">
                        <span class="icomoon icon-edit"></span>
                        <span class="label"><?php echo lng('home.action_multi.rename'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_COPY_MULTI; ?>">
                        <span class="icomoon icon-copy"></span>
                        <span class="label"><?php echo lng('home.action_multi.copy'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_DELETE_MULTI; ?>">
                        <span class="icomoon icon-trash"></span>
                        <span class="label"><?php echo lng('home.action_multi.delete'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_ZIP_MULTI; ?>">
                        <span class="icomoon icon-archive"></span>
                        <span class="label"><?php echo lng('home.action_multi.zip'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_CHMOD_MULTI; ?>">
                        <span class="icomoon icon-key"></span>
                        <span class="label"><?php echo lng('home.action_multi.chmod'); ?></span>
                    </button>
                </li>
            </ul>
        <?php } ?>
    </form>

    <?php if ($developmentMode) { ?>
        <ul class="alert">
            <li class="warning"><span><?php echo lng('home.alert.tips'); ?></span></li>
        </ul>
    <?php } ?>

    <ul class="menu-action">
        <li>
            <a href="create.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-folder-create"></span>
                <span><?php echo lng('home.menu_action.create'); ?></span>
            </a>
        </li>
        <li>
            <a href="upload.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-folder-upload"></span>
                <span><?php echo lng('home.menu_action.upload'); ?></span>
            </a>
        </li>
        <li>
            <a href="import.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-folder-download"></span>
                <span><?php echo lng('home.menu_action.import'); ?></span>
            </a>
        </li>

        <?php if ($isRoot == false) { ?>
            <?php
                $appParameterDirectory = new AppParameter();
                $appParameterDirectory->add(AppDirectory::PARAMETER_DIRECTORY_URL, AppDirectory::getInstance()->getParentDirectoryEncode(), true);
                $appParameterDirectory->add(AppDirectory::PARAMETER_PAGE_URL,      AppDirectory::getInstance()->getPage(),                  AppDirectory::getInstance()->getPage() > 1);
                $appParameterDirectory->add(AppDirectory::PARAMETER_NAME_URL,      AppDirectory::getInstance()->getNameInParentEncode(),    true);
                $appParameterDirectory->add(AppDirectory::PARAMETER_LIST_URL,      1,                                                       true);
            ?>

            <li>
                <a href="file_rename.php<?php echo $appParameterDirectory->toString(); ?>">
                    <span class="icomoon icon-edit"></span>
                    <span><?php echo lng('file_info.menu_action.rename'); ?></span>
                </a>
            </li>
            <li>
                <a href="file_copy.php<?php echo $appParameterDirectory->toString(); ?>">
                    <span class="icomoon icon-copy"></span>
                    <span><?php echo lng('file_info.menu_action.copy'); ?></span>
                </a>
            </li>
            <li>
                <a href="file_delete.php<?php echo $appParameterDirectory->toString(); ?>">
                    <span class="icomoon icon-trash"></span>
                    <span><?php echo lng('file_info.menu_action.delete'); ?></span>
                </a>
            </li>
            <li>
                <a href="file_chmod.php<?php echo $appParameterDirectory->toString(); ?>">
                    <span class="icomoon icon-key"></span>
                    <span><?php echo lng('file_info.menu_action.chmod'); ?></span>
                </a>
            </li>
        <?php } ?>
    </ul>

<?php require_once('incfiles' . SP . 'footer.php'); ?>
