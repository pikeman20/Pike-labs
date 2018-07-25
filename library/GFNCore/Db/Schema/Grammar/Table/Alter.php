<?php /*d8fc5973e12c1a155fb3662f4c8d8b1af3f67dca*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Db_Schema_Grammar_Table_Alter extends GFNCore_Db_Schema_Grammar_Abstract
{
    protected $_table;

    public function __construct(GFNCore_Db_Schema_Table_Alter $table)
    {
        $this->_table = $table;
    }

    public function parse()
    {
        $table = $this->_table;
        $sql = 'ALTER TABLE `' . $table->name . "`";

        if (!empty($table->rename))
        {
            $sql1 = true;
            $sql .= "\nRENAME TO `" . $table->rename . '`';
        }

        if (!empty($table->columns))
        {
            if (!empty($sql1))
            {
                $sql .= ',';
            }

            $sql1 = array();

            /** @var GFNCore_Db_Schema_Column $column */
            foreach ($table->columns as $column)
            {
                $sql1[] = 'ADD COLUMN ' . $column->parse() . ($column->after ? ' AFTER `' . $column->after . '`' : '');
            }

            $sql .= "\n" . implode(",\n", $sql1);
        }

        if (!empty($table->modify))
        {
            if (!empty($sql1))
            {
                $sql .= ',';
            }

            $sql1 = array();

            /** @var GFNCore_Db_Schema_Column $column */
            foreach ($table->modify as $name => $column)
            {
                if ($name == $column->name)
                {
                    $sql1[] = 'MODIFY COLUMN ' . $column->parse() . ($column->after ? ' AFTER ' . $column->after : '');
                }
                else
                {
                    $sql1[] = 'CHANGE COLUMN `' . $name . '` ' . $column->parse()  . ($column->after ? ' AFTER ' . $column->after : '');
                }
            }

            $sql .= "\n" . implode(",\n", $sql1);
        }

        if (!empty($table->drop))
        {
            if (!empty($sql1))
            {
                $sql .= ',';
            }

            $sql1 = array();

            foreach ($table->drop as $name => $null)
            {
                $sql1[] = 'DROP COLUMN `' . $name . '`';
            }

            $sql .= "\n" . implode(",\n", $sql1);
        }

        if ($table->dropPrimary)
        {
            if (!empty($sql1))
            {
                $sql .= ',';
            }

            $sql .= "\nDROP PRIMARY KEY";
        }

        if ($table->primary)
        {
            if (!empty($sql1))
            {
                $sql .= ',';
            }

            if (!$table->dropPrimary)
            {
                $sql .= "\nDROP PRIMARY KEY,";
            }

            $sql .= "\nADD PRIMARY KEY `" . $table->primary['name'] . '` (`' . implode('`, `', $table->primary['columns']) . '`)';
        }

        if (!empty($table->dropIndex))
        {
            if (!empty($sql1))
            {
                $sql .= ',';
            }

            $sql1 = array();

            foreach ($table->dropIndex as $name => $null)
            {
                $sql1[] = 'DROP INDEX `' . $name . '`';
            }

            $sql .= "\n" . implode(",\n", $sql1);
        }

        if (!empty($table->unique))
        {
            if (!empty($sql1))
            {
                $sql .= ',';
            }

            $sql1 = array();

            foreach ($table->unique as $name => $columns)
            {
                $sql1[] = 'ADD UNIQUE `' . $name . '` (`' . implode('`, `', $columns) . '`)';
            }

            $sql .= "\n" . implode(",\n", $sql1);
        }

        if (!empty($table->index))
        {
            if (!empty($sql1))
            {
                $sql .= ',';
            }

            $sql1 = array();

            foreach ($table->index as $name => $columns)
            {
                $sql1[] = 'ADD INDEX `' . $name . '` (`' . implode('`, `', $columns) . '`)';
            }

            $sql .= "\n" . implode(",\n", $sql1);
        }

        return $sql;
    }
} 