<?php

class Brivium_Store_ControllerPublic_Product extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		return $this->responseReroutePath('brs-categories');
	}

	public function actionPurchase()
	{
		$productModel = $this->_getProductModel();
		$productPurchaseModel = $this->_getProductPurchaseModel();
		$productId = $this->_input->filterSingle('product_id', XenForo_Input::UINT);

		$product = XenForo_Application::get('brsProducts')->$productId;
		if (!$product)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_not_found'), 404);
		}

		if(!$productModel->canPurchaseProduct()){
			return $this->responseError(new XenForo_Phrase('do_not_have_permission'));
		}
		$userId = XenForo_Visitor::getUserId();
		$errorString = '';
		$purchased = $productModel->canBuyProduct($userId, $product, $errorString);
		if($errorString)return $this->responseError($errorString);

		$visitor = XenForo_Visitor::getInstance()->toArray();
		$remained = $productPurchaseModel->checkUserMoney($userId,$product,$visitor);
		if($remained < 0) {
			return $this->responseError(new XenForo_Phrase('BRS_not_enough_money_to_purchase',array('money' => Brivium_Store_EventListeners_Helpers::helperStoreCostFormat($product['cost_amount'],$product['product_id'],$product['money_type'],$product['currency_id'],$product))));
		}

		if ($this->isConfirmedPost())
		{
			$recurring = $this->_input->filterSingle('recurring', XenForo_Input::UINT);
			$errorString = '';
			$productPurchaseModel->processPurchase($userId, $product, $recurring,0,$errorString);
			if($errorString){
				return $this->responseError($errorString);
			}
			$productPurchaseModel->rebuildProductPurchaseCache($product['product_type_id']);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('store/purchased',null,array('product_type_id'=>$product['product_type_id']))
			);
		}
		else // show confirmation dialog
		{
			$product = $productModel->prepareProduct($product);
			$viewParams = array(
				'remained' => $remained,
				'product' => $product,
				'purchased' => $purchased,
			);
			return $this->responseView('Brivium_Store_ViewPublic_Product_Purchase', 'BRS_product_purchase_confirm', $viewParams);
		}
	}

	public function actionGift()
	{
		$productModel = $this->_getProductModel();
		$productPurchaseModel = $this->_getProductPurchaseModel();
		$productId = $this->_input->filterSingle('product_id', XenForo_Input::UINT);

		$product = XenForo_Application::get('brsProducts')->$productId;
		if (!$product)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_not_found'), 404);
		}

		if(!$productModel->canGiftProduct()){
			return $this->responseError(new XenForo_Phrase('do_not_have_permission'));
		}
		if(!$product['can_gift']){
			return $this->responseError(new XenForo_Phrase('BRS_you_cant_gift_this_product'));
		}
		$userId = XenForo_Visitor::getUserId();
		$visitor = XenForo_Visitor::getInstance()->toArray();
		if( !$remained = $productPurchaseModel->checkUserMoney($userId,$product,$visitor)) {
			return $this->responseError(new XenForo_Phrase('BRS_not_enough_money_to_purchase',array('money' => Brivium_Store_EventListeners_Helpers::helperStoreCostFormat($product['cost_amount'],$product['product_id'],$product['money_type'],$product['currency_id'],$product))));
		}

		if ($this->isConfirmedPost())
		{
			$receiverName = $this->_input->filterSingle('receiver', XenForo_Input::STRING);
			$receiver = $this->getModelFromCache('XenForo_Model_User')->getUserByName($receiverName);
			if (empty($receiver)) {
				return $this->responseError(new XenForo_Phrase('requested_user_not_found'));
			}else if ($receiver['user_id'] == $visitor['user_id']) {
				return $this->responseError(new XenForo_Phrase('BRS_error_gift_self'));
			}
			$productModel->canSendGiftProduct($userId, $receiver['user_id'], $product, $errorString);
			if($errorString)return $this->responseError($errorString);
			$recurring = $this->_input->filterSingle('recurring', XenForo_Input::UINT);
			$errorString = '';
			$productPurchaseModel->processPurchase($receiver['user_id'], $product, $recurring, $userId, $errorString, $recurring?'gifted_user':'');
			if($errorString)return $this->responseError($errorString);
			$productPurchaseModel->rebuildProductPurchaseCache($product['product_type_id']);
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('store/gifted')
			);
		}
		else // show confirmation dialog
		{
			$product = $productModel->prepareProduct($product);
			$viewParams = array(
				'remained' => $remained,
				'product' => $product,
				'redirect' => $this->getDynamicRedirect()
			);
			return $this->responseView('Brivium_Store_ViewPublic_Product_Gift', 'BRS_product_purchase_gift_confirm', $viewParams);
		}
	}

	public function actionSubscribe()
	{
		$productModel = $this->_getProductModel();
		$productPurchaseModel = $this->_getProductPurchaseModel();
		//$productId = $this->_input->filterSingle('product_id', XenForo_Input::UINT);
		$productPurchaseId = $this->_input->filterSingle('product_purchase_id', XenForo_Input::UINT);
		$gifted = $this->_input->filterSingle('gifted', XenForo_Input::UINT);
		$subscribe = $this->_input->filterSingle('subscribe', XenForo_Input::UINT);
		$active = $productPurchaseModel->getActiveProductPurchaseById($productPurchaseId);

		$userId = XenForo_Visitor::getUserId();

		if($gifted){
			if (!$active || $active['gifted_user_id']!=$userId)
			{
				return $this->responseError(new XenForo_Phrase('BRS_you_did_not_gift_this_product'));
			}
		}else{
			if (!$active || $active['user_id']!=$userId)
			{
				return $this->responseError(new XenForo_Phrase('BRS_you_did_not_purchase_this_product'));
			}
		}

		$product = XenForo_Application::get('brsProducts')->$active['product_id'];

		if (!$product)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_not_found'), 404);
		}

		if ( !$product['length_unit'] || !$product['recurring']) {
			return $this->responseError(new XenForo_Phrase('BRS_this_product_does_not_allow_recurring'));
		}
		$activeExtra = !empty($active['extra'])?unserialize($active['extra']):array();
		if ($this->isConfirmedPost())
		{
			if($subscribe && $gifted){
				$activeExtra['recurring_type']='gifted_user';
			}
			if(!$subscribe){
				$activeExtra['recurring_type']='';
			}
			$productPurchaseModel->processSubcribe($active['user_id'], $active['product_purchase_id'], $subscribe?1:0,	serialize($activeExtra));
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
				'product' 		=> $product,
				'activeExtra' 	=> $activeExtra,
				'purchase' 		=> $active,
				'gifted' 		=> $gifted,
				'redirect' 		=> $this->getDynamicRedirect()
			);
			return $this->responseView('Brivium_Store_ViewPublic_Product_Buy', 'BRS_product_subscribe_confirm', $viewParams);
		}
	}

	protected function _preDispatch($action)
	{
		if (!$this->_getStoreModel()->canViewStores($error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}
	}

	protected function _getProductHelper()
	{
		return $this->getHelper('Brivium_Store_ControllerHelper_Product');
	}

	/**
	 * Gets the category model.
	 *
	 * @return Brivium_Store_Model_Category
	 */
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
}