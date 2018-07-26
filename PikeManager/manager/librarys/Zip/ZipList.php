<?php

  namespace Librarys\Zip;

    if (defined('LOADED') == false)
        exit;

    use Librarys\App\AppDirectory;
    use Librarys\App\Config\AppConfig;
    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\Exception\NullPointerException;

    class ZipList
    {

        private $pathFileZip;
        private $pathContentInZip;
        private $separator;

        private $directoryZipOrigin;
        private $directoryZip;

        private $pclZip;
        private $lists;
        private $countLists;

        private $pageCurrent;
        private $pageTotal;
        private $pageBeginLoop;
        private $pageEndLoop;
        private $pageMax;
        private $pageIsOddEntry;
        private $pageCountEntryOnPage;

        public function __construct($pathFileZip, $pathContentInZip = null, $pageCurrent = null, $separator = '/')
        {
            $this->setPathFileZip($pathFileZip);
            $this->setPathContentInZip($pathContentInZip);
            $this->setPageCurrent($pageCurrent);
            $this->setPageMax(AppConfig::getInstance()->get('paging.file_view_zip'));
            $this->setSeparator($separator);
        }

        public function setPathFileZip($pathFileZip)
        {
            if ($pathFileZip == null || empty($pathFileZip))
                throw new NullPointerException('Path file zip is empty');

            $this->pathFileZip = FileInfo::filterPaths($pathFileZip);
        }

        public function getPathFileZip()
        {
            return $this->pathFileZip;
        }

        public function setPathContentInZip($pathContentInZip)
        {
            if ($pathContentInZip == null || empty($pathContentInZip))
                return;

            $this->pathContentInZip   = FileInfo::filterPaths($pathContentInZip);
            $this->directoryZipOrigin = separator($this->pathContentInZip, $this->separator);
            $this->directoryZip       = separator($this->directoryZipOrigin . $this->separator, $this->separator);
        }

        public function getPathContentInZip()
        {
            return $this->pathContentInZip;
        }

        public function setSeparator($separator = '/')
        {
            if ($separator == null || empty($separator))
                throw new NullPointerException('Separator path is empty');

            $this->separator = $separator;
        }

        public function getSeparator()
        {
            return $this->separator;
        }

        public function setPageCurrent($page)
        {
            if ($page == null || empty($page) || intval($page) <= 0)
                $page = 1;

            $this->pageCurrent = $page;
        }

        public function getDirectoryZipOrigin()
        {
            return $this->directoryZipOrigin;
        }

        public function getDirectoryZip()
        {
            return $this->directoryZip;
        }

        public function getLists()
        {
            return $this->lists;
        }

        public function getCountLists()
        {
            return $this->countLists;
        }

        public function getPageCurrent()
        {
            return $this->pageCurrent;
        }

        public function getPageTotal()
        {
            return $this->pageTotal;
        }

        public function getPageBeginLoop()
        {
            return $this->pageBeginLoop;
        }

        public function getPageEndLoop()
        {
            return $this->pageEndLoop;
        }

        public function setPageMax($max)
        {
            $this->pageMax = $max;
        }

        public function getPageMax()
        {
            return $this->pageMax;
        }

        public function getPageCountEntryOnPage()
        {
            return $this->pageCountEntryOnPage;
        }

        public function isPageOddEntry()
        {
            return $this->pageIsOddEntry;
        }

        public function isPaging()
        {
            return $this->getPageTotal() > 1 && $this->getPageMax() > 0;
        }

        public function putEntryAppLocationPath($appLocationPath, $nameDirectory)
        {
            if ($appLocationPath == null)
                throw new NullPointerException('AppLocationPath instance is null');

            if ($this->directoryZipOrigin != null) {
                $locationPath = explode($this->separator, $this->directoryZipOrigin);
                $appLocationPath->addEntry($nameDirectory, null);

                if (is_array($locationPath) && count($locationPath) > 0)
                    foreach ($locationPath AS $location)
                        $appLocationPath->addEntry($location, null);
            }
        }

        public function execute()
        {
            $this->pclZip = new PclZip($this->pathFileZip);
            $this->lists  = $this->pclZip->listContent();

            if ($this->lists !== false && is_array($this->lists)) {
                $arrayFolders = [];
                $arrayFiles   = [];

                foreach ($this->lists AS $entry) {
                    $entryFileName = $entry['filename'];
                    $entryFileSize = $entry['size'];

                    if (strpos($entryFileName, $this->separator) === false && $this->directoryZip != null) {
                        $arrayFiles[$entryFileName] = self::putIconNameToArray([
                            'path'   => $entryFileName,
                            'name'   => $entryFileName,
                            'is_dir' => false,
                            'size'   => $entryFileSize
                        ]);
                    } else if (preg_match('#(' . $this->directoryZip . '(.+?))(' . $this->separator . '|$)+#', $entryFileName, $entryMatches)) {
                        if ($entryMatches[3] == $this->separator && isset($arrayFolders[$entryMatches[2]]) == false) {
                            $arrayFolders[$entryMatches[2]] = [
                                'path'   => $entryMatches[1],
                                'name'   => $entryMatches[2],
                                'is_dir' => true,
                                'size'   => 0
                            ];
                        } else if ($entryMatches[3] != $this->separator && $entry['folder'] == false) {
                            $arrayFiles[$entryMatches[2]] = self::putIconNameToArray([
                                'path'   => $entryMatches[1],
                                'name'   => $entryMatches[2],
                                'is_dir' => false,
                                'size'   => $entryFileSize
                            ]);
                        }
                    }
                }

                $this->lists     = array();
                $countArayFolder = count($arrayFolders);
                $countArrayFile  = count($arrayFiles);

                if ($countArayFolder > 0) {
                    ksort($arrayFolders);

                    foreach ($arrayFolders AS $entry)
                        $this->lists[] = $entry;
                }

                if ($countArrayFile > 0) {
                    ksort($arrayFiles);

                    foreach ($arrayFiles AS $entry)
                        $this->lists[] = $entry;
                }

                array_splice($arrayFolders, 0, $countArayFolder);
                array_splice($arrayFiles,   0, $countArrayFile);

                $this->countLists = count($this->lists);
            } else {
                $this->lists      = null;
                $this->countLists = 0;
            }

            $this->executePage();
        }

        private static function putIconNameToArray($entry)
        {
            $info = new FileInfo($entry['path'], false);
            $mime = new FileMime($info);
            $icon = null;

            if ($mime->isFormatText())
                $icon   = 'icon-file-text';
            else if ($mime->isFormatCode())
                $icon   = 'icon-file-code';
            else if ($mime->isFormatArchive())
                $icon   = 'icon-file-archive';
            else if ($mime->isFormatAudio())
                $icon   = 'icon-file-audio';
            else if ($mime->isFormatVideo())
                $icon   = 'icon-file-video';
            else if ($mime->isFormatDocument())
                $icon   = 'icon-file-document';
            else if ($mime->isFormatImage())
                $icon   = 'icon-file-image';
            else if ($mime->isFormatSource())
                $icon   = 'icon-file-code';
            else
                $icon   = 'icon-file';

            $entry['icon'] = $icon;

            return $entry;
        }

        private function executePage()
        {
            if ($this->countLists > 0 && $this->pageMax > 0) {
                $this->pageTotal      = ceil($this->countLists / $this->pageMax);
                $this->pageBeginLoop = ($this->pageCurrent * $this->pageMax) - $this->pageMax;
                $this->pageEndLoop   = 0;

                if ($this->pageBeginLoop + $this->pageMax <= $this->countLists)
                    $this->pageEndLoop =  $this->pageBeginLoop + $this->pageMax;
                else
                    $this->pageEndLoop = $this->countLists;

                if (($this->pageEndLoop - $this->pageBeginLoop) % 2 !== 0)
                    $this->pageIsOddEntry = true;

                $this->pageCountEntryOnPage = $this->pageEndLoop - $this->pageBeginLoop;
            } else {
                $this->pageTotal            = 1;
                $this->pageBeginLoop        = 0;
                $this->pageEndLoop          = $this->countLists;
                $this->pageCountEntryOnPage = $this->countLists;
            }

            if ($this->pageCountEntryOnPage % 2 !== 0)
                $this->pageIsOddEntry = true;
        }
    }
