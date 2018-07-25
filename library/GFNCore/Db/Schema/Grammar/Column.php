<?php /*87473f7a04dbd4ba9b1379242870babfff0cb666*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Db_Schema_Grammar_Column extends GFNCore_Db_Schema_Grammar_Abstract
{
    protected $_column;

    public function __construct(GFNCore_Db_Schema_Column $column)
    {
        $this->_column = $column;
    }

    public function parse()
    {
        $column = $this->_column;
        $sql = '`' . $column->name . '` ' . $column->dataType;

        if (
            in_array($column->dataType, array(
                'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT',
                'CHAR', 'VARCHAR', 'BINARY', 'VARBINARY'
            ))
        )
        {
            $sql .= '(' . intval($column->length) . ')';
        }
        elseif ($column->dataType === 'ENUM')
        {
            $sql .= '(' . $this->quote($column->allowed) . ')';
        }
        elseif (
            in_array($column->dataType, array(
                'DECIMAL', 'FLOAT', 'DOUBLE',
            ))
        )
        {
            $sql .= '(' . intval($column->length) . ',' . intval($column->places) . ')';
        }

        if (
            in_array($column->dataType, array(
                'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT',
                'DECIMAL', 'FLOAT', 'DOUBLE'
            ))
        )
        {
            if ($column->unsigned)
            {
                $sql .= ' UNSIGNED';
            }
        }

        if ($column->nullable)
        {
            $sql .= ' NULL';
        }
        else
        {
            $sql .= ' NOT NULL';
        }

        if ((strpos($column->dataType, 'BLOB') === false) && (strpos($column->dataType, 'TEXT') === false))
        {
            if ($column->default === null)
            {
                if ($column->nullable)
                {
                    $sql .= ' DEFAULT NULL';
                }
            }
            elseif (is_bool($column->default))
            {
                $sql .= ' DEFAULT ' . intval($column->default);
            }
            else
            {
                $sql .= ' DEFAULT ' . $this->quote($column->default);
            }
        }

        if (strpos($column->dataType, 'INT') !== false)
        {
            if ($column->autoIncrement)
            {
                $sql .= ' AUTO_INCREMENT';
            }
        }

        if ($column->comments)
        {
            $sql .= ' COMMENT ' . $this->quote($column->comments);
        }

        return $sql;
    }
} 