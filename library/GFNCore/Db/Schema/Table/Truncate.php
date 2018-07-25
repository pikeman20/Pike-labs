<?php /*46841964f5c826c804a90d5a21529e20f3572602*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Db_Schema_Table_Truncate extends GFNCore_Db_Schema_Table_Abstract
{
    public function parse()
    {
        return 'TRUNCATE TABLE `' . $this->name . '`';
    }
} 