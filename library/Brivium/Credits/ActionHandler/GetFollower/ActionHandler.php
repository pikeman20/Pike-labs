<?php

/**
 *
 * @package Brivium_Credits
 */
class Brivium_Credits_ActionHandler_GetFollower_ActionHandler extends Brivium_Credits_ActionHandler_Abstract
{
	protected $_editTemplate = 'BRC_action_edit_template_user';
	protected $_displayOrder = 145;
	protected $_extendedClasses = array(
		'load_class_datawriter' => array(
			'XenForo_DataWriter_Follower' => 'Brivium_Credits_ActionHandler_GetFollower_DataWriter_Follower'
		),
	);

 	public function getActionId()
 	{
 		return 'getFollower';
 	}

	public function getActionTitlePhrase()
 	{
 		return 'BRC_action_getFollower';
 	}

	public function getDescriptionPhrase()
 	{
 		return 'BRC_action_getFollower_description';
 	}
}