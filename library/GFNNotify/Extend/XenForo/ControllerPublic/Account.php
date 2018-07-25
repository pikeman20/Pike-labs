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
class GFNNotify_Extend_XenForo_ControllerPublic_Account extends XFCP_GFNNotify_Extend_XenForo_ControllerPublic_Account
{
    public function actionPreferencesSave()
    {
        XenForo_Application::set('show_notification_popup', $this->_input->filterSingle('show_notification_popup', XenForo_Input::BOOLEAN));
        return parent::actionPreferencesSave();
    }
} 