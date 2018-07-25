<?php /*11458e3ddd5816492ac7f9f292325d8f9bfdf291*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 * @include    ./
 */
class GFNCore_Db_Schema
{
    public function table()
    {
        return new GFNCore_Db_Schema_Table();
    }
} 