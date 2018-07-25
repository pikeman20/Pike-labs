<?php /*84743037252bfbd5cf57a6a5127a551969e1f5f3*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Installer_Controller_Install extends GFNCore_Installer_Controller_Abstract
{
    public function execute()
    {
        $installer = $this->_installer;
        $this->callHook('pre_install');
        $this->checkXenForoVersion();
        $this->checkDependencies();

        if ($this->hasSqlData())
        {
            $versions = $this->getAvailableSqlVersions();

            foreach ($versions as $version)
            {
                $class = $installer->getSqlDataClassPrefix() . $version;

                /** @var GFNCore_Installer_Data_Abstract $obj */
                $obj = new $class();
                $this->callHook('install_sql_pre_' . $version, array($obj));
                $obj->install(false);
                $this->callHook('install_sql_post_' . $version, array($obj));
            }
        }

        $this->callHook('post_install');
    }
} 