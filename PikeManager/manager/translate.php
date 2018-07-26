<?php

    define('LOADED', 1);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if (isset($_GET['submit'])) {
        if (isset($_GET['from_lang']) && isset($_GET['to_lang']) && isset($_GET['text'])) {
            $fromLang = addslashes($_GET['from_lang']);
            $toLang   = addslashes($_GET['to_lang']);
            $text     = addslashes($_GET['text']);

            $url  = 'https://translate.google.com/m?hl=vi';
            $url .= '&sl=' . $fromLang;
            $url .= '&tl=' . $toLang;
            $url .= '&ie=UTF-8&prev=_m&q=' . urlencode($text);
            $curl = new Librarys\File\FileCurl($url);

            if ($curl->curl() != false) {
                $startText = '<div dir="ltr" class="t0">';
                $endText   = '</div>';

                $buffer  = $curl->getBuffer();
                $posText = strpos($buffer, $startText);
                $array   = array('text' => null);

                if ($posText !== false) {
                    $posText      += strlen($startText);
                    $posEndText    = strpos($buffer, $endText, $posText);
                    $buffer        = substr($buffer, $posText, $posEndText - $posText);
                    $array['text'] = $buffer;
                }

                die(json_encode($array));
            }
        } else {
            exit;
        }
    }

    require_once('incfiles' . SP . 'header.php');
?>

    <script type="text/javascript">
        Main.TranslateLanguage.translate("Hello word", "en", "vi");
    </script>

<?php require_once('incfiles' . SP . 'footer.php'); ?>