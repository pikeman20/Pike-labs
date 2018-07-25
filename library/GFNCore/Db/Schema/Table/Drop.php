<?php /*7b1a736af9a5ea4da91bfa976c1973283035f3c0*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Db_Schema_Table_Drop extends GFNCore_Db_Schema_Table_Abstract
{
    public function parse()
    {
        return 'DROP TABLE IF EXISTS `' . $this->name . '`';
    }
} 