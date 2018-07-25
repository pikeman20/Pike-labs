<?php

class Brivium_Store_ControllerPublic_Category extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$categoryId = $this->_input->filterSingle('product_category_id', XenForo_Input::UINT);
		$productTypeId = $this->_input->filterSingle('product_type_id', XenForo_Input::STRING);

		$productModel = $this->_getProductModel();
		$categoryModel = $this->_getCategoryModel();

		$defaultOrder = 'product_date';
		$defaultOrderDirection = 'desc';

		$order = $this->_input->filterSingle('order', XenForo_Input::STRING, array('default' => $defaultOrder));
		$orderDirection = $this->_input->filterSingle('direction', XenForo_Input::STRING, array('default' => $defaultOrderDirection));


		$category = $categoryModel->getCategoryById($categoryId);

		$categoryList = $categoryModel->getCategoryDataForListDisplay(false);
		//$categoryList = $categoryModel->applyRecursiveCountsToGrouped($categoryList);
		$categories = isset($categoryList[0]) ? $categoryList[0] : array();


		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$perPage = XenForo_Application::get('options')->BRS_productsPerPage;

		$criteria = array(
			'product_type_id'	=> $productTypeId,
			'product_category_id'	=> $categoryId,
			'display_in_list'	=> true,
			'sticky' => false,
		);
		$stickyCriteria = array(
			'product_type_id'	=> $productTypeId,
			'product_category_id'	=> $categoryId,
			'display_in_list'	=> true,
			'sticky'	=> true,
		);
		$totalProducts = $productModel->countProducts($criteria);

		$this->canonicalizePageNumber($page, $perPage, $totalProducts, 'store');

		$fetchOptions = array(
			'perPage' => $perPage,
			'page' => $page,
			'order' => $order,
			'direction' => $orderDirection,
			'purchaseUserId' => XenForo_Visitor::getUserId(),
			'join' => Brivium_Store_Model_Product::FETCH_FULL,
		);
		$stickyFetchOptions = array(
			'order' => $order,
			'direction' => $orderDirection,
			'purchaseUserId' => XenForo_Visitor::getUserId(),
			'join' => Brivium_Store_Model_Product::FETCH_FULL,
		);
		$stickyProducts = $productModel->getProducts($stickyCriteria, $stickyFetchOptions);
		$stickyProducts = $productModel->prepareProducts($stickyProducts);

		$products = $productModel->getProducts($criteria, $fetchOptions);
		$products = $productModel->prepareProducts($products);

		$pageNavParams = array(
			'order' => ($order != $defaultOrder ? $order : false),
			'direction' => ($orderDirection != $defaultOrderDirection ? $orderDirection : false),
		);
		$viewParams = array(
			'category' 			=> $category,
			'productTypeId' 	=> $productTypeId,

			'categories' 		=> $categoryModel->prepareCategories($categories),

			'productTypes' 		=> XenForo_Application::get('brsProductTypes')->getAll(),
			'products'		 	=> $products,
			'stickyProducts' 	=> $stickyProducts,
			'totalProducts' 	=> $totalProducts,

			'page' 				=> $page,
			'perPage'			=> $perPage,
			'pageNavParams' 	=> $pageNavParams,

			'canPurchase' 		=> $productModel->canPurchaseProduct(),
			'canGift' 			=> $productModel->canGiftProduct(),

			'order' 			=> $order,
			'direction' 		=> $orderDirection,
		);

		return $this->responseView('Brivium_Store_ViewPublic_Product_Category', 'BRS_product_index', $viewParams);
	}

	protected function _getDefaultProductSort(array $category)
	{
		return array('display_order',  'ASC');
	}

	protected function _getProductSortFields(array $category)
	{
		return array('title', 'create_date', 'display_order', 'buy_count', 'cost_amount');
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
}