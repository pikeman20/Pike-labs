<?php

    namespace Librarys\Error;

    use Librarys\Buffer\BufferOutput;

    class ErrorDisplay
    {

        private static $errors;
        private static $exceptions;
        private static $isDisplayAlready;

        public static function putError($label, $type, $str, $file, $line)
        {
            self::putErrors([
                'label' => $label,
                'type'  => $type,
                'str'   => $str,
                'file'  => $file,
                'line'  => $line
            ]);
        }

        public static function putErrors($errors)
        {
            self::$errors = $errors;
        }

        public static function putExceptions($exceptions)
        {
            self::$exceptions = $exceptions;
        }

        public static function isErrors()
        {
            return is_array(self::$errors) && count(self::$errors) > 0;
        }

        public static function isExceptions()
        {
            return is_object(self::$exceptions);
        }

        public static function display()
        {
            if (self::$isDisplayAlready == false)
                self::$isDisplayAlready = true;
            else
                return;

            $isErrors     = self::isErrors();
            $isExceptions = self::isExceptions();

            if ($isErrors == false && $isExceptions == false)
                return;

            BufferOutput::clearBuffer();

            $class      = null;
            $trace      = null;
            $message    = null;
            $file       = null;
            $line       = 0;
            $label      = null;

            if ($isExceptions) {
                $class   = get_class(self::$exceptions);
                $trace   = self::$exceptions->getTrace();
                $message = self::$exceptions->getMessage();
                $file    = self::$exceptions->getFile();
                $line    = self::$exceptions->getLine();
                $label   = ErrorHandler::getLabelErrorCode(self::$exceptions->getCode());
            } else {
                $label = ErrorHandler::getLabelErrorCode(self::$errors['type']);
                $message = self::$errors['str'];
                $file    = self::$errors['file'];
                $line    = self::$errors['line'];
            }

            $object = new \stdClass();

            $object->isErrors     = $isErrors;
            $object->isExceptions = $isExceptions;
            $object->class        = $class;
            $object->trace        = $trace;
            $object->message      = $message;
            $object->line         = $line;
            $object->file         = $file;
            $object->label        = $label;

            unset($isErrors);
            unset($isExceptions);
            unset($class);
            unset($trace);
            unset($message);
            unset($file);
            unset($label);

            require_once(env('app.dev.error_reported.tpl'));
        }

        public static function argsExport($args)
        {
            if (is_array($args) == false || count($args) <= 0)
                return null;

            $buffer = null;
            $count  = count($args);

            foreach ($args AS $index => $arg) {
                if (is_array($arg))
                    $buffer .= 'array(' . self::argsExport($arg) . ')';
                else if (is_object($arg))
                    $buffer .= 'object(<span class="class">' . get_class($arg) . '</span>)';
                else if (is_resource($arg))
                    $buffer .= 'resource(<span class="resource">' . get_resource_type($arg) . '</span>)';
                else if (is_string($arg))
                    $buffer .= '\'' . $arg . '\'';
                else if (is_null($arg))
                    $buffer .= 'null';
                else
                    $buffer .= $arg;

                if ($index + 1 < $count)
                    $buffer .= ', ';
            }

            return $buffer;
        }
    }
