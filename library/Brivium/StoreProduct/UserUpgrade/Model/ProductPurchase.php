<?php

/**
 * Model for ProductPurchase.
 *
 * @package Brivium_Store
 */
class Brivium_StoreProduct_UserUpgrade_Model_ProductPurchase extends XFCP_Brivium_StoreProduct_UserUpgrade_Model_ProductPurchase
{
	public function getCreditActionId($productTypeId)
	{
		if($productTypeId=='UserUpgrade'){
			return 'BRS_UserUpgrade';
		}
		return parent::getCreditActionId($productTypeId);
	}
	protected function _processProductChange($user, array $product, $productTypeId, $existingPurchased = null){
		if($productTypeId=='UserUpgrade')
		{
			$this->_getUserModel()->addUserGroupChange(
				$user['user_id'], 'userUpgradeProduct-' . $product['product_id'], $product['extra_group_ids']
			);

			return true;
		}
		return parent::_processProductChange($user, $product, $productTypeId);
	}
	
	protected function _removeProductChange($purchase)
	{
		if(!empty($purchase['product_id']) && $purchase['product_type_id'] == 'UserUpgrade')
		{
			$this->_getUserModel()->removeUserGroupChange(
				$purchase['user_id'], 'userUpgradeProduct-' . $purchase['product_id']
			);
		}
		return parent::_removeProductChange($purchase);
	}
	
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}
}

?>