<?php /*b5ba2c626520ba032cbe39ed30e01c0d0eaf7cd5*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
abstract class GFNCore_Db_Schema_Abstract
{
    abstract public function parse();

    public function __toString()
    {
        return $this->parse();
    }
} 