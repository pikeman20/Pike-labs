<?php /*eb8f171eea6848bb434feeb5ce53a272ff80db2e*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Db_Schema_Grammar_Table_Create extends GFNCore_Db_Schema_Grammar_Abstract
{
    protected $_table;

    public function __construct(GFNCore_Db_Schema_Table_Create $table)
    {
        $this->_table = $table;
    }

    public function parse()
    {
        $table = $this->_table;
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $table->name . "` (";

        if (empty($table->columns))
        {
            throw new GFNCore_Exception(new XenForo_Phrase('no_column_specified'));
        }

        $sql1 = array();

        /** @var GFNCore_Db_Schema_Column $column */
        foreach ($table->columns as $column)
        {
            $sql1[] = $column->parse();
        }

        $sql .= "\n  " . implode(",\n  ", $sql1);

        if (!empty($table->primary))
        {
            $sql .= ",\n  " . 'PRIMARY KEY `' . $table->primary['name'] . '` (`' . implode('`, `', $table->primary['columns']) . '`)';
        }

        if (!empty($table->unique))
        {
            $sql1 = array();

            foreach ($table->unique as $name => $columns)
            {
                $sql1[] = 'UNIQUE `' . $name . '` (`' . implode('`, `', $columns) . '`)';
            }

            $sql .= ",\n  " . implode(",\n  ", $sql1);
        }

        if (!empty($table->index))
        {
            $sql1 = array();

            foreach ($table->index as $name => $columns)
            {
                $sql1[] = 'INDEX `' . $name . '` (`' . implode('`, `', $columns) . '`)';
            }

            $sql .= ",\n  " . implode(",\n  ", $sql1);
        }

        $sql .= "\n) ENGINE=" . $table->engine . ' COLLATE=' . $table->collation;
        return $sql;
    }
} 