<?php /*a153f420c468ee89196790e582996a1e38d4ca7d*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Installer_Controller_Uninstall extends GFNCore_Installer_Controller_Abstract
{
    public function execute()
    {
        $installer = $this->_installer;
        $this->callHook('pre_uninstall');

        if ($this->hasSqlData())
        {
            $versions = $this->getAvailableSqlVersions();

            foreach ($versions as $version)
            {
                $class = $installer->getSqlDataClassPrefix() . $version;

                /** @var GFNCore_Installer_Data_Abstract $obj */
                $obj = new $class($installer, $this);
                $this->callHook('uninstall_sql_pre_' . $version, array($obj));
                $obj->uninstall();
                $this->callHook('uninstall_sql_post_' . $version, array($obj));
            }
        }

        $this->callHook('post_uninstall');
    }
} 