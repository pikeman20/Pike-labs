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
class GFNNotify_Model_Notify extends XenForo_Model
{
    public function markAsRead(array $alert)
    {
        $hash = hash('sha1', serialize(array(
            $alert['alert_id'], $alert['alerted_user_id'], $alert['content_type'], $alert['content_id'],
            $alert['action'], $alert['event_date'], $alert['view_date']
        )));

        $stmt = $this->_getDb()->query(
            'INSERT IGNORE INTO gfnnotify_notification
              (notification_hash)
            VALUES
              (?)', $hash
        );

        if ($stmt->rowCount() > 0)
        {
            return true;
        }

        return false;
    }

    public function markAlertRead(array $alert)
    {
        $db = $this->_getDb();

        $stmt = $db->query(
            'UPDATE xf_user_alert
            SET view_date = ?
            WHERE alert_id = ?
            AND view_date = 0', array(XenForo_Application::$time, $alert['alert_id'])
        );

        if ($stmt->rowCount() < 1)
        {
            return false;
        }

        $userId = $alert['alerted_user_id'];

        $db->query(
            'UPDATE xf_user
            SET alerts_unread = alerts_unread - 1
            WHERE user_id = ?
            AND alerts_unread > 0', $userId
        );

        // not sure if the right approach... just going with the 'XenForo' way.
        $visitor = XenForo_Visitor::getInstance();
        if ($userId == $visitor['user_id'] && $visitor['alerts_unread'] > 0)
        {
            $visitor['alerts_unread'] = $visitor['alerts_unread'] - 1;
        }

        return true;
    }
} 