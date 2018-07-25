<?php

/**
* Data writer for conversation masters.
*
* @package XenForo_Conversation
*/

class Brivium_Credits_ActionHandler_ConversationGetReply_DataWriter_ConversationMaster extends XFCP_Brivium_Credits_ActionHandler_ConversationGetReply_DataWriter_ConversationMaster
{
	/**
	 * Post-save handling.
	 */
	protected function _postSave()
	{
		if (isset($GLOBALS['BRC_ConversationGetReply_replyUserId']) && $GLOBALS['BRC_ReplyConversation_replyUserId']!=$this->get('user_id')) {
			$dataCredit = array(
				'content_id' 	=>	$this->get('conversation_id'),
				'content_type'	=>	'conversation',
				'user_action_id'	=>	$GLOBALS['BRC_ConversationGetReply_replyUserId'],
			);
			$this->getModelFromCache('Brivium_Credits_Model_Credit')->updateUserCredit('conversationGetReply', $this->get('user_id'), $dataCredit);
			unset($GLOBALS['BRC_ConversationGetReply_replyUserId']);
		}
		return parent::_postSave();
	}
}