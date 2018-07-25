<?php

class Brivium_Store_ControllerAdmin_ProductType extends XenForo_ControllerAdmin_Abstract
{
	
	public function actionIndex()
	{
		$productTypeModel = $this->_getProductTypeModel();
		$productTypes = $productTypeModel->getAllProductTypes();
		

		$viewParams = array(
			'productTypes' => $productTypes,
		);

		return $this->responseView('Brivium_Store_ViewAdmin_ProductType_List', 'BRS_product_type_list', $viewParams);
	}
	
	/*
	public function actionAdd()
	{
		return $this->responseReroute('Brivium_Store_ControllerAdmin_ProductType', 'edit');
	}

	public function actionEdit()
	{
		$productTypeModel = $this->_getProductTypeModel();

		if ($productTypeId = $this->_input->filterSingle('product_type_id', XenForo_Input::STRING))
		{
			// if a productType ID was specified, we should be editing, so make sure a productType exists
			$productType = $productTypeModel->getProductTypeById($productTypeId);
			if (!$productType)
			{
				return $this->responseError(new XenForo_Phrase('BRS_requested_product_type_not_found'), 404);
			}
		}
		else
		{
			// add a new productType
			$productType = array(
				'title' => '',
				'purchase_type' => '',
			);
		}
		
		
		$viewParams = array(
			'productType' => $productType,
		);
		return $this->responseView('Brivium_Store_ViewAdmin_ProductType_Edit', 'BRS_product_type_edit', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		if ($this->_input->filterSingle('delete', XenForo_Input::STRING))
		{
			return $this->responseReroute('Brivium_Store_ControllerAdmin_ProductType', 'delete');
		}

		$originalProductTypeId = $this->_input->filterSingle('original_product_type_id', XenForo_Input::STRING);
		$writerData = $this->_input->filter(array(
			'product_type_id' => XenForo_Input::STRING,
			'title' => XenForo_Input::STRING,
			'purchase_type' => XenForo_Input::STRING,
		));
		$writer = XenForo_DataWriter::create('Brivium_Store_DataWriter_ProductType');
		if ($originalProductTypeId)
		{
			$writer->setExistingData($originalProductTypeId);
		}

		$writer->bulkSet($writerData);
		$writer->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('brs-product-types') . $this->getLastHash($writer->get('product_type_id'))
		);
	}

	*/
	/**
	 * This method should be sufficiently generic to handle deletion of any extended productType type
	 *
	 * @return XenForo_ControllerResponse_Reroute
	 */
	/**
	public function actionDelete()
	{
		$productTypeModel = $this->_getProductTypeModel();
		$productTypeId = $this->_input->filterSingle('product_type_id', XenForo_Input::STRING);
		if ($this->isConfirmedPost())
		{
			$dw = XenForo_DataWriter::create('Brivium_Store_DataWriter_ProductType');
			$dw->setExistingData($productTypeId);
			$dw->delete();
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('brs-product-types')
			);
		}
		else // show confirmation dialog
		{
			$viewParams['productType'] = $productTypeModel->getProductTypeById($productTypeId);
			return $this->responseView('Brivium_Store_ViewAdmin_Product_Delete', 'BRS_product_type_delete', $viewParams);
		}
	}
	*/
	protected function _getLengthUnits($lengthUnits)
	{
		if(!$lengthUnits)$lengthUnits = array();
		$lengthUnitOptions = array(
			'day' => array(
				'value' => 'day',
				'label' => new XenForo_Phrase('days'),
				'selected' => in_array('day',$lengthUnits),
				'depth' => 0,
			),
			'month' => array(
				'value' => 'month',
				'label' => new XenForo_Phrase('months'),
				'selected' => in_array('month',$lengthUnits),
				'depth' => 0,
			),
			'year' => array(
				'value' => 'year',
				'label' => new XenForo_Phrase('years'),
				'selected' => in_array('year',$lengthUnits),
				'depth' => 0,
			),
			'time' => array(
				'value' => 'time',
				'label' => new XenForo_Phrase('BRS_times'),
				'selected' => in_array('time',$lengthUnits),
				'depth' => 0,
			),
		);
		return $lengthUnitOptions;
	}
	
	/**
	 * Gets the permission model.
	 *
	 * @return XenForo_Model_Permission
	 */
	protected function _getPermissionModel()
	{
		return $this->getModelFromCache('XenForo_Model_Permission');
	}
	
	/**
	 * Gets the productType model.
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
	 * @return Brivium_Store_Model_ProductType
	 */
	protected function _getProductTypeModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_ProductType');
	}
	
	
}