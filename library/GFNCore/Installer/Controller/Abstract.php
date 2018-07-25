<?php /*bd691c6ce157fc7472f6bc1c7ea8531969ed7ab4*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Alpha 4
 * @since      1.0.0 Alpha 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2015 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 * @require    ./
 */
abstract class GFNCore_Installer_Controller_Abstract
{
    /**
     * @var GFNCore_Installer_Abstract
     */
    protected $_installer;

    public function __construct(GFNCore_Installer_Abstract $installer)
    {
        @set_time_limit(0);
        $this->_installer = $installer;
        $installer->listen($this);
    }

    abstract public function execute();

    protected $_minXenForoVersion = 1030070;

    public function setMinXenForoVersion($version)
    {
        $this->_minXenForoVersion = intval($version);
    }

    protected $_hooks = array();

    public function setHook($name, $callback, array $args = array())
    {
        if (is_callable($callback))
        {
            $this->_hooks[$name] = array(
                'callback' => $callback,
                'args' => array_values($args) ?: array()
            );
        }
    }

    public function unsetHook($name)
    {
        unset($this->_hooks[$name]);
    }

    public function callHook($name, array $args = array())
    {
        if (isset($this->_hooks[$name]))
        {
            $hook = $this->_hooks[$name];
            call_user_func_array($hook['callback'], $hook['args'] + $args);
        }
    }

    public function checkXenForoVersion()
    {
        $this->callHook('xenforo_version_pre_check');

        if (XenForo_Application::$versionId < $this->_minXenForoVersion)
        {
            throw $this->exception('minimum_xenforo_version_error', array(
                'required' => GFNCore_Helper_Version::i2s($this->_minXenForoVersion),
                'current' => XenForo_Application::$version
            ));
        }

        $this->callHook('xenforo_version_post_check');
    }

    public function checkDependencies()
    {
        $this->callHook('dependency_pre_check');

        $dependencies = $this->_installer->getDependencies();
        if ($dependencies)
        {
            /** @var XenForo_Model_AddOn $addOnModel */
            $addOnModel = XenForo_Model::create('XenForo_Model_AddOn');
            $notInstalled = $outOfDate = array();

            foreach ($dependencies as $addOnId => $version)
            {
                $addOn = $addOnModel->getAddOnById($addOnId);
                if (!$addOn)
                {
                    $notInstalled[] = $addOnId;
                    continue;
                }

                if ($addOn['version_id'] < $version)
                {
                    $outOfDate[] = $addOnId;
                }
            }

            $this->callHook('dependency_mismatch', array(&$notInstalled, &$outOfDate));

            if ($notInstalled)
            {
                throw $this->exception('following_required_addons_not_installed', array(
                    'addons' => implode("', '", $notInstalled)
                ));
            }

            if ($outOfDate)
            {
                throw $this->exception('following_addons_are_out_of_date', array(
                    'addons' => implode("', '", $outOfDate)
                ));
            }
        }

        $this->callHook('dependency_post_check');
    }

    public function hasSqlData()
    {
        return ($this->_installer->getSqlDataPath());
    }

    public function getAvailableSqlVersions()
    {
        $installer = $this->_installer;
        $handle = opendir($installer->getSqlDataPath());
        if (!$handle)
        {
            throw $this->exception('unable_to_read_directory', array('directory' => $installer->getSqlDataPath()));
        }

        $start = $installer->isUpgrade() ? $installer->getExistingData()->version_id : 0;
        $end = $installer->isUninstall() ? $installer->getExistingData()->version_id : $installer->getNewData()->version_id;
        $return = array();

        while (($file = readdir($handle)) !== false)
        {
            if (in_array($file, array('.', '..')))
            {
                continue;
            }

            if (substr($file, -4) != '.php')
            {
                continue;
            }

            $version = intval(substr($file, 0, -4));
            if (!$version)
            {
                continue;
            }

            if ($version <= $start)
            {
                continue;
            }

            if ($version > $end)
            {
                continue;
            }

            $return[] = $version;
        }

        if ($installer->isUninstall())
        {
            rsort($return, SORT_NUMERIC);
        }
        else
        {
            sort($return, SORT_NUMERIC);
        }

        return $return;
    }

    public function exception($message, array $params = array())
    {
        return new GFNCore_Exception(new XenForo_Phrase($message, $params), true);
    }
} 