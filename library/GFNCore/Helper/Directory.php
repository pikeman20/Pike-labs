<?php /*10bda62b59925234a7c7e3256e5c9323028cdc11*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Alpha 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Helper_Directory
{
    /**
     * Is used to copy an entire directory recursively.
     *
     * @param $source
     * @param $target
     * @param bool $createIndexHtml
     * @throws GFNCore_Exception
     */
    public static function copy($source, $target, $createIndexHtml = false)
    {
        if (!is_dir($source))
        {
            throw new GFNCore_Exception('Source directory does not exist.');
        }

        if (!is_dir($target))
        {
            XenForo_Helper_File::createDirectory($target, $createIndexHtml);
        }

        $handle = opendir($source);
        if (!$handle)
        {
            return;
        }

        while (($file = readdir($handle)) !== false)
        {
            if (in_array($file, array('.', '..')))
            {
                continue;
            }

            $from = $source . '/' . $file;
            $to = $target . '/' . $file;

            if (is_dir($from))
            {
                self::copy($from, $to);
            }
            else
            {
                copy($from, $to);
            }
        }

        closedir($handle);
    }

    /**
     * Reads a directory and return all the child nodes.
     *
     * @param $dir
     * @param bool $recursive
     * @param bool $includeDir
     * @return array
     */
    public static function read($dir, $recursive = true, $includeDir = true)
    {
        $dir = realpath($dir);
        $dir = str_replace('\\', '/', $dir);
        $dir = rtrim($dir, '/');

        $handle = opendir($dir);
        if (!$handle)
        {
            return array();
        }

        $return = array();

        while (($file = readdir($handle)) !== false)
        {
            if (in_array($file, array('.', '..')))
            {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_dir($path))
            {
                if ($includeDir)
                {
                    $return[] = $path;
                }

                if ($recursive)
                {
                    $return = array_merge($return, self::read($path, $recursive, $includeDir));
                }
            }
            else
            {
                $return[] = $path;
            }
        }

        closedir($handle);
        return $return;
    }
}