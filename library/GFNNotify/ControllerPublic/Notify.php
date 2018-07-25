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
class GFNNotify_ControllerPublic_Notify extends XenForo_ControllerPublic_Abstract
{
    protected function _preDispatch($action)
    {
        $this->_routeMatch->setResponseType('json');

        if (!$this->_request->isXmlHttpRequest())
        {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getUserId())
        {
            throw $this->getNoPermissionResponseException();
        }
    }

    public function actionMarkRead()
    {
        $alertId = $this->_input->filterSingle('alert_id', XenForo_Input::UINT);

        if ($alertId)
        {
            /** @var XenForo_Model_Alert $alertModel */
            $alertModel = $this->getModelFromCache('XenForo_Model_Alert');
            $alert = $alertModel->getAlertById($alertId);

            if ($alert)
            {
                /** @var GFNNotify_Model_Notify $notifyModel */
                $notifyModel = $this->getModelFromCache('GFNNotify_Model_Notify');
                $notifyModel->markAlertRead($alert);
            }
        }

        return $this->responseView();
    }

    public function actionGetNotifications()
    {
        /** @var GFNNotify_Model_Notify $notifyModel */
        $notifyModel = $this->getModelFromCache('GFNNotify_Model_Notify');
        /** @var XenForo_Model_Alert $alertModel */
        $alertModel = $this->getModelFromCache('XenForo_Model_Alert');
        /** @var XenForo_Model_Conversation $convoModel */
        $convoModel = $this->getModelFromCache('XenForo_Model_Conversation');
        /** @var XenForo_Model_User $userModel */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        $alerts = $alertModel->getAlertsForUser(
            XenForo_Visitor::getUserId(),
            XenForo_Model_Alert::FETCH_MODE_ALL
        );

        $convos = $convoModel->getConversationsForUser(
            XenForo_Visitor::getUserId(),
            array(
                'is_unread' => true
            )
        );

        $alertHandlers = $alerts['alertHandlers'];
        $alerts = $alerts['alerts'];

        if (!$alerts)
        {
            $alerts = array();
        }

        foreach ($convos as $convo)
        {
            if ($convo['first_message_id'] == $convo['last_message_id'])
            {
                $action = 'insert';
            }
            else
            {
                $action = 'reply';
            }

            if ($convo['last_message_user_id'] == $convo['user_id'])
            {
                $user = array(
                    'user_id' => $convo['user_id'],
                    'username' => $convo['username'],
                    'gender' => $convo['gender'],
                    'gravatar' => $convo['gravatar'],
                    'avatar_date' => $convo['avatar_date']
                );
            }
            else
            {
                $user = $userModel->getUserById($convo['last_message_user_id']);

                $user = array(
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'gender' => $user['gender'],
                    'gravatar' => $user['gravatar'],
                    'avatar_date' => $user['avatar_date']
                );
            }

            if (!empty($action))
            {
                array_push($alerts, array(
                    'alert_id' => 0,
                    'alerted_user_id' => XenForo_Visitor::getUserId(),
                    'content_type' => 'conversation',
                    'content_id' => $convo['conversation_id'],
                    'action' => $action,
                    'event_date' => $convo['last_message_date'],
                    'view_date' => 0,
                    'content' => $convo,
                    'user' => $user,
                    'extra' => array(
                        'message_id' => $convo['last_message_id']
                    )
                ));
            }
        }

        $alertHandlers['conversation'] = new XenForo_AlertHandler_Conversation();

        if ($alerts)
        {
            foreach ($alerts as $i => $alert)
            {
                if (($alert['view_date'] > 0) || !$notifyModel->markAsRead($alert))
                {
                    unset($alerts[$i]);
                }
            }
        }

        $viewParams = array(
            'alertHandlers' => $alertHandlers,
            'alerts' => $this->_orderAlerts($alerts),
            'convos' => $convos,
        );

        return $this->responseView('GFNNotify_ViewPublic_GetNotification', '', $viewParams);
    }

    protected function _orderAlerts(array $alerts)
    {
        $orders = array();
        $return = array();

        foreach ($alerts as $i => $alert)
        {
            $orders[$i] = $alert['event_date'];
        }

        arsort($orders, SORT_NUMERIC);

        foreach ($orders as $i => $null)
        {
            $return[] = $alerts[$i];
        }

        return $return;
    }
} 