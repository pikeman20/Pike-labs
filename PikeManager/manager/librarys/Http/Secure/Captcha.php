<?php

    namespace Librarys\Http\Secure;

    use Librarys\Http\Request;

    class Captcha
    {

        protected $res;
        protected $buffer;
        protected $width;
        protected $height;
        protected $letters;

        const DEFAULT_WIDTH   = 100;
        const DEFAULT_HEIGHT  = 50;
        const DEFAULT_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        const SESSION_NAME = 'captcha';

        protected function __construct($width, $height, $letters, $fontSize, $fontPath)
        {
            $this->setWidth($width);
            $this->setHeight($height);
            $this->setLetters($letters);
            $this->setFontSize($fontSize);
            $this->setFontPath($fontPath);
            $this->draw();
        }

        protected function __wakeup()
        {

        }

        protected function __clone()
        {

        }

        public static function create($width = null, $height = null, $letters = null, $fontSize = null, $fontPath = null)
        {
            $envWidth   = env('app.captcha.width',   self::DEFAULT_WIDTH);
            $envHeight  = env('app.captcha.height',  self::DEFAULT_HEIGHT);
            $envLetters = env('app.captcha.letters', self::DEFAULT_LETTERS);

            $envFontSize = env('app.captcha.font.size', null);
            $envFontPath = env('app.captcha.font.path', null);

            if ($width == null)
                $width = $envWidth;

            if ($height == null)
                $height = $envHeight;

            if ($letters == null)
                $letters = $envLetters;

            if ($fontSize == null)
                $fontSize = $envFontSize;

            if ($fontPath == null)
                $fontPath = $envFontPath;

            return new Captcha($width, $height, $letters, $fontSize, $fontPath);
        }

        public function clear()
        {
            $this->buffer = null;
            $this->res    = imagecreatetruecolor($this->width, $this->height);
        }

        public function setWidth($width)
        {
            $this->width = $width;
        }

        public function getWidth()
        {
            return $this->width;
        }

        public function setHeight($height)
        {
            $this->height = $height;
        }

        public function getHeight()
        {
            return $this->height;
        }

        public function setLetters($letters)
        {
            $this->letters = $letters;
        }

        public function getLetters()
        {
            return $this->letters;
        }

        public function setFontSize($fontSize)
        {
            $this->fontSize = $fontSize;
        }

        public function getFontSize()
        {
            return $this->fontSize;
        }

        public function setFontPath($fontPath)
        {
            $this->fontPath = $fontPath;
        }

        public function getFontPath()
        {
            return $this->fontPath;
        }

        public function draw()
        {
            $this->clear();

            $backgroundColor = imagecolorallocate($this->res, 255, 255, 255);
            $lineColor       = imagecolorallocate($this->res, 155, 155, 155);
            $textColor       = imagecolorallocate($this->res,   0,   0,   0);

            imagefilledrectangle($this->res, 0, 0, $this->width, $this->height, $backgroundColor);

            for ($i = 0; $i < 5; ++$i)
                imageline($this->res, 0, rand() % $this->height, $this->width, rand() % $this->height, $lineColor);

            $pixelCount = ($this->width * $this->height) >> 3;

            for ($i = 0; $i < $pixelCount; ++$i)
                imagesetpixel($this->res, rand() % $this->width, rand() % $this->height, imagecolorallocate($this->res, rand(0, 255), rand(0, 255), rand(0, 255)));

            $letterLength    = strlen($this->letters);
            $letterCharacter = null;
            $letterWord      = null;
            $letterArray     = array();

            for ($i = 0; $i < 5; ++$i) {
                $letterCharacter  = $this->letters[rand(0, $letterLength - 1)];
                $letterWord      .= $letterCharacter;
                $letterArray[]    = $letterCharacter;
            }

            $angle       = 0;
            $ttfBox      = imagettfbbox($this->fontSize, $angle, $this->fontPath, 'A');
            $ttfWidth    = abs($ttfBox[4] - $ttfBox[0]) + 8;
            $ttfHeight   = abs($ttfBox[5] - $ttfBox[1]);
            $letterCount = count($letterArray);
            $xBegin      = (($this->width >> 1) - $ttfWidth) >> 1;

            for ($i = 0; $i < $letterCount; ++$i) {
                $letterCharacter = $letterArray[$i];

                imagettftext($this->res, $this->fontSize, $angle, $xBegin + ($i * $ttfWidth), $this->height - $ttfHeight, $textColor, $this->fontPath, $letterCharacter);
            }

            Request::session()->put(self::SESSION_NAME, $letterWord);
        }

        public function export()
        {
            if ($this->buffer !== null)
                return $this->buffer;

            ob_start();
            imagepng($this->res);
            imagedestroy($this->res);

            $this->buffer = ob_get_clean();

            return $this->buffer;
        }

        public function exportBase64()
        {
            return 'data:image/png;base64,' . base64_encode($this->export());
        }

    }
