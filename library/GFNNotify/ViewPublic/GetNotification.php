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
class GFNNotify_ViewPublic_GetNotification extends XenForo_ViewPublic_Base
{
    public function renderJson()
    {
        $alertHandlers = $this->_params['alertHandlers'];
        $return = array();

        foreach ($this->_params['alerts'] as $item)
        {
            /** @var XenForo_AlertHandler_Abstract $handler */
            $handler = @$alertHandlers[$item['content_type']];

            if (!$handler)
            {
                continue;
            }

            $return[] = $this->createTemplateObject('gfnnotify_item', array(
                'user' => $item['user'],
                'content' => $handler->renderHtml($item, $this),
                'alertId' => $item['alert_id']
            ))->render();
        }

        return array('notifications' => $return);
    }
} 