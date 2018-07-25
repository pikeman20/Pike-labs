<?php

/**
 * Model for categories.
 *
 * @package Brivium_Store
 */
class Brivium_Store_Model_Category extends XenForo_Model
{
	/**
	 * Checks that the provided array is a category
	 * by checking that it contains category_id and parent_category_id keys
	 *
	 * @param array $category
	 *
	 * @return boolean
	 */
	protected static function _isCategory($category)
	{
		return (
			!empty($category) &&
			is_array($category) &&
			array_key_exists('product_category_id', $category) &&
			array_key_exists('parent_category_id', $category)
		);
	}

	/**
	 * Checks that the provided array is a category array
	 * by checking that the first element is a category
	 *
	 * @param mixed $categories
	 *
	 * @return boolean
	 */
	protected static function _isCategoriesArray($categories)
	{
		if (is_array($categories))
		{
			if (count($categories) == 0)
			{
				return true;
			}
			else
			{
				return (self::_isCategory(reset($categories)));
			}
		}
		else
		{
			return false;
		}
	}
	/**
	 * Checks that the provided array is a category hierarchy
	 * by checking that the first child of the first element has a category_id key
	 *
	 * @param mixed $categoryHierarchy
	 *
	 * @return boolean
	 */
	protected static function _isCategoryHierarchy($categoryHierarchy)
	{
		if (is_array($categoryHierarchy))
		{
			if (count($categoryHierarchy) == 0)
			{
				return true;
			}

			$firstChild = reset($categoryHierarchy);
			if (is_array($firstChild))
			{
				return (self::_isCategoriesArray(reset($firstChild)));
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns category records based on product_id.
	 *
	 * @param string $categoryId
	 *
	 * @return array|false
	 */
	public function getCategoryById($categoryId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_store_category
			WHERE  product_category_id = ?
		', array( $categoryId));
	}
	
	/**
	 * Gets all categories from the database
	 *
	 * @param boolean $ignoreNestedSetOrdering If true, ignore nested set infor for ordering and use display_order instead
	 * @param boolean $listView If true, only includes categories viewable in list
	 *
	 * @return array
	 */
	public function getAllCategories($ignoreNestedSetOrdering = false, $listView = false)
	{
		if ($ignoreNestedSetOrdering)
		{
			return $this->fetchAllKeyed('
				SELECT *
				FROM xf_store_category
				' . ($listView ? 'WHERE display_in_list = 1' : '') . '
				ORDER BY parent_category_id, display_order ASC
			', 'product_category_id');
		}
		else
		{
			return $this->fetchAllKeyed('
				SELECT *
				FROM xf_store_category
				' . ($listView ? 'WHERE display_in_list = 1' : '') . '
				ORDER BY lft ASC
			', 'product_category_id');
		}
	}
	/**
	 * Gets an array representing the category hierarchy that can be traversed recursively
	 * Format: item[parent_id][category_id] = category
	 *
	 * @param array|null category list from getAllCategories()
	 *
	 * @return array category hierarchy
	 */
	public function getCategoryHierarchy($categories = null)
	{
		if (!$this->_isCategoriesArray($categories))
		{
			$categories = $this->getAllCategories(true);
		}

		$categoryHierarchy = array();

		foreach ($categories AS $category)
		{
			$categoryHierarchy[$category['parent_category_id']][$category['product_category_id']] = $category;
		}

		return $categoryHierarchy;
	}
	
	
	/**
	 * Gets an array of all categories that are decendents of the specified category
	 * up to $depth levels of nesting
	 *
	 * @param array $category
	 * @param integer $depth
	 * @param boolean $listView If true, only categories that are visible in the list view are included
	 *
	 * @return mixed
	 */
	public function getChildCategoriesToDepth($category, $depth, $listView = false)
	{
		if (!$this->_isCategory($category) || $depth < 1)
		{
			return false;
		}
		else if ($depth == 1)
		{
			// use parent id to get the results
			return $this->fetchAllKeyed('
				SELECT *
				FROM xf_store_category
				WHERE parent_category_id = ?
					' . ($listView ? ' AND display_in_list = 1' : '') . '
				ORDER BY lft ASC
			', 'product_category_id', $category['product_category_id']);
		}
		else
		{
			// use left/right/depth to get children
			return $this->fetchAllKeyed('
				SELECT *
				FROM xf_store_category
				WHERE lft > ? AND rgt < ? AND depth <= ?
					' . ($listView ? ' AND display_in_list = 1' : '') . '
				ORDER BY lft ASC
			', 'product_category_id', array($category['lft'], $category['rgt'], $category['depth'] + $depth));
		}
	}

	/**
	 * Groups a list of categories by their parent category ID. This allows
	 * for easier recursive traversal.
	 *
	 * @param array $categories Format: [category id] => info
	 *
	 * @return array Format: [parent category id][category id] => info
	 */
	public function groupCategoriesByParent(array $categories)
	{
		$output = array();

		foreach ($categories AS $categoryId => $category)
		{
			$output[$category['parent_category_id']][$categoryId] = $category;
		}

		return $output;
	}

	
	
	/**
	 * Gets an array of all categories that are not decendents of the specified category
	 *
	 * @param array	category
	 *
	 * @return array categories
	 */
	public function getPossibleParentCategories($category = null)
	{
		$rootCategory = array($this->getRootCategory());

		if (!$this->_isCategory($category))
		{
			// we are going to return ALL categories, as the specified category does not exist
			$categories = $this->getAllCategories();
		}
		else
		{
			// return only categories that are not decendents of the specified category
			$categories = $this->fetchAllKeyed('
				SELECT *
				FROM xf_store_category
				WHERE lft < ? OR rgt > ?
				ORDER BY lft ASC
			', 'product_category_id', array($category['lft'], $category['rgt']));
		}

		return $rootCategory + $categories;
	}

	/**
	 * Gets an array of all categories that are decendents of the specified category
	 *
	 * @param array $category
	 * @param boolean $listView If true, only categories that are visible in the list view are included
	 *
	 * @return mixed
	 */
	public function getChildCategories($category, $listView = false)
	{
		if (!$this->_isCategory($category))
		{
			return false;
		}

		if (!$this->hasChildCategories($category))
		{
			return array();
		}

		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_store_category
			WHERE lft > ? AND rgt < ?
				' . ($listView ? ' AND display_in_list = 1' : '') . '
			ORDER BY lft ASC
		', 'product_category_id', array($category['lft'], $category['rgt']));
	}
		
		
	/**
	 * Filters a set of grouped category data to only include categories up to
	 * a certain depth from the specified root category.
	 *
	 * @param integer $parentCategoryId Root of the sub-tree or the whole tree (0)
	 * @param array $groupedCategories Categories, grouped by parent: [parent category id][category id] => info
	 * @param integer $depth Depth to filter to; should be at least 1
	 *
	 * @return array Filtered, grouped categories
	 */
	public function filterGroupedCategoriesToDepth($parentCategoryId, array $groupedCategories, $depth)
	{
		if (empty($groupedCategories[$parentCategoryId]) || $depth < 1)
		{
			return array();
		}

		$okParentCategories = array($parentCategoryId);

		$currentDepth = 1;
		$checkCategories = array($parentCategoryId);

		while ($currentDepth < $depth)
		{
			$newCheckCategories = array();
			foreach ($checkCategories AS $checkCategoryId)
			{
				if (!empty($groupedCategories[$checkCategoryId]))
				{
					$newCheckCategories = array_merge(
						$newCheckCategories, array_keys($groupedCategories[$checkCategoryId])
					);
				}
			}

			$okParentCategories = array_merge($okParentCategories, $newCheckCategories);
			$checkCategories = $newCheckCategories;
			$currentDepth++;
		}

		$newGroupedCategories = array();
		foreach ($okParentCategories AS $parentCategoryId)
		{
			if (isset($groupedCategories[$parentCategoryId]))
			{
				$newGroupedCategories[$parentCategoryId] = $groupedCategories[$parentCategoryId];
			}
		}

		return $newGroupedCategories;
	}

	/**
	 * Gets all the category data required for a category list display
	 * (eg, a forum list) from a given point. Returns 3 pieces of data:
	 * 	* categoriesGrouped - categories, grouped by parent, with all data integrated
	 *
	 * @param array|false $parentCategory Root category of the tree to display from; false for the entire tree
	 * @param integer $displayDepth Number of levels of categories to display below the root, 0 for all
	 *
	 * @return array Empty, or with keys: categoriesGrouped, parentCategoryId
	 */
	public function getCategoryDataForListDisplay($parentCategory,$selectedId = 0)
	{
		if (is_array($parentCategory))
		{
			$categories = $this->getChildCategories($parentCategory, true);
			$parentCategoryId = $parentCategory['product_category_id'];
		}
		else if ($parentCategory === false)
		{
			$categories = $this->getAllCategories(false, true);
			$parentCategoryId = 0;
		}
		else
		{
			throw new XenForo_Exception('Unexpected parent category parameter passed to getCategoryDataForListDisplay');
		}
		if (!$categories)
		{
			return array();
		}
		$groupedCategories = $this->groupCategoriesByParent($categories);
		
		
		if(isset($groupedCategories[$parentCategoryId])){
			$this->_getCategoryData($groupedCategories, $groupedCategories[$parentCategoryId], $parentCategoryId);
		}
		return $groupedCategories;
	}
		
	protected function _getCategoryData($groupedCategories, &$groupedChildCategories, $parentCategoryId)
	{
		$productCount = 0;
		if(isset($groupedCategories[$parentCategoryId])){
			foreach($groupedChildCategories AS $key=>&$groupedCategory){
				if(isset($groupedCategories[$groupedCategory['product_category_id']])){
					$groupedCategory['childCount'] = count($groupedCategories[$groupedCategory['product_category_id']]);
					$groupedCategory['product_count'] += $this->_getCategoryData($groupedCategories,$groupedCategories[$groupedCategory['product_category_id']], $groupedCategory['product_category_id']);
					$groupedCategory['categoryChildren'] = $groupedCategories[$groupedCategory['product_category_id']];
				}
				$productCount += $groupedCategory['product_count'];
			}
		}
		return $productCount;
	}
	
	/**
	 * Calls prepareCategoriesForAdmin() on each member of the input array
	 *
	 * @param array Raw categories
	 *
	 * @return array Prepared categories
	 */
	public function prepareCategoriesForAdmin(array $categories)
	{
		foreach ($categories AS $id => &$category)
		{
			$category = $this->prepareCategoryForAdmin($category);
		}
		return $categories;
	}
	
	/**
	 * Prepares the raw data of a category into human-readable information for use in
	 * the admin area.
	 *
	 * @param array Raw category data
	 *
	 * @return array Prepared category
	 */
	public function prepareCategoryForAdmin(array $category)
	{
		$productModel = $this->_getProductModel();
		$category += $productModel->getCategoryCounters($category['product_category_id']);
		return $category;
	}	

	
	public function prepareCategories(array $categories)
	{
		foreach ($categories AS $id => &$category)
		{
			$category = $this->prepareCategoryForAdmin($category);
		}
		return $categories;
	}
	
	
	public function prepareCategory(array $category)
	{
		//$productModel = $this->_getProductModel();
		//$category += $productModel->getCategoryCounters($category['product_category_id']);
		return $category;
	}	

	/**
	 * Fetches a representation of the root category to be merged into an array of other categories
	 *
	 * @return array
	 */
	public function getRootCategory()
	{
		return array(
			'product_category_id' => 0,
			'category_title' => new XenForo_Phrase('BRS_root_category_meta'),
			'parent_category_id' => null,
			'product_count' => 0,
			'display_order' => 0,
			'lft' => 0,
			'rgt' => 0,
			'depth' => 0
		);
	}
	
	
	
	/**
	 * If the specified category has a lft-rgt difference of more than 1, it must have child categories.
	 *
	 * @param array Category must include lft and rgt keys
	 *
	 * @return boolean
	 */
	public function hasChildCategories(array $category)
	{
		if (array_key_exists('lft', $category) && array_key_exists('rgt', $category))
		{
			return ($category['rgt'] > ($category['lft'] + 1));
		}
		else
		{
			throw new XenForo_Exception('The array provided to ' . __CLASS__. '::hasChildCategories() did not contain the necessary keys.');
		}
	}

	/**
	 * Moves all child categories from one parent to another
	 *
	 * @param mixed Source category: Either category array or category id
	 * @param mixed Destination category: Either category array or category id
	 * @param boolean Rebuild caches afterwards
	 *
	 * @return null
	 */
	public function moveChildCategories($fromCategory, $toCategory, $rebuildCaches = true)
	{
		if (!$this->_isCategory($fromCategory))
		{
			$fromCategory = $this->getCategoryById($fromCategory);
		}

		if (!is_int($toCategory) && $this->_isCategory($toCategory))
		{
			$toCategory = $this->getCategoryById($toCategory);
			$toCategory = $toCategory['product_category_id'];
		}

		if ($childCategories = $this->getChildCategoriesToDepth($fromCategory, 1))
		{
			foreach ($childCategories AS $childCategoryId => $childCategory)
			{
				$writer = XenForo_DataWriter::create('Brivium_Store_DataWriter_Category');
				$writer->setExistingData($childCategoryId);
				$writer->setOption(Brivium_Store_DataWriter_Category::OPTION_POST_WRITE_UPDATE_CHILD_CATEGORIES, false);
				$writer->set('parent_category_id', $toCategory);
				$writer->save();
			}
		}

		if ($rebuildCaches)
		{
			$this->updateNestedSetInfo();
		}
	}

	/**
	 * Deletes all child categories of the specified parent category
	 *
	 * @param mixed Parent category: Either category array or category id
	 * @param boolean Rebuild caches afterwards
	 *
	 * @return null
	 */
	public function deleteChildCategories($parentCategory, $rebuildCaches = true)
	{
		if (!$this->_isCategory($parentCategory))
		{
			$parentCategory = $this->getCategoryById($parentCategory);
		}

		if ($childCategories = $this->getChildCategories($parentCategory))
		{
			foreach ($childCategories AS $childCategoryId => $childCategory)
			{
				$writer = XenForo_DataWriter::create('Brivium_Store_DataWriter_Category');
				$writer->setExistingData($childCategoryId);
				$writer->setOption(Brivium_Store_DataWriter_Category::OPTION_POST_WRITE_UPDATE_CHILD_CATEGORIES, false);
				$writer->delete();
			}
		}

		if ($rebuildCaches)
		{
			$this->updateNestedSetInfo();
			$this->getModelFromCache('XenForo_Model_Permission')->rebuildPermissionCache();
		}
	}

	/**
	 * Builds lft, rgt and depth values for all categories, based on the parent_category_id and display_order information in the database.
	 * Also rebuilds the effective style ID.
	 *
	 * @param array|null $categoryHierarchy - will be fetched automatically when NULL is provided
	 * @param integer $parentCategoryId
	 * @param integer $depth
	 * @param integer $lft The entry left value; note that this will be changed and returned as the rgt value
	 *
	 * @return array [category_id] => array(lft => int, rgt => int)...
	 */
	public function getNewNestedSetInfo($categoryHierarchy = null, $parentCategoryId = 0, $depth = 0, &$lft = 1)
	{
		$categories = array();

		if ($depth == 0 && !$this->_isCategoryHierarchy($categoryHierarchy))
		{
			$categoryHierarchy = $this->getCategoryHierarchy($categoryHierarchy);
		}

		if (empty($categoryHierarchy[$parentCategoryId]))
		{
			return array();
		}

		foreach ($categoryHierarchy[$parentCategoryId] AS $i => $category)
		{
			$categories[$category['product_category_id']] = $category;
			$categories[$category['product_category_id']]['lft'] = $lft++;
			$categories[$category['product_category_id']]['depth'] = $depth;

			$categories += $this->getNewNestedSetInfo($categoryHierarchy, $category['product_category_id'], $depth + 1, $lft);

			$categories[$category['product_category_id']]['rgt'] = $lft++;
		}

		return $categories;
	}

	/**
	 * Rebuilds and saves nested set info (lft, rgt, depth) for all categories based on parent id and display order
	 *
	 * @return array All categories
	 */
	public function updateNestedSetInfo()
	{
		//TODO: This should probably have a much cleverer system than forcing a complete rebuild of the nested set info...
		$categories = $this->getAllCategories(true);
		$nestedSetInfo = $this->getNewNestedSetInfo($categories);

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		foreach ($nestedSetInfo AS $categoryId => $category)
		{
			/* @var $writer Brivium_Store_DataWriter_Category */
			$writer = XenForo_DataWriter::create('Brivium_Store_DataWriter_Category');

			// we want to set nested set info, so don't prevent it
			$writer->setOption(Brivium_Store_DataWriter_Category::OPTION_ALLOW_NESTED_SET_WRITE, true);

			// prevent any child updates from occuring - we're handling it here
			$writer->setOption(Brivium_Store_DataWriter_Category::OPTION_POST_WRITE_UPDATE_CHILD_CATEGORIES, false);

			// we already have the data, don't go and query it again
			$writer->setExistingData($categories[$categoryId], true);

			$writer->set('lft', $category['lft']);
			$writer->set('rgt', $category['rgt']);
			$writer->set('depth', $category['depth']);

			// fingers crossed...
			$writer->save();
		}

		XenForo_Db::commit($db);

		return $categories;
	}

	/**
	 * Fetches an array suitable as source for admin template 'options' tag from categories array
	 *
	 * @param array Array of categories, including category_id, title, parent_category_id and depth keys
	 * @param integer CategoryId of selected category
	 * @param mixed Add root as the first option, and increment all depths by 1 to show indenting.
	 * 	If 'true', the root category will be entitled '(root category)', alternatively, specify a string to use
	 *  as the option text.
	 *
	 * @return array
	 */
	public function getCategoryOptionsArray(array $categories, $selectedCategoryId = 0, $includeRoot = false)
	{
		$options = array();

		if ($includeRoot !== false)
		{
			$root = $this->getRootCategory();

			$options[0] = array(
				'value' => 0,
				'label' => (is_string($includeRoot) === true ? $includeRoot : $root['category_title']),
				'selected' => (strval($selectedCategoryId) === '0'),
				'depth' => 0,
			);
		}

		foreach ($categories AS $categoryId => $category)
		{
			$category['depth'] += (($includeRoot && $categoryId) ? 1 : 0);

			$options[$categoryId] = array(
				'value' => $categoryId,
				'label' => $category['category_title'],
				'selected' => ($categoryId == $selectedCategoryId),
				'depth' => $category['depth'],
			);
		}

		return $options;
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