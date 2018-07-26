<?php

    use Librarys\App\AppAlert;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\AppPaging;
    use Librarys\App\Config\AppConfig;
    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\File\FileCurl;
    use Librarys\Http\Request;
    use Librarys\Http\Secure\CFSRToken;

    define('LOADED',              1);
    define('PARAMETER_PAGE_EDIT', 'page_edit_text');

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

    if ($fileMime->isFormatTextEdit() == false) {
        $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
        AppAlert::danger(lng('file_edit_text.alert.file_is_not_format_text_edit'), ALERT_INDEX, $appParameter->toString(true));
    }

    if ($fileMime->isFormatTextAsEdit() == false)
        $title = lng('file_edit_text.title_page');
    else
        $title = lng('file_edit_text.title_page_as');

    $isEnableEditHighlight = false; //Request::isLocal();

    AppAlert::setID(ALERT_FILE_EDIT_TEXT);
    require_once('incfiles' . SP . 'header.php');

    $edits = [
        'page' => [
            'total'      => 0,
            'current'    => 0,
            'begin_loop' => 0,
            'end_loop'   => 0,
            'max'        => AppConfig::getInstance()->get('paging.file_edit_text')
        ],

        'path'               => FileInfo::filterPaths(AppDirectory::getInstance()->getDirectoryAndName()),
        'content'            => null,
        'is_syntax'          => true,

        'content_lines'      => null,
        'content_line_count' => 0
    ];

    if (isset($_GET[PARAMETER_PAGE_EDIT]) && empty($_GET[PARAMETER_PAGE_EDIT]) == false)
        $edits['page']['current'] = intval(addslashes($_GET[PARAMETER_PAGE_EDIT]));

    if ($edits['page']['current'] <= 0)
        $edits['page']['current'] = 1;

    $edits['page']['begin_loop'] = ($edits['page']['current'] * $edits['page']['max']) - $edits['page']['max'];
    $edits['page']['end_loop']   = 0;

    if ($edits['path'] != null)
        $edits['content'] = FileInfo::fileReadContents($edits['path']);

    $isCheckSyntax = true;
    $isMimePHP     = true;
    $pathPHPBin    = null;

    if (strcasecmp(FileInfo::extFile(AppDirectory::getInstance()->getName()), 'php') !== 0) {
        $isCheckSyntax = false;
        $isMimePHP     = false;
    } else if (function_exists('exec') == false || function_exists('shell_exec') == false) {
        $isCheckSyntax = false;
    } else if (($pathPHPBin = FileInfo::takePathPHPBin()) == null || empty($pathPHPBin)) {
        $isCheckSyntax = false;
    }

    if (isset($_POST['save'])) {
        $name = AppDirectory::getInstance()->getName();

        if ($isCheckSyntax && isset($_POST['is_syntax']) && intval($_POST['is_syntax']) === 1)
            $edits['is_syntax'] = true;

        if ($edits['page']['max'] > 0) {
            $edits['content'] = str_replace("\r\n", "\n", $edits['content']);
            $edits['content'] = str_replace("\n",   "\n", $edits['content']);

            if (strpos($edits['content'], "\n") !== false) {
                $edits['content_lines']      = explode("\n", $edits['content']);
                $edits['content_line_count'] = count($edits['content_lines']);
                $edits['content']            = null;

                if ($edits['page']['begin_loop'] + $edits['page']['max'] <= $edits['content_line_count'])
                    $edits['page']['end_loop'] = $edits['page']['begin_loop'] + $edits['page']['max'];
                else
                    $edits['page']['end_loop'] = $edits['content_line_count'];

                if ($edits['page']['begin_loop'] > 0) {
                    for ($begin = 0; $begin < $edits['page']['begin_loop']; ++$begin)
                        $edits['content'] .= $edits['content_lines'][$begin] . "\n";
                }

                $edits['content'] .= str_replace("\r", "\n",
                    str_replace("\r\n", "\n", $_POST['content'])
                );

                if ($edits['page']['current'] < ceil($edits['content_line_count'] / $edits['page']['max'])) {
                    for ($end = $edits['page']['end_loop']; $end < $edits['content_line_count']; ++$end)
                        $edits['content'] .= "\n" . $edits['content_lines'][$end];
                }
            } else {
                $edits['content'] = $_POST['content'];
            }
        } else {
            $edits['content'] = $_POST['content'];

            $edits['content'] = str_replace("\r\n", "\n", $edits['content']);
            $edits['content'] = str_replace("\r",   "\n", $edits['content']);
        }

        $isSave = true;

        if (strcasecmp($name, '.htaccess') === 0) {
            $serverRoot = env('server.document_root');
            $appRoot    = env('app.path.root');
            $tmp        = env('app.path.tmp') . SP . md5(rand(1000, 9999));
            $appAbs     = substr($appRoot, strlen($serverRoot) + 1);
            $tmpAbs     = substr($tmp, strlen($appRoot) + 1);

            if (FileInfo::isTypeDirectory($tmp) == false)
                FileInfo::mkdir($tmp, true);

            $folderManager    = $appAbs;
            $fileHtaccess     = '.htaccess';
            $fileIndex        = 'index.php';
            $fileManagerIndex = 'index.php';

            $pathFolderManager    = FileInfo::filterPaths($tmp . SP . $folderManager);
            $pathFileHtaccess     = FileInfo::filterPaths($tmp . SP . $fileHtaccess);
            $pathFileIndex        = FileInfo::filterPaths($tmp . SP . $fileIndex);
            $pathFileManagerIndex = FileInfo::filterPaths($pathFolderManager . SP . $fileManagerIndex);

            if (FileInfo::isTypeDirectory($pathFolderManager) == false)
                FileInfo::mkdir($pathFolderManager);

            if (FileInfo::fileExists($pathFileIndex))
                FileInfo::rrmdir($pathFileIndex);

            if (FileInfo::fileExists($pathFileManagerIndex))
                FileInfo::rrmdir($pathFileManagerIndex);

            $tokenRandom = CFSRToken::generator();

            FileInfo::fileWriteContents($pathFileIndex,        $tokenRandom);
            FileInfo::fileWriteContents($pathFileManagerIndex, $tokenRandom);
            FileInfo::fileWriteContents($pathFileHtaccess,     $edits['content']);

            $httpTmp             = separator(env('app.http.host') . SP . $tmpAbs, '/');
            $httpTmpIndex        = separator($httpTmp . SP . $fileIndex,          '/');
            $httpTmpManagerIndex = separator($httpTmp . SP . $folderManager . SP . $fileManagerIndex,   '/');

            $fileCurl          = new FileCurl($httpTmpIndex);
            $tokenIndex        = null;
            $tokenManagerIndex = null;
            $errorResponseCode = null;
            $errorIndex        = true;

            if ($fileCurl->curl() != false && $fileCurl->getHttpCode() === 200) {
                $tokenIndex = trim(addslashes($fileCurl->getBuffer()));

                $fileCurl->setURL($httpTmpManagerIndex);

                if ($fileCurl->curl() != false && $fileCurl->getHttpCode() === 200) {
                    $tokenManagerIndex = trim(addslashes($fileCurl->getBuffer()));
                } else {
                    $errorResponseCode = $fileCurl->getResponseCode();
                    $errorIndex        = false;
                }
            } else {
                $errorResponseCode = $fileCurl->getResponseCode();
                $errorIndex        = true;
            }

            $isSave = false;

            if (empty($tokenIndex) || empty($tokenManagerIndex) || strcmp($tokenRandom, $tokenIndex) !== 0 || strcmp($tokenRandom, $tokenManagerIndex) !== 0)
                AppAlert::danger(lng('file_edit_text.alert.htaccess_check_error_code', 'code', Request::httpResponseCodeToString($errorResponseCode)));
            else
                $isSave = true;

            if (FileInfo::isTypeDirectory($tmp))
                FileInfo::rrmdirSystem($tmp);
        }

        if ($isSave) {
            if (FileInfo::fileWriteContents($edits['path'], $edits['content']) !== false) {
                AppAlert::success(lng('file_edit_text.alert.save_text_success'));

                if ($isCheckSyntax && $edits['is_syntax']) {
                    $callback = null;

                    if (function_exists('exec'))
                        $callback = 'exec';
                    else if (function_exists('shell_exec'))
                        $callback = 'shell_exec';

                    if ($callback == null) {
                        AppAlert::danger(lng('file_edit_text.alert.function_exec_is_disable'));
                    } else {
                        $callback($pathPHPBin . ' -c -f -l ' . $edits['path'], $execOutput, $execValue);

                        if ($execValue == -1)
                            AppAlert::info(lng('file_edit_text.alert.not_check_syntax'));
                        else if ($execValue == 255 || count($execOutput) == 3)
                            AppAlert::danger($execOutput[1]);
                        else
                            AppAlert::success(lng('file_edit_text.alert.syntax_not_error'));
                    }
                }
            } else {
                AppAlert::danger(lng('file_edit_text.alert.save_text_failed'));
            }
        }
    }

    if ($edits['content'] != null && empty($edits['content']) == false && strlen($edits['content']) > 0) {
        $edits['content'] = str_replace("\r\n", "\n", $edits['content']);
        $edits['content'] = str_replace("\r",   "\n", $edits['content']);

        if ($edits['page']['max'] > 0 && strpos($edits['content'], "\n") !== false) {
            $edits['content_lines']      = explode("\n", $edits['content']);
            $edits['content_line_count'] = count($edits['content_lines']);

            if ($edits['content_line_count'] > $edits['page']['max']) {
                $edits['content']       = null;
                $edits['page']['total'] = ceil($edits['content_line_count'] / $edits['page']['max']);

                if ($edits['page']['begin_loop'] + $edits['page']['max'] <= $edits['content_line_count'])
                    $edits['page']['end_loop'] = $edits['page']['begin_loop'] + $edits['page']['max'];
                else
                    $edits['page']['end_loop'] = $edits['content_line_count'];

                for ($i = $edits['page']['begin_loop']; $i < $edits['page']['end_loop']; ++$i) {
                    if ($i >= $edits['page']['end_loop'] - 1)
                        $edits['content'] .= $edits['content_lines'][$i];
                    else
                        $edits['content'] .= $edits['content_lines'][$i] . "\n";
                }
            }
        }
    }

    $appPaging = new AppPaging(
        'file_edit_text.php' . $appParameter->toString(),
        'file_edit_text.php' . $appParameter->toString() . '&' . PARAMETER_PAGE_EDIT . '='
    );
?>

    <?php AppAlert::display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <?php if ($fileMime->isFormatTextAsEdit() == false) { ?>
                <span><?php echo lng('file_edit_text.title_page'); ?>: <?php echo AppDirectory::getInstance()->getName(); ?></span>
            <?php } else { ?>
                <span><?php echo lng('file_edit_text.title_page_as'); ?>: <?php echo AppDirectory::getInstance()->getName(); ?></span>
            <?php } ?>
        </div>
        <?php $appParameter->add(PARAMETER_PAGE_EDIT, $edits['page']['current'], $edits['page']['current'] > 1); ?>
        <form action="<?php echo env('app.http.host'); ?>/file_edit_text.php<?php echo $appParameter->toString(true); ?>" method="post" id="form-file-edit-javascript">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul class="form-element">
                <li class="textarea">
                    <span><?php echo lng('file_edit_text.form.input.content_file'); ?></span>
                    <?php if ($isEnableEditHighlight) { ?>
                        <div id="editor-highlight" class="editor-highlight">
                            <textarea name="content"><?php echo htmlspecialchars($edits['content']); ?></textarea>
                        </div>
                    <?php } else { ?>
                        <textarea name="content" rows="20"><?php echo htmlspecialchars($edits['content']); ?></textarea>
                    <?php } ?>
                </li>
                <?php if ($isCheckSyntax) { ?>
                    <li class="checkbox">
                        <span><?php echo lng('file_edit_text.form.input.options'); ?></span>
                        <ul>
                            <li>
                                <input type="checkbox" id="is-syntax" name="is_syntax" value="1"<?php if ($edits['is_syntax'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="is-syntax">
                                    <span><?php echo lng('file_edit_text.form.input.syntax_check'); ?></span>
                                </label>
                            </li>
                        </ul>
                    </li>
                <?php } ?>
                <?php if ($edits['page']['max'] > 0 && $edits['page']['total'] > 1) { ?>
                    <li class="paging">
                        <?php echo $appPaging->display($edits['page']['current'], $edits['page']['total']); ?>
                    </li>
                <?php } ?>
                <li class="button">
                    <button type="submit" name="save" id="button-save-on-javascript">
                        <span><?php echo lng('file_edit_text.form.button.save'); ?></span>
                    </button>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('file_edit_text.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
        <?php $appParameter->remove(PARAMETER_PAGE_EDIT); ?>
        <?php $appParameter->toString(true); ?>
    </div>

    <ul class="alert">
        <li class="info"><span><?php echo lng('file_edit_text.alert.tips'); ?></span></li>
        <?php if ($isMimePHP && $isCheckSyntax == false) { ?>
            <li class="warning"><span><?php echo lng('file_edit_text.alert.not_support_check_syntax'); ?></span></li>
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