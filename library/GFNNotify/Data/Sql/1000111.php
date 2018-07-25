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
class GFNNotify_Data_Sql_1000111 extends GFNCore_Data_Sql_Abstract
{
    public function install()
    {
        $this->table()->create('gfnnotify_notification', function(GFNCore_Db_Schema_Table_Create $table)
        {
            // Indices...
            $table->primary('notification_hash');

            // Columns...
            $table->string('notification_hash', 40);
        });
    }

    public function uninstall()
    {
        $this->table()->drop('gfnnotify_notification');
    }
} 