<?php /*{$___fileHash}*/

/**
 * @package    {$___addOnTitle}
 * @version    {$___fileVersion}
 * @since      {$___fileCreateVersion}
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-{$___currentYear} GoodForNothing Labs <https://gfnlabs.com/>
 * @license    {$___licenseLink}
 * @link       {$___addOnLink}
 */
class GFNNotify_Extend_XenForo_DataWriter_User extends XFCP_GFNNotify_Extend_XenForo_DataWriter_User
{
    protected function _getFields()
    {
        $return = parent::_getFields();
        $return['xf_user_option']['show_notification_popup'] = array('type' => self::TYPE_BOOLEAN, 'default' => 1);
        return $return;
    }

    protected function _preSave()
    {
        parent::_preSave();

        if (XenForo_Application::isRegistered('show_notification_popup'))
        {
            $this->set('show_notification_popup', XenForo_Application::get('show_notification_popup'), 'xf_user_option');
        }
    }
} 