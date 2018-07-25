<?php /*1fc346f1d26b5a466b79792f58bca29d389da0d7*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Db_Schema_Table_Create extends GFNCore_Db_Schema_Table_Abstract
{
    public function parse()
    {
        return new GFNCore_Db_Schema_Grammar_Table_Create($this);
    }
} 