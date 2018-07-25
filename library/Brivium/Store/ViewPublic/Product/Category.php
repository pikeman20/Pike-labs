<?php

class Brivium_Store_ViewPublic_Product_Category extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$categories = $this->_params['categories'];

		$categorySidebarHtml = $this->_renderSidebarTemplate($categories, $this->_params['category']);
		//prd($categorySidebarHtml);
		$this->_params['categorySidebarHtml'] = $categorySidebarHtml;
	}
	protected function _renderSidebarTemplate($categories, $selectedCategory){
		foreach($categories AS $key=>&$category){
			if (!empty($category['categoryChildren']))
			{
				$category['childCategoryHtml'] = $this->_renderSidebarTemplate($category['categoryChildren'], $selectedCategory);
			}
		}
		return $this->_renderer->createTemplateObject('BRS_category_sidebar_list', array(
			'categories' => $categories,
			'category' => $selectedCategory,
		));
	}
}