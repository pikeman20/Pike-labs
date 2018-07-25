<?php /*9773c47671612bed6c4c122bc15e1485d824d84c*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Db_Schema_Column extends GFNCore_Db_Schema_Abstract
{
    public $name;
    public $dataType;
    public $length;
    public $default = null;
    public $nullable = false;
    public $comments = '';

    public $allowed;
    public $places;
    public $unsigned = false;
    public $autoIncrement = false;
    public $after;

    public function parse()
    {
        return new GFNCore_Db_Schema_Grammar_Column($this);
    }

    public function __call($method, array $parameters)
    {
        if (property_exists($this, $method))
        {
            $this->{$method} = reset($parameters);
        }

        return $this;
    }
} 