<?php

class Brivium_StoreProduct_UserUpgrade_EventListeners_Listener
{
	public static function brcActionHandler(array &$actions)
	{
		$actions['BRS_UserUpgrade'] = 'Brivium_StoreProduct_UserUpgrade_ActionHandler_UserUpgrade_ActionHandler';
	}
}