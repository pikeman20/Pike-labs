<?php

class Brivium_Store_ControllerAdmin_Store extends XenForo_ControllerAdmin_Abstract
{
	
	public function actionIndex()
	{
		$categoryModel = $this->_getCategoryModel();

		$categories = $categoryModel->prepareCategoriesForAdmin($categoryModel->getAllCategories());
		

		$viewParams = array(
			'categories' => $categories,
		);

		return $this->responseView('Brivium_Store_ViewAdmin_Category_List', 'BRS_category_list', $viewParams);
	}
	
	/*========================= Category ================================*/
	
	public function actionAdd()
	{
		return $this->responseReroute('Brivium_Store_ControllerAdmin_Store', 'edit');
	}

	public function actionEdit()
	{
		$categoryModel = $this->_getCategoryModel();

		if ($categoryId = $this->_input->filterSingle('product_category_id', XenForo_Input::UINT))
		{
			// if a category ID was specified, we should be editing, so make sure a category exists
			$category = $categoryModel->getCategoryById($categoryId);
			if (!$category)
			{
				return $this->responseError(new XenForo_Phrase('requested_category_not_found'), 404);
			}
		}
		else
		{
			// add a new category
			$category = array(
				'parent_category_id' => $this->_input->filterSingle('parent_category_id', XenForo_Input::UINT),
				'display_order' => 1,
				'display_in_list' => 1
			);
		}

		$viewParams = array(
			'category' => $category,
			'categoryParentOptions' => $categoryModel->getCategoryOptionsArray(
				$categoryModel->getPossibleParentCategories($category), $category['parent_category_id'], true
			),
		);
		return $this->responseView('Brivium_Store_ViewAdmin_Category_Edit', 'BRS_category_edit', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		if ($this->_input->filterSingle('delete', XenForo_Input::STRING))
		{
			return $this->responseReroute('Brivium_Store_ControllerAdmin_Store', 'deleteConfirm');
		}

		$categoryId = $this->_input->filterSingle('product_category_id', XenForo_Input::UINT);

		$writerData = $this->_input->filter(array(
			'category_title' => XenForo_Input::STRING,
			'parent_category_id' => XenForo_Input::UINT,
			'display_order' => XenForo_Input::UINT,
			'display_in_list' => XenForo_Input::UINT,
			'category_description' => XenForo_Input::STRING,
		));

		$writer = XenForo_DataWriter::create('Brivium_Store_DataWriter_Category');

		if ($categoryId)
		{
			$writer->setExistingData($categoryId);
		}

		$writer->bulkSet($writerData);
		$writer->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('store') . $this->getLastHash($writer->get('product_category_id'))
		);
	}

	public function actionDeleteConfirm()
	{
		$categoryModel = $this->_getCategoryModel();

		$category = $categoryModel->getCategoryById($this->_input->filterSingle('product_category_id', XenForo_Input::UINT));
		if (!$category)
		{
			return $this->responseError(new XenForo_Phrase('requested_category_not_found'), 404);
		}

		$childCategories = $categoryModel->getChildCategories($category);

		$viewParams = array(
			'category' => $category,
			'childCategories' => $childCategories,
			'categoryParentOptions' => $categoryModel->getCategoryOptionsArray(
				$categoryModel->getPossibleParentCategories($category), $category['parent_category_id'], true
			)
		);

		return $this->responseView('Brivium_Store_ViewAdmin_Category_Delete', 'BRS_category_delete', $viewParams);
	}
	
	/**
	 * This method should be sufficiently generic to handle deletion of any extended category type
	 *
	 * @return XenForo_ControllerResponse_Reroute
	 */
	public function actionDelete()
	{
		$categoryId = $this->_input->filterSingle('product_category_id', XenForo_Input::INT);

		if ($this->isConfirmedPost())
		{
			$writer = XenForo_DataWriter::create('Brivium_Store_DataWriter_Category');
			$writer->setExistingData($categoryId);

			if ($this->_input->filterSingle('move_child_categories', XenForo_Input::BINARY))
			{
				$parentCategoryId = $this->_input->filterSingle('parent_category_id', XenForo_Input::UINT);

				if ($parentCategoryId)
				{
					$parentCategory = $this->_getCategoryModel()->getCategoryById($parentCategoryId);

					if (!$parentCategory)
					{
						return $this->responseError(new XenForo_Phrase('BRS_specified_destination_category_does_not_exist'));
					}
				}
				else
				{
					// no destination category id, so set it to 0 (root category)
					$parentCategoryId = 0;
				}

				$writer->setOption(Brivium_Store_DataWriter_Category::OPTION_CHILD_CATEGORY_DESTINATION_PARENT_ID, $parentCategoryId);
			}

			$writer->delete();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('store')
			);
		}
		else
		{
			if ($categoryId)
			{
				return $this->responseReroute('Brivium_Store_ControllerAdmin_Store', 'deleteConfirm');
			}
			else
			{
				return $this->responseError(new XenForo_Phrase('requested_category_not_found'), 404);
			}
		}
	}
	
	public function actionProductPurchaseActive()
	{
	
		$productId = $this->_input->filterSingle('product_id', XenForo_Input::UINT);
		$productPurchaseModel = $this->_getProductPurchaseModel();
		$productModel = $this->_getProductModel();
		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = 20;

		$fetchOptions = array(
			'page' => $page,
			'perPage' => $perPage,
			'order'	=> 'last_purchase',
		);
		$conditions = array(
			'active' => true
		);
		$pageParams = array();
		$product = array();
		if ($productId)
		{
			$product = $productModel->getProductById($productId);
			$conditions['product_id'] = $product['product_id'];
			$pageParams['product_id'] = $product['product_id'];
		}
		$fetchOptions['join'] = Brivium_Store_Model_ProductPurchase::JOIN_PRODUCT;
		
		$viewParams = array(
			'product' => $product,
			'pageParams' => $pageParams,
			'purchaseRecords' => $productPurchaseModel->getProductPurchaseRecords($conditions, $fetchOptions),
			'totalRecords' => $productPurchaseModel->countProductPurchaseRecords($conditions),
			'perPage' => $perPage,
			'page' => $page
		);
		return $this->responseView('Brivium_Store_ViewAdmin_ProductPurchase_Active', 'BRS_product_purchase_active', $viewParams);
	}
	
	public function actionRemoveProductPurchase()
	{
		$productModel = $this->_getProductModel();
		$productPurchaseModel = $this->_getProductPurchaseModel();
		$productPurchaseId = $this->_input->filterSingle('product_purchase_id', XenForo_Input::UINT);
		$active = $productPurchaseModel->getActiveProductPurchaseById($productPurchaseId);
		if (!$active)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_purchase_not_found'), 404);
		}
		
		$product = $productModel->getProductById($active['product_id']);
		
		/* if (!$product)
		{
			return $this->responseError(new XenForo_Phrase('BRS_requested_product_not_found'), 404);
		} */
		$userId = XenForo_Visitor::getUserId();
		if ($this->isConfirmedPost())
		{
			$productPurchaseModel->processExpiredProductPurchases(array($active),true);
			$redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);
			$redirect = ($redirect ? $redirect : $this->getDynamicRedirect());

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('store/product-purchase-active')
			);
		}
		else // show confirmation dialog
		{
			if ($product){
				$product = $productModel->prepareProduct($product);
			}	
			$viewParams = array(
				'product' => $product,
				'purchase' => $active,
				'redirect' => $this->getDynamicRedirect()
			);
			return $this->responseView('Brivium_Store_ViewAdmin_Product_Purchase_Remove', 'BRS_product_purchase_remove_confirm', $viewParams);
		}
	}
	
	
	/*========================= Transactions ================================*/
	
	public function actionTransactions()
	{
		if ($this->_input->inRequest('delete_selected'))
		{
			return $this->responseReroute(__CLASS__, 'delete-transaction');
		}

		$input = $this->_getFilterParams();

		$dateInput = $this->_input->filter(array(
			'start' => XenForo_Input::DATE_TIME,
			'end' => XenForo_Input::DATE_TIME,
		));

		$transactionModel = $this->_getTransactionModel();

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = 50;

		$pageParams = array();
		if ($input['order'])
		{
			$pageParams['order'] = $input['order'];
		}
		if ($input['start'])
		{
			$pageParams['start'] = $input['start'];
		}
		if ($input['end'])
		{
			$pageParams['end'] = $input['end'];
		}
		if ($input['product_id'])
		{
			$pageParams['product_id'] = $input['product_id'];
		}

		$userId = 0;
		if ($input['username'])
		{
			if ($user = $this->getModelFromCache('XenForo_Model_User')->getUserByName($input['username']))
			{
				$userId = $user['user_id'];
				$pageParams['username'] = $input['username'];
			}
			else
			{
				$input['username'] = '';
			}
		}

		$conditions = array(
			'product_id' => $input['product_id'],
			'user_id' => $userId,
			'start' => $dateInput['start'],
			'end' => $dateInput['end'],
		);
		$fetchOptions = array(
			'page' => $page,
			'perPage' => $perPage,
			'join' =>  Brivium_Store_Model_Transaction::FETCH_FULL
		);
		switch ($input['order'])
		{
			case 'transaction_date';
			default:
				$input['order'] = 'transaction_date';
				$fetchOptions['order'] = 'transaction_date';
				break;
		}

		$transactions = $transactionModel->getTransactions($conditions, $fetchOptions);
		$transactions = $transactionModel->prepareTransactions($transactions);
		$productModel = $this->_getProductModel();
		$products = $productModel->getAllProducts();
		
		$viewParams = array(
			'products' => $products,

			'transactions' => $transactions,

			'order' => $input['order'],
			'productId' => $input['product_id'],
			'username' => $input['username'],
			'start' => $input['start'],
			'end' => $input['end'],

			'datePresets' => XenForo_Helper_Date::getDatePresets(),

			'page' => $page,
			'perPage' => $perPage,
			'pageParams' => $pageParams,
			'total' =>	$transactionModel->countTransactions($conditions)
		);

		return $this->responseView('Brivium_Store_ControllerAdmin_Transaction_List', 'BRS_transaction_list', $viewParams);
	}
	
	public function actionDeleteTransaction()
	{
		$transactionModel = $this->_getTransactionModel();
		
		$filterParams = $this->_getFilterParams();

		$transactionIds = $this->_input->filterSingle('transaction_ids', array(XenForo_Input::UINT, 'array' => true));

		if ($transactionId = $this->_input->filterSingle('transaction_id', XenForo_Input::UINT))
		{
			$transactionIds[] = $transactionId;
		}
		$transactionId = $this->_input->filterSingle('transaction_id', XenForo_Input::UINT);

		if ($this->isConfirmedPost())
		{
			foreach ($transactionIds AS $transactionId)
			{
				$transactionModel->deleteTransactionById($transactionId);
			}
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('store/transactions', null, $filterParams)
			);
		}
		else // show confirmation dialog
		{
			$fetchOptions = array(
				'join' =>  Brivium_Store_Model_Transaction::FETCH_FULL
			);
			$viewParams = array(
				'transactionIds' => $transactionIds,
				'filterParams' => $filterParams
			);

			if (count($transactionIds) == 1)
			{
				list($transactionId) = $transactionIds;
				$viewParams['transaction'] = $transactionModel->prepareTransaction($transactionModel->getTransactionById($transactionId,$fetchOptions));
			}
			

			return $this->responseView('Brivium_Store_ViewAdmin_DeleteTransaction', 'BRS_transaction_delete', $viewParams);
		}
	}
	
	
	
	protected function _getFilterParams()
	{
		return $this->_input->filter(array(
			'order' => XenForo_Input::STRING,
			'product_id' => XenForo_Input::STRING,
			'username' => XenForo_Input::STRING,
			'start' => XenForo_Input::STRING,
			'end' => XenForo_Input::STRING
		));
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