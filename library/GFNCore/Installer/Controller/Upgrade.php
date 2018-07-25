<?php /*f9be9be0c7550551543e6247b64aab92d405e0dd*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Installer_Controller_Upgrade extends GFNCore_Installer_Controller_Abstract
{
    public function execute()
    {
        $installer = $this->_installer;
        $this->callHook('pre_upgrade');
        $this->checkXenForoVersion();

        if (!$this->doMasterRebuild())
        {
            $this->checkDependencies();

            if ($this->hasSqlData())
            {
                $versions = $this->getAvailableSqlVersions();

                foreach ($versions as $version)
                {
                    $class = $installer->getSqlDataClassPrefix() . $version;

                    /** @var GFNCore_Installer_Data_Abstract $obj */
                    $obj = new $class($installer, $this);
                    $this->callHook('upgrade_sql_pre_' . $version, array($obj));
                    $obj->install($installer->getExistingData()->version_id);
                    $this->callHook('upgrade_sql_post_' . $version, array($obj));
                }
            }
        }

        $this->callHook('post_upgrade');
    }

    public function doMasterRebuild()
    {
        return ($this->_installer->getExistingData()->version_id == $this->_installer->getNewData()->version_id);
    }
} 