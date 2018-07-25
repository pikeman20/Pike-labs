<?php /*af854ef16b29b019194cbbe9ec60c71d6706c28c*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
abstract class GFNCore_Installer_Data_Abstract
{
    protected $_schema;

    protected $_db;

    public function __construct()
    {
        $this->_schema = new GFNCore_Db_Schema();
        $this->_db = XenForo_Application::getDb();
    }

    /**
     * @return GFNCore_Db_Schema_Table
     */
    public function table()
    {
        return $this->_schema->table();
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function db()
    {
        return $this->_db;
    }

    abstract public function install($isUpgrade = false);

    abstract public function uninstall();
} 