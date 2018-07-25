<?php

/**
 * Model for Store.
 *
 * @package Brivium_Store
 */
class Brivium_Store_Model_Store extends XenForo_Model
{
	public function canViewStores(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		return XenForo_Permission::hasPermission($viewingUser['permissions'], 'BR_storePermission', 'view');
	}

}

?>