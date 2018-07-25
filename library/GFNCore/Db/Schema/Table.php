<?php /*be5915154be2061816b539567a1fe0f264206b4c*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Db_Schema_Table
{
    public function create($name, $callback)
    {
        $table = new GFNCore_Db_Schema_Table_Create();
        $table->name = $name;
        call_user_func($callback, $table);
        $table->execute();
    }

    public function alter($name, $callback)
    {
        $table = new GFNCore_Db_Schema_Table_Alter();
        $table->name = $name;
        call_user_func($callback, $table);
        $table->execute();
    }

    public function drop($name)
    {
        $table = new GFNCore_Db_Schema_Table_Drop();
        $table->name = $name;
        $table->execute();
    }

    public function truncate($name)
    {
        $table = new GFNCore_Db_Schema_Table_Truncate();
        $table->name = $name;
        $table->execute();
    }
} 