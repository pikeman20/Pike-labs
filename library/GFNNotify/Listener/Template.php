<?php /*{$___fileHash}*/

/**
 * @package    {$___addOnTitle}
 * @version    {$___fileVersion}
 * @since      {$___fileCreateVersion}
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-{$___currentYear} GoodForNothing Labs <http://gfnlabs.com/>
 * @license    {$___licenseLink}
 * @link       {$___addOnLink}
 */
class GFNNotify_Listener_Template
{
    public static function createPublicContainerTemplate(&$templateName, array &$params, XenForo_Template_Abstract $template)
    {
        if (!($template instanceof XenForo_Template_Public))
        {
            return;
        }

        if (XenForo_Visitor::getInstance()->get('show_notification_popup'))
        {
            if (!isset($params['head']) || !is_array($params['head']))
            {
                $params['head'] = array();
            }

            $params['head']['notifyCssKeyframes'] = '<link rel="stylesheet" href="styles/default/gfnnotify/keyframes.min.css" />';
        }
    }
} 