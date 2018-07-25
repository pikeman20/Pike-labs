<?php

class Brivium_Store_AlertHandler_Store extends XenForo_AlertHandler_Abstract
{
	/**
	 * @var Brivium_Store_Model_Product
	 */
	protected $_productModel = null;
	/**
	 * Fetches the content required by alerts.
	 *
	 * @param array $contentIds
	 * @param XenForo_Model_Alert $model Alert model invoking this
	 * @param integer $userId User ID the alerts are for
	 * @param array $viewingUser Information about the viewing user (keys: user_id, permission_combination_id, permissions)
	 *
	 * @return array
	 */
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		$products = $this->_getProductModel()->getProductsByIds($contentIds);
		return $products ;
	}
	protected function _prepareAlertBeforeAction(array $item, $content, array $viewingUser)
	{
		if($item['content_type']=='store'){
			if ($item['extra_data'])
			{
				$item['extra'] = unserialize($item['extra_data']);
				$item['product'] = !empty($item['extra']['product'])?$item['extra']['product']:array();
				$item['isGift'] = !empty($item['extra']['is_gift'])?$item['extra']['is_gift']:false;
				$item['giftedUser'] = !empty($item['extra']['gifted_user'])?$item['extra']['gifted_user']:array();
				$item['user'] = !empty($item['extra']['user'])?$item['extra']['user']:array();
				$item['giftType'] = !empty($item['extra']['gift_type'])?$item['extra']['gift_type']:'';
			}
		}
		unset($item['extra_data']);
		return $item;
	}
	protected function _getDefaultTemplateTitle($contentType, $action) {
		return 'BRS_alert_store_' . $action;
	}

	/**
	 * @return Brivium_Store_Model_Product
	 */
	protected function _getProductModel()
	{
		if (!$this->_productModel)
		{
			$this->_productModel = XenForo_Model::create('Brivium_Store_Model_Product');
		}

		return $this->_productModel;
	}
}