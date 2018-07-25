<?php

class Brivium_Store_ControllerAdmin_Product extends XenForo_ControllerAdmin_Abstract
{
	public function actionIndex()
	{
		return $this->responseReroute('Brivium_Store_ControllerAdmin_Store', 'index');
	}

	public function actionList()
	{

		if ($this->_input->inRequest('delete_selected'))
		{
			return $this->responseReroute(__CLASS__, 'delete');
		}

		$input = $this->_getFilterParams();

		$dateInput = $this->_input->filter(array(
			'start' => XenForo_Input::DATE_TIME,
			'end' => XenForo_Input::DATE_TIME,
		));

		$productModel = $this->_getProductModel();
		$categoryModel = $this->_getCategoryModel();
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
		if ($input['product_category_id'])
		{
			$pageParams['product_category_id'] = $input['product_category_id'];
		}
		if ($input['product_type_id'])
		{
			$pageParams['product_type_id'] = $input['product_type_id'];
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
			'product_category_id' => $input['product_category_id'],
			'product_type_id' => $input['product_type_id'],
			'user_id' => $userId,
			'start' => $dateInput['start'],
			'end' => $dateInput['end'],
		);
		$fetchOptions = array(
			'page' => $page,
			'perPage' => $perPage,
			'join' =>  Brivium_Store_Model_Product::FETCH_FULL
		);
		switch ($input['order'])
		{
			case 'buy_count':
				$fetchOptions['order'] = 'buy_count';
				break;

			case 'product_date';
			default:
				$input['order'] = 'product_date';
				$fetchOptions['order'] = 'product_date';
				break;
		}

		$products = $productModel->getProducts($conditions, $fetchOptions);
		$products = $productModel->prepareProducts($products);
		$categories =  $categoryModel->getCategoryOptionsArray($categoryModel->getAllCategories());

		$viewParams = array(
			'categories' => $categories,
			'productTypes' => $this->_getProductTypeModel()->getAllProductTypes(),

			'products' => $products,

			'order' => $input['order'],
			'categoryId' => $input['product_category_id'],

			'productTypeId' => $input['product_type_id'],

			'username' => $input['username'],
			'start' => $input['start'],
			'end' => $input['end'],

			'datePresets' => XenForo_Helper_Date::getDatePresets(),

			'page' => $page,
			'perPage' => $perPage,
			'pageParams' => $pageParams,
			'total' =>	$productModel->countProducts($conditions)
		);
		return $this->responseView('Brivium_Store_ViewAdmin_Product_List', 'BRS_product_list', $viewParams);
	}

	public function actionAdd()
	{
		$productTypeId = $this->_input->filterSingle('product_type_id', XenForo_Input::STRING);
		if ($this->isConfirmedPost()||$productTypeId)
		{
			if($productTypeId){
				return $this->responseReroute('Brivium_Store_ControllerAdmin_Product', 'edit');
			}
		}
		$productTypes = $this->_getProductTypeModel()->getAllProductTypes();

		$viewParams = array(
			'productTypes' => $productTypes,
		);

		return $this->responseView('Brivium_Store_ViewAdmin_Product_Add', 'BRS_product_add', $viewParams);

	}

	public function actionEdit()
	{
		$productModel = $this->_getProductModel();
		$productTypeModel = $this->_getProductTypeModel();
		$categoryModel = $this->_getCategoryModel();

		if ($productId = $this->_input->filterSingle('product_id', XenForo_Input::INT))
		{
			// if a product ID was specified, we should be editing, so make sure a product exists
			$product = $productModel->getProductById($productId);
			if (!$product)
			{
				return $this->responseError(new XenForo_Phrase('BRS_requested_product_not_found'), 404);
			}
		}
		else
		{
			$product = array(
				'product_category_id' => $this->_input->filterSingle('product_category_id', XenForo_Input::UINT) ,
				'product_type_id' => $this->_input->filterSingle('product_type_id', XenForo_Input::STRING),
				'display_order' => 1,
				'display_in_list' => 1,
				'currency_id' => 0,
				'quantity' => -1,
			);
		}
		$noCreditPremium = true;
		$noCreditFree = true;
		$requiredAddon = XenForo_Model::create('XenForo_Model_AddOn')->getAddOnVersion('Brivium_Credits');
		if(!empty($requiredAddon['version_id'])){
			if($requiredAddon['version_id'] < 1000000){
				$noCreditPremium = true;
				$noCreditFree = false;
			}else{
				$noCreditPremium = false;
				$noCreditFree = true;
			}
		}
		$productType = $productTypeModel->getProductTypeById($product['product_type_id']);
		$viewParams = array(
			'product' => $product,
			'productType' => $productType,
			'noCreditPremium' => $noCreditPremium,
			'noCreditFree' => $noCreditFree,

			'editTemplate' => 'BRS_product_edit',
			'lengthUnits' => $this->_getLengthUnits(),

			'saveLink' => XenForo_Link::buildAdminLink('brs-products/save',$product),
			'categoryOptions' => $categoryModel->getCategoryOptionsArray($categoryModel->getAllCategories(), $product['product_category_id'])
		);

		$editTemplate = 'BRS_product_edit';
		return $this->_getProductAddEditResponse($viewParams, $editTemplate, $product['product_type_id']);
	}


	protected function _getProductAddEditResponse(array $viewParams, $editTemplate, $productTypeId)
	{
		if(empty($viewParams['currencies'])){
			return $this->responseError(new XenForo_Phrase('BRS_you_must_create_credit_event_for_this_product_type'));
		}
		return $this->responseView('Brivium_Store_ViewAdmin_Product_Edit',$editTemplate , $viewParams);
	}

	protected function _getCurrencies($actionId)
	{
		if(!$actionId){
			return array();
		}
		$currencies = $this->_getCreditHelper()->assertCurrenciesValidAndViewable($actionId);
		return $currencies;
	}

	protected function _getCreditHelper()
	{
		return $this->getHelper('Brivium_Credits_ControllerHelper_Credit');
	}

	public function actionSave()
	{
		$this->_assertPostOnly();
		$categoryModel = $this->_getCategoryModel();
		$productId = $this->_input->filterSingle('product_id', XenForo_Input::UINT);

		$writerData = $this->_input->filter(array(
			'title' 			=> XenForo_Input::STRING,
			'description' 		=> XenForo_Input::STRING,
			'product_category_id' 	=> XenForo_Input::UINT,
			'cost_amount' 		=> XenForo_Input::UNUM,
			'money_type' 		=> XenForo_Input::STRING,
			'currency_id' 		=> XenForo_Input::UINT,
			'length_amount' 	=> XenForo_Input::UINT,
			'length_unit' 		=> XenForo_Input::STRING,
			'recurring' 		=> XenForo_Input::UINT,
			'sticky' 			=> XenForo_Input::UINT,
			'quantity' 			=> XenForo_Input::UINT,
			'display_order' 	=> XenForo_Input::UINT,
			'display_in_list'	=> XenForo_Input::UINT,
			'can_purchase'	 	=> XenForo_Input::UINT,
			'can_gift'	 		=> XenForo_Input::UINT,
			'product_type_id' 	=> XenForo_Input::STRING
		));

		$quantityType = $this->_input->filterSingle('quantity_type', XenForo_Input::STRING);
		if($quantityType=='unlimited'){
			$writerData['quantity'] = -1;
		}

		$writerData['money_type'] = $writerData['money_type']?$writerData['money_type']:'trophy_points';
		if($writerData['money_type'] != 'trophy_points'){
			$requiredAddon = XenForo_Model::create('XenForo_Model_AddOn')->getAddOnVersion('Brivium_Credits');
			if((empty($requiredAddon['version_id']) || $requiredAddon['version_id'] < 1000000) && $writerData['money_type'] == 'brivium_credit_premium'){
				return $this->responseError(new XenForo_Phrase('BRS_brivium_credit_premium_required'));
			}else if((empty($requiredAddon['version_id']) || $requiredAddon['version_id'] > 1000000) && $writerData['money_type'] == 'brivium_credit_free'){
				return $this->responseError(new XenForo_Phrase('BRS_brivium_credit_free_required'));
			}
		}

		$writer = XenForo_DataWriter::create('Brivium_Store_DataWriter_Product');

		if($productId){
			$writer->setExistingData($productId);
		}
		$visitor = XenForo_Visitor::getInstance();
		$writerData['user_id'] = $visitor['user_id'];
		$writerData['username'] = $visitor['username'];

		$writer = $this->_processProductWriter($writer, $writerData, $writerData['product_type_id']);

		$writer->save();

		$this->_processProductWriterAfterSave($writer);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('brs-products/list','',array('product_category_id'=>$writerData['product_category_id'])) . $this->getLastHash($writer->get('product_id'))
		);

	}

	protected function _processProductWriter(Brivium_Store_DataWriter_Product $writer, $writerData, $productTypeId)
	{
		$writer->bulkSet($writerData);
		return $writer;
	}
	protected function _processProductWriterAfterSave(Brivium_Store_DataWriter_Product $writer)
	{
		$this->imageUpload($writer->get("product_id"));
		return;
	}

	public function imageUpload($contentId)
	{
		$image = XenForo_Upload::getUploadedFile('upload_image');
		$imageModel = $this->getModelFromCache('Brivium_Store_Model_Image');
		$imageData = array();

		if ($image)
		{
			$imageData = $imageModel->uploadImage($image, $contentId, array());
		}
		return $imageData;
	}
	protected function _getLengthUnits()
	{
		$lengthUnitOptions = array(
			'day' => array(
				'value' => 'day',
				'label' => new XenForo_Phrase('days'),
				'depth' => 0,
			),
			'month' => array(
				'value' => 'month',
				'label' => new XenForo_Phrase('months'),
				'depth' => 0,
			),
			'year' => array(
				'value' => 'year',
				'label' => new XenForo_Phrase('years'),
				'depth' => 0,
			),
			'time' => array(
				'value' => 'time',
				'label' => new XenForo_Phrase('BRS_times'),
				'depth' => 0,
			),
		);
		return $lengthUnitOptions;
	}
	public function actionDelete()
	{
		$productModel = $this->_getProductModel();

		$filterParams = $this->_getFilterParams();

		$productIds = $this->_input->filterSingle('product_ids', array(XenForo_Input::UINT, 'array' => true));
		if ($productId = $this->_input->filterSingle('product_id', XenForo_Input::UINT))
		{
			$productIds[] = $productId;
		}

		if ($this->isConfirmedPost())
		{
			foreach ($productIds AS $productId)
			{
				$dw = XenForo_DataWriter::create('Brivium_Store_DataWriter_Product');
				$dw->setExistingData($productId);
				$dw->delete();
			}
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('brs-products/list', null, $filterParams)
			);
		}
		else // show confirmation dialog
		{
			$fetchOptions = array(
				'join' =>  Brivium_Store_Model_Product::FETCH_FULL
			);
			$viewParams = array(
				'productIds' => $productIds,
				'filterParams' => $filterParams
			);

			if (count($productIds) == 1)
			{
				list($productId) = $productIds;
				$viewParams['product'] = $productModel->getProductById($productId,$fetchOptions);
			}


			return $this->responseView('Brivium_Store_ViewAdmin_Product_Delete', 'BRS_product_delete', $viewParams);
		}
	}

	protected function _getFilterParams()
	{
		return $this->_input->filter(array(
			'order' => XenForo_Input::STRING,
			'product_category_id' => XenForo_Input::UINT,
			'product_type_id' => XenForo_Input::STRING,
			'username' => XenForo_Input::STRING,
			'start' => XenForo_Input::STRING,
			'end' => XenForo_Input::STRING
		));
	}

	public function getProductHelper($class)
	{
		if (strpos($class, '_') === false)
		{
			$class = 'Brivium_Store_ControllerHelper_Product_' . $class;
		}

		$class = XenForo_Application::resolveDynamicClass($class);

		return new $class($this);
	}



	protected function _getProductTypeModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_ProductType');
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