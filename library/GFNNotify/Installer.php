<?php /*{$___fileHash}*/

/**
 * @package    {$___addOnTitle}
 * @version    {$___fileVersion}
 * @since      {$___fileCreateVersion}
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-{$___currentYear} GoodForNothing Labs <http://gfnlabs.com/>
 * @license    {$___licenseLink}
 * @link       {$___addOnLink}
 */
class GFNNotify_Installer extends GFNCore_Installer_Abstract
{
    public function getVersionId()
    {
        return '{$___currentVersionId}';
    }

    public function getSqlDataPath()
    {
        return realpath(dirname(__FILE__) . '/Data/Sql');
    }

    public function getSqlDataClassPrefix()
    {
        return 'GFNNotify_Data_Sql_';
    }
} 