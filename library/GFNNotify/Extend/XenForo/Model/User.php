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
class GFNNotify_Extend_XenForo_Model_User extends XFCP_GFNNotify_Extend_XenForo_Model_User
{
    public function getVisitingGuestUser()
    {
        return parent::getVisitingGuestUser() + array('show_notification_popup' => 0);
    }
} 