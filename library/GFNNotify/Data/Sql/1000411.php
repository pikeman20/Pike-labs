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
class GFNNotify_Data_Sql_1000411 extends GFNCore_Data_Sql_Abstract
{
    public function install()
    {
        $this->table()->alter('xf_user_option', function(GFNCore_Db_Schema_Table_Alter $table)
        {
            $table->boolean('show_notification_popup')->default(1);
        });
    }

    public function uninstall()
    {
        $this->table()->alter('xf_user_option', function(GFNCore_Db_Schema_Table_Alter $table)
        {
            $table->drop('show_notification_popup');
        });
    }
}