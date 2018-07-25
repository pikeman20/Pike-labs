<?php

/**
* Data writer for product.
*
* @package Brivium_Store
*/
class Brivium_Store_DataWriter_Product extends XenForo_DataWriter
{
	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_store_product' => array(
				'product_id'        => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'product_category_id'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'title'				=> array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 100),
				'description'		=> array('type' => self::TYPE_STRING, 'default' => ''),
				'user_id'			=> array('type' => self::TYPE_UINT, 'required' => true),

				'username'			=> array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 50),
				'product_type_id'		=> array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 50),
				'cost_amount'       => array('type' => self::TYPE_FLOAT,   'required' => true,
						'verification' 	=> array('$this', '_verifyCostAmount')
				),
				'money_type'		=> array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 150),
				'currency_id'		=> array('type' => self::TYPE_UINT, 'default' => 0),
				'length_amount'     => array('type' => self::TYPE_UINT,    'required' => true),
				'length_unit'       => array('type' => self::TYPE_STRING,  'default' => '',
						'allowedValues' => array('day', 'month', 'year', 'time', '')
				),
				'permissions'      	=> array('type' => self::TYPE_UNKNOWN,
						'verification' => array('$this', '_verifyPermissions')),
				'product_unique'      	=> array('type' => self::TYPE_STRING, 'default' => ''),
				'recurring'      	=> array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'can_purchase'      => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'unique'      		=> array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'product_date'		=> array('type' => self::TYPE_UINT, 'default' => XenForo_Application::$time),
				'sticky'			=> array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'buy_count'			=> array('type' => self::TYPE_UINT, 'default' => 0),
				'quantity'			=> array('type' => self::TYPE_INT, 'default' => -1),
				'display_order'		=> array('type' => self::TYPE_UINT, 'default' => 0),
				'display_in_list'	=> array('type' => self::TYPE_BOOLEAN, 'default' => 1),

				'style_css'			=> array('type' => self::TYPE_UNKNOWN,
						'verification' => array('$this', '_verifyStyleCss')),
				'image_type'		=> array('type' => self::TYPE_STRING,  'default' => '', 'maxLength' => 100),

				'has_icon'			=> array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'can_gift'			=> array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'product_state'		=> array('type' => self::TYPE_STRING, 'maxLength' => 30, 'default' => ''),
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
		if (!$productId = $this->_getExistingPrimaryKey($data, 'product_id'))
		{
			return false;
		}
		return array('xf_store_product' => $this->_getProductModel()->getProductById($productId));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'product_id = ' . $this->_db->quote($this->getExisting('product_id'));
	}
	/**
	 * Verifies that the cost of the product is valid.
	 *
	 * @param float $cost
	 *
	 * @return boolean
	 */
	protected function _verifyCostAmount(&$cost)
	{
		if ($cost <= 0)
		{
			$this->error(new XenForo_Phrase('BRS_please_enter_an_product_cost_greater_than_zero'), 'cost_amount');
			return false;
		}
		else
		{
			return true;
		}
	}

	protected function _verifyPermissions(&$permissions)
	{
		if ($permissions === null)
		{
			$permissions = '';
			return true;
		}

		return XenForo_DataWriter_Helper_Denormalization::verifySerialized($permissions, $this, 'permissions');
	}
	protected function _verifyStyleCss(&$styles)
	{
		if ($styles === null)
		{
			$styles = '';
			return true;
		}

		return XenForo_DataWriter_Helper_Denormalization::verifySerialized($styles, $this, 'style_css');
	}

	protected function _postSave()
	{
		$catDw = $this->_getCategoryDwForUpdate();
		if ($catDw)
		{
			// will already be called for removal
			$catDw->productUpdate($this);
			$catDw->save();
		}
		if ($this->isUpdate() && $this->isChanged('product_unique'))
		{
			$idQuoted = $this->_db->quote($this->get('product_id'));
			$this->_db->update('xf_store_product_purchase_active', array('product_unique'=>$this->get('product_unique')), 'product_id = ' . $idQuoted);

		}
		$this->_getProductModel()->rebuildProductCache();
	}

	/**
	 * Post-delete behaviors.
	 */
	protected function _postDelete()
	{
		$idQuoted = $this->_db->quote($this->get('product_id'));
		$this->_db->delete('xf_store_transaction', 'product_id = ' . $idQuoted);
		$this->_db->delete('xf_store_product_purchase_expired', 'product_id = ' . $idQuoted);
		$this->_db->delete('xf_store_product_purchase_active', 'product_id = ' . $idQuoted);
		$this->_db->delete('xf_store_product_change', 'product_id = ' . $idQuoted);
		$this->_productRemoved();
		$this->_getProductModel()->rebuildProductCache();
	}
	protected function _productRemoved()
	{
		$catDw = $this->_getCategoryDwForUpdate();
		if ($catDw)
		{
			$catDw->productRemoved($this);
			$catDw->save();
		}
	}
	protected function _getCategoryDwForUpdate()
	{
		$dw = XenForo_DataWriter::create('Brivium_Store_DataWriter_Category', XenForo_DataWriter::ERROR_SILENT);
		if ($dw->setExistingData($this->get('product_category_id')))
		{
			return $dw;
		}
		else
		{
			return false;
		}
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