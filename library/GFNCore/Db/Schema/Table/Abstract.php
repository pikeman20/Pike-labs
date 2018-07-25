<?php /*d9a72ea808ac6e263ad762fa0eb604b3f5b407fa*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
abstract class GFNCore_Db_Schema_Table_Abstract extends GFNCore_Db_Schema_Abstract
{
    public $name;
    public $engine = 'InnoDB';
    public $collation = 'utf8_general_ci';
    public $columns = array();

    public function tinyInteger($name, $length = 3)
    {
        $obj = $this->getColumnObj($name, 'TINYINT');
        $obj->length = $length;
        $this->columns[$name] = &$obj;
        return $obj;
    }

    public function smallInteger($name, $length = 5)
    {
        $obj = $this->getColumnObj($name, 'SMALLINT');
        $obj->length = $length;
        return $obj;
    }

    public function mediumInteger($name, $length = 7)
    {
        $obj = $this->getColumnObj($name, 'MEDIUMINT');
        $obj->length = $length;
        return $obj;
    }

    public function integer($name, $length = 10)
    {
        $obj = $this->getColumnObj($name, 'INT');
        $obj->length = $length;
        return $obj;
    }

    public function bigInteger($name, $length = 20)
    {
        $obj = $this->getColumnObj($name, 'BIGINT');
        $obj->length = $length;
        return $obj;
    }

    public function decimal($name, $length = 8, $places = 2)
    {
        $obj = $this->getColumnObj($name, 'DECIMAL');
        $obj->length = $length;
        $obj->places = $places;
        return $obj;
    }

    public function float($name, $length = 8, $places = 2)
    {
        $obj = $this->getColumnObj($name, 'FLOAT');
        $obj->length = $length;
        $obj->places = $places;
        return $obj;
    }

    public function double($name, $length = 8, $places = 2)
    {
        $obj = $this->getColumnObj($name, 'DOUBLE');
        $obj->length = $length;
        $obj->places = $places;
        return $obj;
    }

    public function boolean($name)
    {
        return $this->getColumnObj($name, 'BOOLEAN');
    }

    public function string($name, $length, $variable = true)
    {
        $obj = $this->getColumnObj($name, ($variable ? 'VARCHAR' : 'CHAR'));
        $obj->length = $length;
        return $obj;
    }

    public function tinyText($name)
    {
        return $this->getColumnObj($name, 'TINYTEXT');
    }

    public function text($name)
    {
        return $this->getColumnObj($name, 'TEXT');
    }

    public function mediumText($name)
    {
        return $this->getColumnObj($name, 'MEDIUMTEXT');
    }

    public function longText($name)
    {
        return $this->getColumnObj($name, 'LONGTEXT');
    }

    public function binary($name, $length, $variable = true)
    {
        $obj = $this->getColumnObj($name, ($variable ? 'VARBINARY' : 'BINARY'));
        $obj->length = $length;
        return $obj;
    }

    public function tinyBlob($name)
    {
        return $this->getColumnObj($name, 'TINYBLOB');
    }

    public function blob($name)
    {
        return $this->getColumnObj($name, 'BLOB');
    }

    public function mediumBlob($name)
    {
        return $this->getColumnObj($name, 'MEDIUMBLOB');
    }

    public function longBlob($name)
    {
        return $this->getColumnObj($name, 'LONGBLOB');
    }

    public function enum($name, array $values)
    {
        $obj = $this->getColumnObj($name, 'ENUM');
        $obj->allowed = $values;
        return $obj;
    }

    public function getColumnObj($name, $type)
    {
        $obj = new GFNCore_Db_Schema_Column();
        $this->columns[$name] = $obj;
        $obj->name = $name;
        $obj->dataType = $type;
        return $obj;
    }

    public $primary = null;
    public $unique = array();
    public $index = array();

    public function primary($columns, $name = null)
    {
        if (!is_array($columns))
        {
            $columns = array($columns);
        }

        if ($name === null)
        {
            $name = implode('_', $columns);
        }

        $this->primary = array(
            'name' => $name,
            'columns' => $columns
        );

        return $this;
    }

    public function unique($columns, $name = null)
    {
        if (!is_array($columns))
        {
            $columns = array($columns);
        }

        if ($name === null)
        {
            $name = implode('_', $columns);
        }

        $this->unique[$name] = $columns;
        return $this;
    }

    public function index($columns, $name = null)
    {
        if (!is_array($columns))
        {
            $columns = array($columns);
        }

        if ($name === null)
        {
            $name = implode('_', $columns);
        }

        $this->index[$name] = $columns;
        return $this;
    }

    public function execute()
    {
        XenForo_Application::getDb()->query((string) $this->parse());
    }
} 