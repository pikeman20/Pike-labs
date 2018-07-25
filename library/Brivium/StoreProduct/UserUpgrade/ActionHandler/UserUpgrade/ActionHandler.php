<?php

/**
 *
 * @package Brivium_Credits
 */
class Brivium_StoreProduct_UserUpgrade_ActionHandler_UserUpgrade_ActionHandler extends Brivium_Credits_ActionHandler_Abstract
{
	protected $_addOnId = 'BRS_UserUpgrade';
	protected $_addOnTitle = 'Brivium - Store Product User Upgrades';

	protected $_displayOrder = 1000;
	protected $_editTemplate = 'BRC_action_edit_template_user';

 	public function getActionId()
 	{
 		return 'BRS_UserUpgrade';
 	}

	public function getActionTitlePhrase()
 	{
 		return 'BRC_action_BRS_UserUpgrade';
 	}

	public function getDescriptionPhrase()
 	{
 		return 'BRC_action_BRS_UserUpgrade_explain';
 	}
}