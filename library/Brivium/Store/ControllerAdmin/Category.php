<?php

class Brivium_Store_ControllerAdmin_Category extends XenForo_ControllerAdmin_Abstract
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

		if ($categoryId = $this->_input->filterSingle('category_id', XenForo_Input::UINT))
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

		$categoryId = $this->_input->filterSingle('category_id', XenForo_Input::UINT);

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
			XenForo_Link::buildAdminLink('brsc-categories') . $this->getLastHash($writer->get('category_id'))
		);
	}

	public function actionDeleteConfirm()
	{
		$categoryModel = $this->_getCategoryModel();

		$category = $categoryModel->getCategoryById($this->_input->filterSingle('category_id', XenForo_Input::UINT));
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
		$categoryId = $this->_input->filterSingle('category_id', XenForo_Input::INT);

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
				XenForo_Link::buildAdminLink('brsc-categories')
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