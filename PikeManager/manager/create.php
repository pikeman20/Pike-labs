<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\Config\AppConfig;
    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;

    define('LOADED',      1);
    define('TYPE_FOLDER', 1);
    define('TYPE_FILE',   2);

    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    $title  = lng('create.title_page');
    AppAlert::setID(ALERT_CREATE);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'header.php');

    if (AppDirectory::getInstance()->isDirectoryExists() == false)
        AppAlert::danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if (AppDirectory::getInstance()->isPermissionDenyPath())
        AppAlert::danger(lng('home.alert.path_not_permission', 'path', AppDirectory::getInstance()->getDirectory()), ALERT_INDEX, env('app.http.host'));

    $appLocationPath = new AppLocationPath('create.php');
    $appLocationPath->setIsPrintLastEntry(true);

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, AppDirectory::getInstance()->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      AppDirectory::getInstance()->getPage(),            AppDirectory::getInstance()->getPage() > 1);

    $forms = [
        'name' => null,
        'type' => TYPE_FOLDER,
        'path' => null
    ];

    if (isset($_POST['create']) || isset($_POST['create_and_continue'])) {
        $forms['name'] = addslashes($_POST['name']);
        $forms['type'] = intval($_POST['type']);

        if (empty($forms['name'])) {
            if ($forms['type'] === TYPE_FOLDER)
                AppAlert::danger(lng('create.alert.not_input_name_directory'));
            else if ($forms['type'] === TYPE_FILE)
                AppAlert::danger(lng('create.alert.not_input_name_file'));
            else
                AppAlert::danger(lng('create.alert.not_choose_type'));
        } else if ($forms['type'] != null && $forms['type'] !== TYPE_FOLDER && $forms['type'] !== TYPE_FILE) {
            AppAlert::danger(lng('create.alert.not_choose_type'));
        } else if (FileInfo::isNameValidate($forms['name']) == false) {
            if ($forms['type'] === TYPE_FOLDER)
                AppAlert::danger(lng('create.alert.name_not_validate_type_directory', 'validate', FileInfo::FILENAME_VALIDATE));
            else if ($forms['type'] === TYPE_FILE)
                AppAlert::danger(lng('create.alert.name_not_validate_type_file', 'validate', FileInfo::FILENAME_VALIDATE));
        } else {
            $forms['path'] = FileInfo::filterPaths(AppDirectory::getInstance()->getDirectory() . SP . $forms['name']);

            if (FileInfo::fileExists($forms['path'])) {
                if (FileInfo::isTypeDirectory($forms['path']))
                    AppAlert::danger(lng('create.alert.name_is_exists_type_directory'));
                else
                    AppAlert::danger(lng('create.alert.name_is_exists_type_file'));
            } else {
                if ($forms['type'] === TYPE_FOLDER) {
                    if (FileInfo::mkdir($forms['path']) == false) {
                        AppAlert::danger(lng('create.alert.create_directory_failed', 'filename', $forms['name']));
                    } else if (isset($_POST['create_and_continue']) == false) {
                        if (AppConfig::getInstance()->get('auto_redirect.create_directory')) {
                            $appParameter->set(AppDirectory::PARAMETER_DIRECTORY_URL, AppDirectory::rawEncode(AppDirectory::getInstance()->getDirectory() . SP . $forms['name']));
                            $appParameter->remove(AppDirectory::PARAMETER_PAGE_URL);
                            $appParameter->toString(true);
                        }

                        AppAlert::success(lng('create.alert.create_directory_success', 'filename', $forms['name']), ALERT_INDEX, 'index.php' . $appParameter->toString());
                    } else {
                        AppAlert::success(lng('create.alert.create_directory_success', 'filename', $forms['name']));
                        $forms['name'] = null;
                    }
                } else if ($forms['type'] === TYPE_FILE) {
                    if (FileInfo::fileWriteContents($forms['path'], '#') === false) {
                        AppAlert::danger(lng('create.alert.create_file_failed', 'filename', $forms['name']));
                    } else if (isset($_POST['create_and_continue']) == false) {
                        $urlGoto = 'index.php';
                        $idAlert = ALERT_INDEX;

                        if (AppConfig::getInstance()->get('auto_redirect.create_file')) {
                            $fileInfo = new FileInfo(AppDirectory::getInstance()->getDirectory() . SP . $forms['name']);
                            $fileMime = new FileMime($fileInfo);

                            if ($fileMime->isFormatTextEdit() && $fileMime->isFormatTextAsEdit() == false) {
                                $urlGoto = 'file_edit_text.php';
                                $idAlert = ALERT_FILE_EDIT_TEXT;
                            } else {
                                $urlGoto = 'file_info.php';
                                $idAlert = ALERT_FILE_INFO;
                            }

                            $appParameter->set(AppDirectory::PARAMETER_NAME_URL, AppDirectory::rawEncode($forms['name']));
                            $appParameter->toString(true);
                        }

                        AppAlert::success(lng('create.alert.create_file_success', 'filename', $forms['name']), $idAlert, $urlGoto . $appParameter->toString());
                    } else {
                        AppAlert::success(lng('create.alert.create_file_success', 'filename', $forms['name']));
                        $forms['name'] = null;
                    }
                } else {
                    AppAlert::danger(lng('create.alert.not_choose_type'));
                }
            }
        }
    }
?>

    <?php AppAlert::display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('create.title_page'); ?></span>
        </div>
        <form action="<?php echo env('app.http.host'); ?>/create.php<?php echo $appParameter->toString(); ?>" method="post" id="form-create-directory-file">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('create.form.input.name'); ?></span>
                    <input type="text" name="name" value="<?php echo $forms['name']; ?>" class="none" placeholder="<?php echo lng('create.form.placeholder.input_name'); ?>"/>
                </li>
                <li class="radio-choose">
                    <ul class="radio-choose-tab">
                        <li>
                            <input type="radio" name="type" value="<?php echo TYPE_FOLDER; ?>"<?php if ($forms['type'] === TYPE_FOLDER) { ?> checked="checked"<?php } ?> id="type-folder"/>
                            <label for="type-folder">
                                <span><?php echo lng('create.form.input.radio_choose_type_directory'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="type" value="<?php echo TYPE_FILE; ?>" id="type-file"<?php if ($forms['type'] === TYPE_FILE) { ?> checked="checked"<?php } ?>/>
                            <label for="type-file">
                                <span><?php echo lng('create.form.input.radio_choose_type_file'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>
                <li class="button">
                    <button type="submit" name="create" id="button-save-on-javascript">
                        <span><?php echo lng('create.form.button.create'); ?></span>
                    </button>
                    <button type="submit" name="create_and_continue">
                        <span><?php echo lng('create.form.button.create_and_continue'); ?></span>
                    </button>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('create.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
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
    </ul>

<?php require_once('incfiles' . SP . 'footer.php'); ?>
