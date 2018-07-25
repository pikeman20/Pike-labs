<?php

/**
 * Model for Products.
 *
 * @package Brivium_Store
 */
class Brivium_Store_Model_Product extends XenForo_Model
{
	const FETCH_USER = 0x01;
	const FETCH_CATEGORY = 0x02;

	const FETCH_FULL    = 0x07;

	public function getAllProducts()
	{
		if (($products = $this->_getLocalCacheData('allProducts')) === false)
		{
			$products = $this->fetchAllKeyed('
				SELECT *
				FROM xf_store_product
				ORDER BY display_order ASC
			', 'product_id');

			$this->setLocalCacheData('allProducts', $products);
		}
		return $products;

	}

	/**
	*	get Category by its id
	* 	@param integer $productId
	* 	@param array $fetchOptions Collection of options related to fetching
	*	@return array|false Category info
	*/
	public function getProductById($productId,$fetchOptions = array()){
		$joinOptions = $this->prepareProductFetchOptions($fetchOptions);
		return $this->_getDb()->fetchRow('
			SELECT product.*
			' .$joinOptions['selectFields']. '
			FROM xf_store_product AS product
			' .$joinOptions['joinTables']. '
			WHERE product.product_id = ?
		',$productId);
	}

	/**
	*	Gets multi products.
	*
	*	@param array $productIds
	*	@param array $fetchOptions Collection of options related to fetching
	*
	*	@return array Format: [product id] => info
	*/
	public function getProductsByIds(array $productIds)
	{
		if (!$productIds)
		{
			return array();
		}
		return $this->fetchAllKeyed('
			SELECT product.*
			FROM xf_store_product AS product
			WHERE product.product_id IN (' . $this->_getDb()->quote($productIds) . ')
		', 'product_id');
	}

	/**
	 * Prepares a collection of product fetching related conditions into an SQL clause
	 *
	 * @param array $conditions List of conditions
	 * @param array $fetchOptions Modifiable set of fetch options (may have joins pushed on to it)
	 *
	 * @return string SQL clause (at least 1=1)
	 */
	public function prepareProductConditions(array $conditions, array &$fetchOptions)
	{
		$sqlConditions = array();
		$db = $this->_getDb();

		if (!empty($conditions['title']))
		{
			if (is_array($conditions['title']))
			{
				$sqlConditions[] = 'product.title LIKE ' . XenForo_Db::quoteLike($conditions['title'][0], $conditions['title'][1], $db);
			}
			else
			{
				$sqlConditions[] = 'product.title LIKE ' . XenForo_Db::quoteLike($conditions['title'], 'lr', $db);
			}
		}
		if (!empty($conditions['product_id']))
		{
			if (is_array($conditions['product_id']))
			{
				$sqlConditions[] = 'product.product_id IN (' . $db->quote($conditions['product_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'product.product_id = ' . $db->quote($conditions['product_id']);
			}
		}
		if (!empty($conditions['product_category_id']))
		{
			if (is_array($conditions['product_category_id']))
			{
				$sqlConditions[] = 'product.product_category_id IN (' . $db->quote($conditions['product_category_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'product.product_category_id = ' . $db->quote($conditions['product_category_id']);
			}
		}
		if (!empty($conditions['user_id']))
		{
			if (is_array($conditions['user_id']))
			{
				$sqlConditions[] = 'product.user_id IN (' . $db->quote($conditions['user_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'product.user_id = ' . $db->quote($conditions['user_id']);
			}
		}

		if (isset($conditions['sticky']))
		{
			$sqlConditions[] = 'product.sticky = ' . ($conditions['sticky'] ? 1 : 0);
		}
		if (isset($conditions['display_in_list']))
		{
			$sqlConditions[] = 'product.display_in_list = ' . ($conditions['display_in_list'] ? 1 : 0);
		}
		if (!empty($conditions['buy_count']) && is_array($conditions['buy_count']))
		{
			list($operator, $cutOff) = $conditions['buy_count'];

			$this->assertValidCutOffOperator($operator);
			$sqlConditions[] = "product.buy_count $operator " . $db->quote($cutOff);
		}
		if (!empty($conditions['product_date']) && is_array($conditions['product_date']))
		{
			list($operator, $cutOff) = $conditions['product_date'];

			$this->assertValidCutOffOperator($operator);
			$sqlConditions[] = "product.product_date $operator " . $db->quote($cutOff);
		}
		if (!empty($conditions['start']))
		{
			$sqlConditions[] = 'product.product_date >= ' . $db->quote($conditions['start']);
		}

		if (!empty($conditions['end']))
		{
			$sqlConditions[] = 'product.product_date <= ' . $db->quote($conditions['end']);
		}
		if (!empty($conditions['product_type_id']))
		{
			$sqlConditions[] = 'product.product_type_id = ' . $db->quote($conditions['product_type_id']);
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	public function prepareProductFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$orderBy = '';
		$orderBySecondary = ', product.sticky DESC';
		if (!empty($fetchOptions['order']))
		{
			switch ($fetchOptions['order'])
			{
				case 'sticky':
				case 'buy_count':
				case 'cost_amount':
				case 'quantity':
				case 'product_date':
					$orderBy = 'product.' . $fetchOptions['order'];
					$orderBySecondary = ', product.product_date DESC';
					break;
				default:
					$orderBy = 'product.product_date';
			}
			if (!isset($fetchOptions['orderDirection']) || $fetchOptions['orderDirection'] == 'desc')
			{
				$orderBy .= ' DESC';
			}
			else
			{
				$orderBy .= ' ASC';
			}
			$orderBy .= $orderBySecondary;
		}

		if (!empty($fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::FETCH_CATEGORY)
			{
				$selectFields .= ',category.category_title';
				$joinTables .= '
					LEFT JOIN xf_store_category AS category ON
						(category.product_category_id = product.product_category_id)';
			}
			if ($fetchOptions['join'] & self::FETCH_USER)
			{
				$selectFields .= ',
					user.*, IF(user.username IS NULL, product.username, user.username) AS username, product.currency_id';
				$joinTables .= '
					LEFT JOIN xf_user AS user ON
						(user.user_id = product.user_id)';
			}
			if (isset($fetchOptions['purchaseUserId']))
			{
				$fetchOptions['purchaseUserId'] = intval($fetchOptions['purchaseUserId']);
				if ($fetchOptions['purchaseUserId'])
				{
					// note: quoting is skipped; intval'd above
					$selectFields .= ',
						IF(product_purchase.user_id IS NOT NULL, 1, 0) AS is_purchased';
					$joinTables .= '
						LEFT JOIN xf_store_product_purchase_active AS product_purchase ON
							(product_purchase.product_id = product.product_id AND product_purchase.user_id = ' . $fetchOptions['purchaseUserId'] . ')';
				}
				else
				{
					$selectFields .= ',
						0 AS is_purchased';
				}
			}

		}
		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables,
			'orderClause'  => ($orderBy ? "ORDER BY $orderBy" : '')
		);
	}

	/**
	 * Gets products that match the given conditions.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * @return array Format: [product id] => info
	 */
	public function getProducts(array $conditions, array $fetchOptions = array())
	{
		$whereConditions = $this->prepareProductConditions($conditions, $fetchOptions);

		$sqlClauses = $this->prepareProductFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		return $this->fetchAllKeyed($this->limitQueryResults(			'
				SELECT product.*
					' . $sqlClauses['selectFields'] . '
				FROM xf_store_product AS product
				' . $sqlClauses['joinTables'] . '
				WHERE ' . $whereConditions . '
				' . $sqlClauses['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'product_id');
	}

	/**
	 * Gets the count of products with the specified criteria.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 *
	 * @return integer
	 */
	public function countProducts(array $conditions)
	{
		$fetchOptions = array();
		$whereConditions = $this->prepareProductConditions($conditions, $fetchOptions);
		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xf_store_product AS product
			WHERE ' . $whereConditions . '
		');
	}


	/**
	 * Gets products that belong to the specified category.
	 *
	 * @param integer $categoryId
	 * @param array $conditions Conditions to apply to the fetching
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * @return array Format: [product id] => info
	 */
	public function getProductsInCategory($categoryId, array $conditions = array(), array $fetchOptions = array())
	{
		$conditions['product_category_id'] = $categoryId;
		return $this->getProducts($conditions, $fetchOptions);
	}

	/**
	 * Gets all sticky products in a particular category.
	 *
	 * @param integer $categoryId
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * @return array Format: [product id] => info
	 */
	public function getStickyProductsInCategory($categoryId, array $conditions = array(), array $fetchOptions = array())
	{
		$conditions['product_category_id'] = $categoryId;
		$conditions['sticky'] = 1;
		return $this->getProducts($conditions, $fetchOptions);
	}

	/**
	 * Gets the count of products in the specified category.
	 *
	 * @param integer $categoryId
	 * @param array $conditions Conditions to apply to the fetching
	 *
	 * @return integer
	 */
	public function countProductsInCategory($categoryId, array $conditions = array())
	{
		$conditions['product_category_id'] = $categoryId;
		return $this->countProducts($conditions);
	}


	public function prepareProducts(array $products)
	{
		if(!$products) return array();
		foreach($products AS &$product){
			$product = $this->prepareProduct($product);
		}
		return $products;
	}
	public function prepareProduct(array $product)
	{
		//$product['currency'] = strtoupper($product['cost_currency']);

		switch ($product['length_unit'])
		{
			case 'day': $product['lengthUnitPP'] = 'D'; break;
			case 'month': $product['lengthUnitPP'] = 'M'; break;
			case 'year': $product['lengthUnitPP'] = 'Y'; break;
			default: $product['lengthUnitPP'] = ''; break;
		}

		$cost = Brivium_Store_EventListeners_Helpers::helperStoreCostFormat($product['cost_amount'],$product['product_id'],$product['money_type'],$product['currency_id'],$product);
		//prd($product);
		if ($product['length_unit'])
		{
			if ($product['length_amount'] > 1)
			{
				if ($product['recurring'])
				{
					$product['costPhrase'] = new XenForo_Phrase("BRS_x_per_y_z", array(
						'cost' => $cost,
						'length' => $product['length_amount'],
						'unit' => $product['length_unit']
					));
				}
				else
				{
					$product['costPhrase'] = new XenForo_Phrase("BRS_x_for_y_z", array(
						'cost' => $cost,
						'length' => $product['length_amount'],
						'unit' => $product['length_unit']
					));
				}
			}
			else
			{
				if ($product['recurring'])
				{
					$product['costPhrase'] = new XenForo_Phrase("BRS_x_per_y", array(
						'cost' => $cost,
						'unit' => $product['length_unit']
					));
				}
				else
				{
					$product['costPhrase'] = new XenForo_Phrase("BRS_x_for_one_y", array(
						'cost' => $cost,
						'unit' => $product['length_unit']
					));
				}
			}
		}
		else
		{
			$product['costPhrase'] = $cost;
		}
		return $product;
	}

	/**
	 * Gets the category counters for the specified category.
	 *
	 * @param integer $categoryId
	 *
	 * @return array Keys: discussion_count, message_count
	 */
	public function getCategoryCounters($categoryId)
	{
		return $this->_getDb()->fetchRow('
			SELECT
				COUNT(*) AS product_count,
				COUNT(*) + SUM(buy_count) AS buy_count
			FROM xf_store_product AS product
			WHERE product_category_id = ?
		', $categoryId);
	}

	/**
	 * Logs purchased record of a product.
	 *
	 * @param varchar $productId
	 */
	public function updateProductBuyCount($productId, $adjust = 1)
	{
		$this->_getDb()->query('
			UPDATE xf_store_product SET
				buy_count = buy_count + ?
			WHERE product_id = ?
		',array($adjust, $productId));
	}

		/**
	 * Gets all products in the format expected by the product cache.
	 *
	 * @return array Format: [product id] => info, with phrase cache as array
	 */
	public function getAllProductsForCache()
	{
		$this->resetLocalCacheData('allProducts');
		$products = $this->getProducts(array(), array('join' => Brivium_Store_Model_Product::FETCH_FULL));
		return $products;
	}

	/**
	 * Rebuilds the full Product cache.
	 *
	 * @return array Format: [product id] => info, with phrase cache as array
	 */
	public function rebuildProductCache()
	{
		$this->resetLocalCacheData('allProducts');

		$products = $this->getAllProductsForCache();

		$this->_getDataRegistryModel()->set('brsProducts', $products);

		return $products;
	}

	/**
	 * Rebuilds all product caches.
	 */
	public function rebuildProductCaches()
	{
		$this->rebuildProductCache();
	}

	public function canPurchaseProduct(array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		return XenForo_Permission::hasPermission($viewingUser['permissions'], 'BR_storePermission', 'purchase');
	}
	public function canGiftProduct(array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		return XenForo_Permission::hasPermission($viewingUser['permissions'], 'BR_storePermission', 'gift');
	}

	public function canBuyProduct($userId, array $product,&$errorString)
	{
		if(!$product)return false;
		if(!$this->canPurchaseProduct()){
			$errorString = new XenForo_Phrase('do_not_have_permission');
			return false;
		}
		$productPurchaseModel = $this->_getProductPurchaseModel();
		$productType = XenForo_Application::get('brsProductTypes')->$product['product_type_id'];
		$purchased = $productPurchaseModel->getActiveProductPurchase($userId,$product['product_id']);
		if($purchased){
			if(!$product['length_unit'] || !$product['recurring']){
				$errorString = new XenForo_Phrase('BRS_you_already_purchased_this_product');
				return false;
			}
		}else{
			if($product['quantity'] == 0){
				$errorString = new XenForo_Phrase('BRS_this_produt_out_of_stock');
				return false;
			}
			if($productType['purchase_type']=='only_one'){
				$conditions = array(
					'active'	=> true,
					'user_id'	=> $userId,
					'product_type_id'	=> $product['product_type_id'],
				);
				if($productPurchaseModel->countProductPurchaseRecords($conditions)){
					$errorString = new XenForo_Phrase('BRS_you_can_buy_only_one_product_of_x',array('type' => $productType['title']));
					return false;
				}
			}
			if(!empty($product['product_unique'])){
				$conditions = array(
					'active'	=> true,
					'user_id'	=> $userId,
					'product_unique'	=> $product['product_unique'],
				);
				if($productPurchaseModel->countProductPurchaseRecords($conditions)){
					$errorString = new XenForo_Phrase('BRS_you_already_purchased_same_kind_of_this_product');
					return false;
				}
			}

			if(!empty($product['permissions'])){
				$permissionModel = $this->_getPermissionModel();
				$productPermissions = unserialize($product['permissions']);
				//prd($productPermissions);
				if($productPermissions && is_array($productPermissions)){
					foreach ($productPermissions AS $permissionGroupId=>$permissionList)
					{
						if($permissionList && is_array($permissionList)){
							foreach ($permissionList AS $permissionId=>$permission){
								$userPermission = XenForo_Visitor::getInstance()->hasPermission($permissionGroupId, $permissionId);
								if(($permission =='allow' && $userPermission) || ($userPermission==-1 || $permission <= $userPermission)){
									$errorString = new XenForo_Phrase('BRS_you_already_had_permission_of_this_product');
									return false;
								}
							}
						}
					}
				}
			}
		}
		return $purchased;
	}
	public function canSendGiftProduct($userId, $receiveUserId, array $product,&$errorString)
	{
		if(!$product)return false;
		if(!$product['can_gift']){
			$errorString = new XenForo_Phrase('BRS_you_cant_gift_this_product');
			return false;
		}
		$productPurchaseModel = $this->_getProductPurchaseModel();
		$productType = XenForo_Application::get('brsProductTypes')->$product['product_type_id'];
		$purchased = $productPurchaseModel->getActiveProductPurchase($receiveUserId,$product['product_id']);
		if($purchased){
			$errorString = new XenForo_Phrase('BRS_receiver_already_purchased_this_product');
			return false;
		}else{
			if($product['quantity'] == 0){
				$errorString = new XenForo_Phrase('BRS_this_produt_out_of_stock');
				return false;
			}
			if($productType['purchase_type']=='only_one'){
				$conditions = array(
					'active'	=> true,
					'user_id'	=> $receiveUserId,
					'product_type_id'	=> $product['product_type_id'],
				);
				if($productPurchaseModel->countProductPurchaseRecords($conditions)){
					$errorString = new XenForo_Phrase('BRS_receiver_can_have_only_one_product_of_x',array('type' => $productType['title']));
					return false;
				}
			}
			if(!empty($product['product_unique'])){
				$conditions = array(
					'active'	=> true,
					'user_id'	=> $receiveUserId,
					'product_unique'	=> $product['product_unique'],
				);
				if($productPurchaseModel->countProductPurchaseRecords($conditions)){
					$errorString = new XenForo_Phrase('BRS_receiver_already_purchased_same_kind_of_this_product');
					return false;
				}
			}
		}
		return $purchased;
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
	 * Gets the product purchase model.
	 *
	 * @return Brivium_Store_Model_ProductPurchase
	 */
	protected function _getProductPurchaseModel()
	{
		return $this->getModelFromCache('Brivium_Store_Model_ProductPurchase');
	}
}

?>