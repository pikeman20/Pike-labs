<?php

/**
* Data writer for Category.
*
* @package Brivium_Store
*/
class Brivium_Store_DataWriter_Category extends XenForo_DataWriter
{
	/**
	 * Option to prevent the nested set info (lft, rgt, depth) from being set by user input
	 * (by default this info should be set only through a rebuild based on parent_category_id and display_order values).
	 * If this option is enabled, no further safeguards of the nested set info are enabled.
	 *
	 * @var string
	 */
	const OPTION_ALLOW_NESTED_SET_WRITE = 'allowNestedSetWrite';

	/**
	 * Option to rebuild nested set info etc. if necessary after insert/update/delete
	 *
	 * @var string
	 */
	const OPTION_POST_WRITE_UPDATE_CHILD_CATEGORIES = 'updateChildCategoriesAfterDbWrite';

	/**
	 * Optional destination parent category for children of a category to be deleted
	 *
	 * @var integer
	 */
	const OPTION_CHILD_CATEGORY_DESTINATION_PARENT_ID = 'destinationForChildrenOfDeletedCategory';


	/**
	 * Title of the phrase that will be created when a call to set the
	 * existing data fails (when the data doesn't exist).
	 *
	 * @var string
	 */
	protected $_existingDataErrorPhrase = 'requested_category_not_found';

	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_store_category' => array(
				'product_category_id'       => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'category_title'            => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50,
						'requiredError' => 'please_enter_valid_title'
				),
				'category_description'      => array('type' => self::TYPE_STRING, 'default' => ''),
				'parent_category_id'     	=> array('type' => self::TYPE_UINT, 'default' => 0),
				'product_count'     		=> array('type' => self::TYPE_UINT, 'default' => 0),
				'display_order'      		=> array('type' => self::TYPE_UINT, 'default' => 1),
				'lft'                		=> array('type' => self::TYPE_UINT, 'verification' => array('$this', '_verifyNestedSetInfo')),
				'rgt'                		=> array('type' => self::TYPE_UINT, 'verification' => array('$this', '_verifyNestedSetInfo')),
				'depth'              		=> array('type' => self::TYPE_UINT, 'verification' => array('$this', '_verifyNestedSetInfo')),
				'display_in_list'    		=> array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'last_post'   		 		=> array('type' => self::TYPE_UINT, 'default' => 0),
				'last_product_title' 		=> array('type' => self::TYPE_STRING, 'default' => ''),
				'last_product_id'    		=> array('type' => self::TYPE_UINT, 'default' => 0),
			)
		);
	}

	/**
	 * @return Brivium_Store_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_Category');
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
		if (!$categoryId = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		return array('xf_store_category' => $this->_getCategoryModel()->getCategoryById($categoryId));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'product_category_id = ' . $this->_db->quote($this->getExisting('product_category_id'));
	}

	/**
	* Gets the default set of options for this data writer.
	*
	* @return array
	*/
	protected function _getDefaultOptions()
	{
		return array(
			self::OPTION_ALLOW_NESTED_SET_WRITE => false,
			self::OPTION_POST_WRITE_UPDATE_CHILD_CATEGORIES => true,
			self::OPTION_CHILD_CATEGORY_DESTINATION_PARENT_ID => false,
		);
	}

	/**
	 * Prevents lft, rgt and depth fields from being set manually,
	 * if OPTION_ALLOW_NESTED_SET_WRITE is false
	 *
	 * @param integer $data
	 *
	 * @return boolean
	 */
	protected function _verifyNestedSetInfo(&$data)
	{
		if (!$this->getOption(self::OPTION_ALLOW_NESTED_SET_WRITE))
		{
			throw new XenForo_Exception('Nested set data can not be set unless OPTION_ALLOW_NESTED_SET_WRITE is enabled.');
			return false;
		}

		return true;
	}

	/**
	 * Verifies that a category name is valid - a-z0-9_-+ valid characters
	 *
	 * @param string $data
	 *
	 * @return boolean
	 */
	protected function _verifyCategoryName(&$data)
	{
		if (!$data)
		{
			$data = null;
			return true;
		}

		if (!preg_match('/^[a-z0-9_\-]+$/i', $data))
		{
			$this->error(new XenForo_Phrase('please_enter_category_name_using_alphanumeric'), 'category_name');
			return false;
		}

		if ($data === strval(intval($data)) || $data == '-')
		{
			$this->error(new XenForo_Phrase('category_names_contain_more_numbers_hyphen'), 'category_name');
			return false;
		}

		return true;
	}

	protected function _preSave()
	{
		
	}

	/**
	* Post-save handler.
	* If parent_category_id or category_type has changed, trigger a rebuild of the nested set info for all categories
	*/
	protected function _postSave()
	{
		if ($this->getOption(self::OPTION_POST_WRITE_UPDATE_CHILD_CATEGORIES))
		{
			if ($this->isChanged('parent_category_id')
				|| $this->isChanged('display_order')
				|| $this->isChanged('title')
			)
			{
				
				$this->_getCategoryModel()->updateNestedSetInfo();
			}
		}
	}

	/**
	 * Post-delete handler
	 * If there is are child categories of the deleted category, delete or move them accordingly
	 *
	 * @see library/XenForo/XenForo_DataWriter#_postDelete()
	 */
	protected function _postDelete()
	{
		$this->deleteCategoryProducts();

		if ($this->getOption(self::OPTION_POST_WRITE_UPDATE_CHILD_CATEGORIES))
		{
			$categoryModel = $this->_getCategoryModel();

			if ($categoryModel->hasChildCategories(array('lft' => $this->getExisting('lft'), 'rgt' => $this->getExisting('rgt'))))
			{
				$moveToCategoryId = $this->getOption(self::OPTION_CHILD_CATEGORY_DESTINATION_PARENT_ID);

				if ($moveToCategoryId !== false)
				{
					$categoryModel->moveChildCategories($this->_existingData['xf_store_category'], $moveToCategoryId, false);
				}
				else
				{
					$categoryModel->deleteChildCategories($this->_existingData['xf_store_category'], false);
				}
			}

			// we deleted and possibly moved stuff, so we need to do a rebuild of the nested set info
			$this->_getCategoryModel()->updateNestedSetInfo();
		}
	}

	public function deleteCategoryProducts()
	{
		$db = $this->_db;
		$categoryId = $this->get('product_category_id');
		$categoryIdQuoted = $db->quote($categoryId);

		$db->delete('xf_store_product', "product_category_id = $categoryIdQuoted");
	}
	
	public function productUpdate(Brivium_Store_DataWriter_Product $product)
	{
		if ($product->isUpdate() && $product->isChanged('product_category_id'))
		{
			$this->updateProductCount();

			$oldCat = XenForo_DataWriter::create('Brivium_Store_DataWriter_Category', XenForo_DataWriter::ERROR_SILENT);
			if ($oldCat->setExistingData($product->getExisting('product_category_id')))
			{
				$oldCat->productRemoved($product);
				$oldCat->save();
			}
		}
		else if ($product->isChanged('product_state'))
		{
			$this->updateProductCount();
		}
		else if ($product->isInsert())
		{
			$this->updateProductCount();
		}

		if ($product->get('product_date') >= $this->get('last_post'))
		{
			$this->set('last_post', $product->get('product_date'));
			$this->set('last_product_title', $product->get('title'));
			$this->set('last_product_id', $product->get('product_id'));
		}

	}

	/**
	 * Called when a product is removed from view in this category.
	 * Can apply to moves, deletes, etc.
	 *
	 * @param Brivium_Store_DataWriter_Product $product
	 */
	public function productRemoved(Brivium_Store_DataWriter_Product $product)
	{
		$this->updateProductCount();

		if ($this->get('last_product_id') == $product->get('product_id'))
		{
			$this->updateLastUpdate();
		}
	}

	public function updateLastUpdate()
	{
		$product = $this->_db->fetchRow($this->_db->limit(
			"
				SELECT *
				FROM xf_store_product
				WHERE product_category_id = ?
				ORDER BY product_date DESC
			", 1
		), $this->get('product_category_id'));
		if (!$product)
		{
			$this->set('product_count', 0);
			$this->set('last_post', 0);
			$this->set('last_product_title', '');
			$this->set('last_product_id', 0);
		}
		else
		{
			$this->set('last_post', $product['product_date']);
			$this->set('last_product_title', $product['title']);
			$this->set('last_product_id', $product['product_id']);
		}
	}

	public function updateProductCount($adjust = null)
	{
		if ($adjust === null)
		{
			$this->set('product_count', $this->_db->fetchOne("
				SELECT COUNT(*)
				FROM xf_store_product
				WHERE product_category_id = ?
			", $this->get('product_category_id')));
		}
		else
		{
			$this->set('product_count', $this->get('product_count') + $adjust);
		}
	}


	public function rebuildCounters()
	{
		$this->updateLastUpdate();
		$this->updateProductCount();
	}
	
	
	
}