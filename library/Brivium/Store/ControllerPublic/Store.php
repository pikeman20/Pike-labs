<?php

class Brivium_Store_ControllerPublic_Store extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		return $this->responseReroutePath('brs-categories');
	}

	public function actionProductPurchaseConfiguration()
	{
		$productModel = $this->_getProductModel();
		$productPurchaseModel = $this->_getProductPurchaseModel();
		$productPurchaseId = $this->_input->filterSingle('product_purchase_id', XenForo_Input::UINT);
		$purchasedRecord = $productPurchaseModel->getActiveProductPurchaseById($productPurchaseId);
		if (!$purchasedRecord)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_purchase_not_found'), 404);
		}
		$product = XenForo_Application::get('brsProducts')->$purchasedRecord['product_id'];

		if (!$product)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_not_found'), 404);
		}
		return $this->_processProductPurchaseConfiguration($product, $purchasedRecord, $product['product_type_id']);

	}

	protected function _processProductPurchaseConfiguration($product, $purchasedRecord, $productTypeId)
	{
		return $this->responseError(new XenForo_Phrase('BRS_you_cant_config_this_product'));
	}

	public function actionRemoveProductPurchase()
	{
		$productModel = $this->_getProductModel();
		$productPurchaseModel = $this->_getProductPurchaseModel();
		$productPurchaseId = $this->_input->filterSingle('product_purchase_id', XenForo_Input::UINT);
		$active = $productPurchaseModel->getActiveProductPurchaseById($productPurchaseId);

		$userId = XenForo_Visitor::getUserId();
		if (!$active || $active['user_id']!=$userId)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_purchase_not_found'), 404);
		}

		$product = XenForo_Application::get('brsProducts')->$active['product_id'];

		if (!$product)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_not_found'), 404);
		}
		if ($this->isConfirmedPost())
		{
			$productPurchaseModel->processExpiredProductPurchases(array($active),true);
			$redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);
			$redirect = ($redirect ? $redirect : $this->getDynamicRedirect());
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$redirect
			);
		}
		else // show confirmation dialog
		{
			$product = $productModel->prepareProduct($product);
			$viewParams = array(
				'product' => $product,
				'purchase' => $active,
				'redirect' => $this->getDynamicRedirect()
			);
			return $this->responseView('Brivium_Store_ViewPublic_Product_Purchase_Remove', 'BRS_product_purchase_remove_confirm', $viewParams);
		}
	}

	public function actionCurrentPurchaseSave(){
		$productPurchaseModel = $this->_getProductPurchaseModel();

		$purchaseId = $this->_input->filterSingle('currentPurchasedId', XenForo_Input::UINT);
		$productTypeId = $this->_input->filterSingle('product_type_id', XenForo_Input::STRING);
		$userId = XenForo_Visitor::getUserId();
		$productType = XenForo_Application::get('brsProductTypes')->$productTypeId;

		if($purchaseId && $productType){
			$productPurchaseModel->updateProductPurchaseCurrent($productTypeId,$purchaseId, $userId);
		}
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('store/purchased',null,array('product_type_id'=>$productTypeId))
		);
	}

	public function actionPurchased()
	{
		$this->_assertRegistrationRequired();

		$productPurchaseModel = $this->_getProductPurchaseModel();
		$userId = XenForo_Visitor::getUserId();
		$user = XenForo_Visitor::getInstance()->toArray();
		$productTypeId = $this->_input->filterSingle('product_type_id', XenForo_Input::STRING);

		$productType = XenForo_Application::get('brsProductTypes')->$productTypeId;
		$conditions = array(
			'active'	=> true,
			'user_id'	=> $userId,
		);

		if($productType){
			$conditions['product_type_id'] = $productTypeId;
		}
		$fetchOptions = array(
			'join'	=> Brivium_Store_Model_ProductPurchase::FETCH_GIFTED,
			'order'	=> 'last_purchase',
		);
		$productActives = $productPurchaseModel->getProductPurchaseRecords($conditions, $fetchOptions);
		$products = array();
		$productIds = array();
		foreach($productActives AS $purchase){
			$products[$purchase['product_id']] = XenForo_Application::get('brsProducts')->$purchase['product_id'];
			$products[$purchase['product_id']]['purchase'] = $purchase;
		}
		$productModel = $this->_getProductModel();
		$viewParams = array(
			'productTypes' => XenForo_Application::get('brsProductTypes')->getAll(),

			'productType' => $productType,
			'productTypeId' => $productTypeId,

			'canGift' 			=> $productModel->canGiftProduct(),
			'user' => $user,
			'products' => $products,
		);

		return $this->responseView('Brivium_Store_ViewPublic_Product_List', 'BRS_product_purchased', $viewParams);
	}

	public function actionGifted()
	{
		$this->_assertRegistrationRequired();

		$productPurchaseModel = $this->_getProductPurchaseModel();
		$userId = XenForo_Visitor::getUserId();
		$user = XenForo_Visitor::getInstance()->toArray();
		$productTypeId = $this->_input->filterSingle('product_type_id', XenForo_Input::STRING);

		$productType = XenForo_Application::get('brsProductTypes')->$productTypeId;
		$conditions = array(
			'active'			=> true,
			'gifted_user_id'	=> $userId,
		);

		if($productType){
			$conditions['product_type_id'] = $productTypeId;
		}
		$fetchOptions = array(
			//'join'	=> Brivium_Store_Model_ProductPurchase::JOIN_PRODUCT,
			'order'	=> 'last_purchase',
		);
		$productActives = $productPurchaseModel->getProductPurchaseRecords($conditions, $fetchOptions);
		$products = array();
		$productIds = array();
		foreach($productActives AS $purchase){
			$products[$purchase['product_id']] = XenForo_Application::get('brsProducts')->$purchase['product_id'];
			$products[$purchase['product_id']]['extraData'] = !empty($purchase['extra'])?unserialize($purchase['extra']):array();
			$products[$purchase['product_id']]['purchase'] = $purchase;
		}
		$productModel = $this->_getProductModel();
		$viewParams = array(
			'productTypes' => XenForo_Application::get('brsProductTypes')->getAll(),

			'productType' => $productType,
			'productTypeId' => $productTypeId,

			'user' => $user,
			'products' => $products,
		);

		return $this->responseView('Brivium_Store_ViewPublic_Product_List', 'BRS_product_gifted', $viewParams);
	}

	protected function _preDispatch($action)
	{
		if (!$this->_getStoreModel()->canViewStores($error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}
	}

	protected function _getStoreModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_Store');
	}
	/**
	 * Gets the category model.
	 *
	 * @return Brivium_Store_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_Category');
	}
	/**
	 * Gets the product model.
	 *
	 * @return Brivium_Store_Model_Product
	 */
	protected function _getProductModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_Product');
	}
	/**
	 * Gets the product purchase model.
	 *
	 * @return Brivium_Store_Model_ProductPurchase
	 */
	protected function _getProductPurchaseModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_ProductPurchase');
	}
	/**
	 * Gets the transaction model.
	 *
	 * @return Brivium_Store_Model_Transaction
	 */
	protected function _getTransactionModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_Transaction');
	}
}