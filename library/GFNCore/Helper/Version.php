<?php /*d25eec3a5b557c7723faeeb481a0bd2c524d6b4d*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Alpha 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Helper_Version
{
    public static function i2s($version)
    {
        $version = strval($version);
        $len = strlen($version);

        if ($len > 7)
        {
            throw new GFNCore_Exception('Not a valid version id');
        }

        if ($len < 7)
        {
            $version = sprintf('%7d', $version);
        }

        $major = intval(substr($version, 0, 1));
        $minor = intval(substr($version, 1, 2));
        $build = intval(substr($version, 3, 2));
        $channel = intval(substr($version, 5, 1));
        $level = intval(substr($version, 6, 1));

        switch ($channel)
        {
            case 9:
                $channel = 'Patch';
                break;

            case 8:
                $level += 10;
            case 7:
                $channel = 'Stable';
                break;

            case 6:
                $level += 10;
            case 5:
                $channel = 'RC';
                break;

            case 4:
                $level += 10;
            case 3:
                $channel = 'Beta';
                break;

            case 2:
                $level += 10;
            case 1:
                $channel = 'Alpha';
                break;

            default:
                throw new GFNCore_Exception('Not a valid version id');
        }

        if ($channel == 'Stable')
        {
            if ($level < 2)
            {
                return sprintf('%u.%u.%u', $major, $minor, $build);
            }

            $channel = 'Update';
        }

        return sprintf('%u.%u.%u %s %u', $major, $minor, $build, $channel, $level);
    }

    public static function s2i($version)
    {
        preg_match('/^([\d]{1}).([\d]{1,2}).([\d]{1,2})(.*?)$/is', $version, $matches);

        $output  = intval($matches[1]) * 1000000;
        $output += intval($matches[2]) * 10000;
        $output += intval($matches[3]) * 100;

        if (!empty($matches[4]))
        {
            preg_match('/^([\w]).*?[\w]*(.*?)$/is', trim($matches[4]), $matches);

            $output += intval($matches[2]);

            switch (strtolower($matches[1]))
            {
                case 'a':
                    $output += 10;
                    break;

                case 'b':
                    $output += 30;
                    break;

                case 'r':
                    $output += 50;
                    break;

                case 'u':
                case 's':
                    $output += 70;
                    break;

                case 'p':
                    $output += 90;
            }
        }

        return intval($output);
    }

    public static function getChannel($version)
    {
        if (is_numeric($version))
        {
            $version = intval($version);
        }
        else
        {
            $version = self::s2i($version);
        }

        $channel = $version % 100;

        if ($channel < 30)
        {
            return 'alpha';
        }

        if ($channel < 50)
        {
            return 'beta';
        }

        if ($channel < 70)
        {
            return 'rc';
        }

        return 'stable';
    }

    public static function parse($version)
    {

    }
} 