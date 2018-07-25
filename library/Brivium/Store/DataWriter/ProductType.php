<?php

/**
* Data writer for product type.
*
* @package Brivium_Store
*/
class Brivium_Store_DataWriter_ProductType extends XenForo_DataWriter
{
	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_store_product_type' => array(
				'product_type_id'			=> array('type' => self::TYPE_STRING, 'maxLength' => 100, 'required' => true,
						'verification' 		=> array('$this', '_verifyProductTypeId'), 'requiredError' => 'BRS_please_enter_valid_product_type_id'
				),
				'title'						=> array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 100),
				'purchase_type'				=> array('type' => self::TYPE_STRING, 'default' => ''),
				'active'     		 		=> array('type' => self::TYPE_BOOLEAN, 'default' => 1),
			)
		);
	}

	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @return array|false
	*/
	protected function _getExistingData($data)
	{
		if (!$productTypeId = $this->_getExistingPrimaryKey($data, 'product_type_id'))
		{
			return false;
		}
		return array('xf_store_product_type' => $this->_getProductTypeModel()->getProductTypeById($productTypeId));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'product_type_id = ' . $this->_db->quote($this->getExisting('product_type_id'));
	}
	
	/**
	 * Verifies that the action ID contains valid characters and does not already exist.
	 *
	 * @param $productType
	 *
	 * @return boolean
	 */
	protected function _verifyProductTypeId(&$productTypeId)
	{
		if (preg_match('/[^a-zA-Z0-9_]/', $productTypeId))
		{
			$this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'product_type_id');
			
			return false;
		}
		if ($productTypeId !== $this->getExisting('product_type_id'))
		{
			if ($this->_getProductTypeModel()->getProductTypeById($productTypeId))
			{
				$this->error(new XenForo_Phrase('BRS_product_types_must_be_unique'), 'product_type_id');
				return false;
			}
		}
		return true;
	}
	
	
	/**
	 * Update notified user's total number of unread alerts
	 */
	protected function _postSave()
	{
		$this->_getProductTypeModel()->rebuildProductTypeCache();
	}

	/**
	 * Post-delete behaviors.
	 */
	protected function _postDelete()
	{
		$idQuoted = $this->_db->quote($this->get('product_type_id'));
		
		$products = $this->_getProductModel()->getProducts(array('product_type_id' => $this->get('product_type_id')));
		if($products){
			foreach($products AS $product){
				$dw = XenForo_DataWriter::create('Brivium_Store_DataWriter_Product', XenForo_DataWriter::ERROR_SILENT);
				if ($dw->setExistingData($product['product_id']))
				{
					$dw->delete();
				}
			}
		}
		$this->_getProductTypeModel()->rebuildProductTypeCache();
	}
	
	protected function _getProductTypeModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_ProductType');
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