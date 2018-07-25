<?php /*{$___fileHash}*/

/**
 * @package    {$___addOnTitle}
 * @version    {$___fileVersion}
 * @since      {$___fileCreateVersion}
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-{$___currentYear} GoodForNothing Labs <https://gfnlabs.com/>
 * @license    {$___licenseLink}
 * @link       {$___addOnLink}
 */
class GFNNotify_Listener_LoadClass
{
    public static function extend($class, array &$extend)
    {
        $extend[] = 'GFNNotify_Extend_' . $class;
    }
} 