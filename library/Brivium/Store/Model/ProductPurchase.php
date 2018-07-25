<?php

/**
 * Model for ProductPurchase.
 *
 * @package Brivium_Store
 */
class Brivium_Store_Model_ProductPurchase extends XenForo_Model
{
	/**
	 * Joins the upgrade details to a user-specific upgrade record.
	 *
	 * @var int
	 */
	const JOIN_PRODUCT = 0x01;
	const FETCH_GIFTED = 0x02;
	/**
	 * Gets the specified purchase records. Queries active and expired records.
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 *
	 * @return array [purchase id]
	 */
	public function getProductPurchaseRecords(array $conditions = array(), array $fetchOptions = array())
	{
		$baseTable = (empty($conditions['active']) ? 'store_product_purchase_expired' : 'store_product_purchase_active');
		$whereClause = $this->prepareProductPurchaseRecordConditions($conditions, $baseTable, $fetchOptions);
		$sqlClauses = $this->prepareProductPurchaseRecordFetchOptions($fetchOptions, $baseTable);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT ' . $baseTable . '.*,
					user.*
				' . $sqlClauses['selectFields'] . '
				FROM xf_' . $baseTable . ' AS ' . $baseTable . '
				LEFT JOIN xf_user AS user ON (' . $baseTable . '.user_id = user.user_id)
				' . $sqlClauses['joinTables'] . '
				WHERE ' . $whereClause . '
				' . $sqlClauses['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'product_purchase_id');
	}

	/**
	 * Counts the number of product purchase records matching the conditions.
	 *
	 * @param array $conditions
	 *
	 * @return integer
	 */
	public function countProductPurchaseRecords(array $conditions = array())
	{
		$baseTable = (empty($conditions['active']) ? 'store_product_purchase_expired' : 'store_product_purchase_active');

		$fetchOptions = array();
		$whereClause = $this->prepareProductPurchaseRecordConditions($conditions, $baseTable, $fetchOptions);
		$sqlClauses = $this->prepareProductPurchaseRecordFetchOptions($fetchOptions, $baseTable);

		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xf_' . $baseTable . ' AS ' . $baseTable . '
			' . $sqlClauses['joinTables'] . '
			WHERE ' . $whereClause
		);
	}

	/**
	 * Prepares a list of product purchase record conditions.
	 *
	 * @param array $conditions
	 * @param string $baseTable Base table to query against
	 * @param array $fetchOptions
	 *
	 * @return string
	 */
	public function prepareProductPurchaseRecordConditions(array $conditions, $baseTable, array &$fetchOptions)
	{
		$db = $this->_getDb();
		$sqlConditions = array();

		if (!empty($conditions['product_purchase_id']))
		{
			if (is_array($conditions['product_purchase_id']))
			{
				$sqlConditions[] = $baseTable . '.product_purchase_id IN (' . $db->quote($conditions['product_purchase_id']) . ')';
			}
			else
			{
				$sqlConditions[] = $baseTable . '.product_purchase_id = ' . $db->quote($conditions['product_purchase_id']);
			}
		}
		if (!empty($conditions['product_id']))
		{
			if (is_array($conditions['product_id']))
			{
				$sqlConditions[] = $baseTable . '.product_id IN (' . $db->quote($conditions['product_id']) . ')';
			}
			else
			{
				$sqlConditions[] = $baseTable . '.product_id = ' . $db->quote($conditions['product_id']);
			}
		}
		if (!empty($conditions['user_id']))
		{
			if (is_array($conditions['user_id']))
			{
				$sqlConditions[] = $baseTable . '.user_id IN (' . $db->quote($conditions['user_id']) . ')';
			}
			else
			{
				$sqlConditions[] = $baseTable . '.user_id = ' . $db->quote($conditions['user_id']);
			}
		}
		if (!empty($conditions['current']))
		{
			$sqlConditions[] = $baseTable . '.current = 1';
		}
		if (!empty($conditions['product_type_id']))
		{
			if (is_array($conditions['product_type_id']))
			{
				$sqlConditions[] = $baseTable . '.product_type_id IN (' . $db->quote($conditions['product_type_id']) . ')';
			}
			else
			{
				$sqlConditions[] = $baseTable . '.product_type_id = ' . $db->quote($conditions['product_type_id']);
			}
		}

		if (isset($conditions['gifted_user_id']))
		{
			$sqlConditions[] = $baseTable . '.gifted_user_id = ' . $db->quote($conditions['gifted_user_id']);
		}
		if (!empty($conditions['product_unique']))
		{
			if (is_array($conditions['product_unique']))
			{
				$sqlConditions[] = $baseTable . '.product_unique IN (' . $db->quote($conditions['product_unique']) . ')';
			}
			else
			{
				$sqlConditions[] = $baseTable . '.product_unique = ' . $db->quote($conditions['product_unique']);
			}
		}
		return $this->getConditionsForClause($sqlConditions);
	}

	/**
	 * Prepares product purchase record fetch options.
	 *
	 * @param array $fetchOptions
	 * @param string $baseTable Base table to query against
	 *
	 * @return array
	 */
	public function prepareProductPurchaseRecordFetchOptions(array $fetchOptions, $baseTable)
	{
		$selectFields = '';
		$joinTables = '';
		$orderBy = '';
		if (!empty($fetchOptions['order']))
		{
			switch ($fetchOptions['order'])
			{
				case 'last_purchase':
				case 'remained':
				case 'start_date':
				case 'end_date':
					$orderBy = '' . $baseTable . '.' . $fetchOptions['order'];
					break;
				default:
					$orderBy = '' . $baseTable . '.last_purchase';
			}
			if (!isset($fetchOptions['orderDirection']) || $fetchOptions['orderDirection'] == 'desc')
			{
				$orderBy .= ' DESC';
			}
			else
			{
				$orderBy .= ' ASC';
			}
		}
		if (!empty($fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::JOIN_PRODUCT)
			{
				$selectFields .= ',
					product.title, product.product_type_id
					';
				$joinTables .= '
					LEFT JOIN xf_store_product AS product ON
						(product.product_id = ' . $baseTable . '.product_id)';
			}
			if ($fetchOptions['join'] & self::FETCH_GIFTED)
			{
				$selectFields .= ',
					user_gifted.username AS gifted_username
					';
				$joinTables .= '
					LEFT JOIN xf_user AS user_gifted ON
						(user_gifted.user_id = ' . $baseTable . '.gifted_user_id)';
			}
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables,
			'orderClause'  => ($orderBy ? "ORDER BY $orderBy" : '')
		);
	}

	/**
	 * Gets the active purchase records for the specified user.
	 *
	 * @param integer $userId
	 *
	 * @return array [purchase id] => info (note, the id of the purchase; not the user-specific record!)
	 */
	public function getActiveProductPurchasesForUser($userId)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_store_product_purchase_active
			WHERE user_id = ?
		', 'product_purchase_id', $userId);
	}

	/**
	 * Gets the specified active product purchase record, based on product and purchase.
	 *
	 * @param integer $userId
	 * @param integer $productId
	 *
	 * @return array|false
	 */
	public function getActiveProductPurchase($userId, $productId)
	{
		return $this->_getDb()->fetchRow('
			SELECT product_purchase_active.*
			FROM xf_store_product_purchase_active AS product_purchase_active
			WHERE product_purchase_active.user_id = ?
				AND product_purchase_active.product_id = ?
		', array($userId, $productId));
	}

	public function getActiveGiftedProductPurchase($userId, $productId)
	{
		return $this->_getDb()->fetchRow('
			SELECT product_purchase_active.*
			FROM xf_store_product_purchase_active AS product_purchase_active
			WHERE product_purchase_active.gifted_user_id = ?
				AND product_purchase_active.product_id = ?
		', array($userId, $productId));
	}

	/**
	 * Gets the specified active purchase records.
	 *
	 * @param integer $productPurchaseId
	 *
	 * @return array|false
	 */
	public function getActiveProductPurchaseById($productPurchaseId)
	{
		return $this->_getDb()->fetchRow('
			SELECT product_purchase_active.*,
				user.*
			FROM xf_store_product_purchase_active AS product_purchase_active
			INNER JOIN xf_user AS user ON
				(user.user_id = product_purchase_active.user_id)
			WHERE product_purchase_active.product_purchase_id = ?
		', $productPurchaseId);
	}

	/**
	 * Returns true if the user can use product_type.
	 *
	 * @return boolean
	 */
	public function getProductPurchaseByOnlyType($productType = '',&$errorString = null,$active=true,$current=true)
	{
		if(!$productType)	return false;
		$conditions = array(
			'product_type_id'	=> $productType
		);
		if($active)$conditions['active'] = $active;
		if($current)$conditions['current'] = $current;
		$fetchOptions = array(
			'join'	=> Brivium_Store_Model_ProductPurchase::JOIN_PRODUCT,
			'order'	=> 'last_purchase',
		);
		$productActives = $this->getProductPurchaseRecords($conditions, $fetchOptions);
		return $productActives;
	}

	/**
	 * Upgrades the user with the specified product.
	 *
	 * @param integer $userId
	 * @param array $product Info about product to apply
	 * @param boolean $allowInsertUnpurchasable Allow insert of a new purchase even if not purchasable
	 * @param boolean $recurring Allow auto renew purchase.
	 * @param integer|null $times Forces a specific times; if null, don't overwrite
	 * @param integer|null $endDate Forces a specific end date; if null, don't overwrite
	 *
	 * @return integer|false Product purchase record ID
	 */
	public function processPurchase($userId, array $product, $recurring = false, $giftedUserId = 0, &$errorString = '', $recurringType = '', $times = null, $endDate = null)
	{
		$db = $this->_getDb();
		$active = $this->getActiveProductPurchase($userId, $product['product_id']);
		$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($userId);
		$giftedUser = array();
		if($giftedUserId){
			$giftedUser = $this->getModelFromCache('XenForo_Model_User')->getUserById($giftedUserId);
		}
		$message = '['.$product['title'].']';
		if ($active)
		{
			$updateArray = array();
			// updating an existing purchase - if no end date override specified, extend the purchase
			$activeExtra = unserialize($active['extra']);
			$updateArray = array();
			if ($times === null){
				if (!$activeExtra['length_unit']|| $activeExtra['length_unit']!='time'){
					$times = 0;
				}else{
					$times = $active['remained'] + $activeExtra['length_amount'];
				}

			}else{
				$times = intval($times);
			}
			if ($times != $active['remained']){
				$updateArray['remained'] = $times;
			}

			if ($endDate === null){
				if ($active['end_date'] == 0 || !$activeExtra['length_unit']|| $activeExtra['length_unit']=='time'){
					$endDate = 0;
				}else{
					$endDate = strtotime('+' . $activeExtra['length_amount'] . ' ' . $activeExtra['length_unit'], $active['end_date']);
				}
			}else{
				$endDate = intval($endDate);
			}

			if ($endDate != $active['end_date']){
				$updateArray['end_date'] = $endDate;
			}

			if ($updateArray){
				$isGift = false;
				if($giftedUser && !empty($activeExtra['recurring_type']) && $activeExtra['recurring_type']=='gifted_user'){
					$message = new XenForo_Phrase('BRS_recurring_purchase_for_x_of_y_as_gift',array('product'=>$product['title'],'username'=>$user['username']));
					if($errorString = $this->_processPurchaseMoney($giftedUser, $product, $product['product_type_id'], $message->render())){
						return false;
					}
					if($product['money_type'] == 'trophy_points'){
						$this->createAlert($giftedUser['user_id'], $user['user_id'], $user['username'], $product['product_id'], 'recurring_purchase',$activeExtra);
					}
					$isGift = true;
				}else{
					$message = new XenForo_Phrase('BRS_recurring_purchase_for_x',array('product'=>$product['title']));
					if($errorString = $this->_processPurchaseMoney($user, $product, $product['product_type_id'], $message->render())){
						return false;
					}
					if($product['money_type'] == 'trophy_points'){
						$this->createAlert($userId, $user['user_id'], $user['username'], $product['product_id'], 'recurring_purchase',$activeExtra);
					}
				}

				$this->_processProductChange($user, $product, $product['product_type_id'], $active);
				$updateArray['last_purchase'] = XenForo_Application::$time;
				$updateArray['money_type'] = $product['money_type'];
				$db->update('xf_store_product_purchase_active',
					$updateArray,
					'product_purchase_id = ' . $db->quote($active['product_purchase_id'])
				);
				$activeExtra['product'] = $product;
				if(!$this->logProductBuy($userId,$activeExtra,$active['product_purchase_id']))return false;

				$this->_getProductModel()->updateProductBuyCount($product['product_id']);
				return $active['product_purchase_id'];
			}
			return false;
		}
		else
		{
			// inserting a new new purchase
			if ($times === null){
				if (!$product['length_unit']||$product['length_unit']!='time'){
					$times = 0;
				}else{
					$times = $product['length_amount'];
				}
			}else{
				$times = intval($times);
			}
			if ($endDate === null){
				if (!$product['length_unit']||$product['length_unit']=='time'){
					$endDate = 0;
				}else{
					$endDate = strtotime('+' . $product['length_amount'] . ' ' . $product['length_unit']);
				}
			}else{
				$endDate = intval($endDate);
			}

			$isGift = false;
			if($giftedUser){
				$isGift = true;
				$message = new XenForo_Phrase('BRS_you_gifted_x_for_y',array('title'=>$product['title'],'username'=>$user['username']));
				if($errorString = $this->_processPurchaseMoney($giftedUser, $product, $product['product_type_id'], $message->render())){
					return false;
				}
			}else{
				if($errorString = $this->_processPurchaseMoney($user, $product, $product['product_type_id'], $message)){
					return false;
				}
			}

			$extra = array(
				'cost_amount' => $product['cost_amount'],
				'length_amount' => $product['length_amount'],
				'length_unit' => $product['length_unit'],
				'recurring_type' => $recurringType,
				'is_gift' => $isGift,
				'gifted_user' => $giftedUser,
				'user' => $user,
			);
			$this->_processProductChange($user, $product, $product['product_type_id']);
			XenForo_Db::beginTransaction($db);
			$productType = XenForo_Application::get('brsProductTypes')->$product['product_type_id'];
			if($productType['purchase_type']=='use_one'){
				$db->update('xf_store_product_purchase_active',
					array('current'=>0),
					'product_type_id = ' . $db->quote($product['product_type_id']) .' AND user_id = ' . $db->quote($userId)
				);
			}
			$db->insert('xf_store_product_purchase_active', array(
				'user_id' => $userId,
				'product_id' => $product['product_id'],
				'product_type_id' => $product['product_type_id'],
				'extra' => serialize($extra),
				'product_unique' => $product['product_unique'],
				'money_type' => $product['money_type'],
				'remained' => $times,
				'current' => 1,
				'recurring' => $recurring,
				'gifted_user_id' => $giftedUserId,
				'start_date' => XenForo_Application::$time,
				'last_purchase' => XenForo_Application::$time,
				'end_date' => $endDate
			));
			$purchaseRecordId = $db->lastInsertId();
			$product = $this->_getProductModel()->prepareProduct($product);
			$extra['product'] = $product;
			if(!$this->logProductBuy($userId,$extra,$purchaseRecordId)){
				return false;
			}
			if($giftedUser){
				$extra['gift_type'] = 'receiver';
				$this->createAlert($userId, $giftedUser['user_id'], $giftedUser['username'], $product['product_id'], 'gifted',$extra);
				$extra['gift_type'] = 'sender';
				$this->createAlert($giftedUser['user_id'], $user['user_id'], $user['username'], $product['product_id'], 'gifted',$extra);
				if($product['money_type'] == 'trophy_points'){
					$this->createAlert($giftedUser['user_id'], $user['user_id'], $user['username'], $product['product_id'], 'purchase_product',$extra);
				}
			}else{
				if($product['money_type'] == 'trophy_points'){
					$this->createAlert($userId, $user['user_id'], $user['username'], $product['product_id'], 'purchase_product',$extra);
				}
			}
			$this->_getProductModel()->updateProductBuyCount($product['product_id']);
			XenForo_Db::commit($db);
			return $purchaseRecordId;
		}
		return false;
	}

	protected function _processProductChange($user, array $product, $productTypeId, $existingPurchased = null)
	{
		return true;
	}

	public function getLogProductChange($userId, $productId)
	{
		$productChange = $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_store_product_change
			WHERE user_id = ? AND product_id = ?
		', array($userId, $productId));
		if($productChange && !empty($productChange['change_data'])){
			$productChange['change_data'] = @unserialize($productChange['change_data']);
		}
		return $productChange;
	}

	public function logProductChange($userId, $productId, $changeData)
	{
		if (!is_string($changeData))
		{
			$changeData = serialize($changeData);
		}
		$this->_getDb()->query('
			INSERT INTO xf_store_product_change
				(user_id, product_id, change_data)
			VALUES
				(?, ?, ?)
			ON DUPLICATE KEY UPDATE change_data = VALUES(change_data)
		', array($userId, $productId, $changeData));
	}

	public function deleteProductChange($userId, $productId)
	{
		$this->_getDb()->delete('xf_store_product_change', array(
			'user_id' => $userId,
			'product_id' => $productId,
		));
	}

	protected function _processPurchaseMoney($user, array $product, $productTypeId, $message)
	{
		if(!empty($product['money_type'])){
			switch($product['money_type']){
				case 'brivium_credit_premium':
					$actionId = $this->getCreditActionId($productTypeId);
					return $this->_processPurchaseCreditPremium($user,$product, $productTypeId, $actionId, $message);
					break;
				case 'brivium_credit_free':
					$actionId = $this->getCreditActionId($productTypeId);
					return $this->_processPurchaseCreditFree($user,$product, $productTypeId, $actionId, $message);
					break;
				case 'trophy_points':
					return $this->_processPurchaseTrophy($user,$product, $productTypeId);
					break;
			}
		}
		return new XenForo_Phrase('BRS_you_cant_purchase_this_product');
	}

	public function getCreditActionId($productTypeId)
	{
		return '';
	}

	protected function _processPurchaseCreditPremium($user, array $product, $productTypeId, $actionId, $message)
	{
		if(!$actionId){
			return new XenForo_Phrase('BRS_you_cant_purchase_this_product');
		}
		if(!$this->canPurchaseCreditPremium($errorString,$actionId)){
			return $errorString;
		}

		$creditModel = $this->getModelFromCache('Brivium_Credits_Model_Credit');
		$dataCredit = array(
			'amount' 			=>	-$product['cost_amount'],
			'user'				=>	$user,
			'currency_id'		=>	$product['currency_id'],
			'content_id' 		=>	$product['product_id'],
			'content_type'		=>	'product',
			'message'			=>	$message,
		);

		if(!$creditModel->updateUserCredit($actionId,$user['user_id'],$dataCredit,$errorString)){
			return $errorString;
		}
		return false;
	}

	protected function _processPurchaseCreditFree($user, array $product, $productTypeId, $actionId, $message)
	{
		if(!$actionId) return new XenForo_Phrase('BRS_you_cant_purchase_this_product');
		if(!$this->canPurchaseCreditFree($errorString,$actionId)) return $errorString;
		$creditModel = $this->getModelFromCache('Brivium_Credits_Model_Credit');
		$dataCredit = array(
			'amount' 			=>	-$product['cost_amount'],
			'user'				=>	$user,
			'currency_id'		=>	$product['currency_id'],
			'content_id' 		=>	$product['product_id'],
			'content_type'		=>	'product',
			'message'			=>	$message,
		);
		$creditModel->updateUserCredit($actionId,$user['user_id'],$dataCredit,$errorString);
		if($errorString)return $errorString;
		return false;
	}

	public function canPurchaseCreditPremium(&$errorString, $actionId = '')
	{
		$xenAddons = XenForo_Application::get('addOns');
		if(!empty($xenAddons['Brivium_Credits']) && $xenAddons['Brivium_Credits'] >= 2000000){
			if($actionId && !XenForo_Application::get('brcActionHandler')->getActionEvents($actionId)){
				$errorString = new XenForo_Phrase('BRS_you_must_create_credit_event_for_this_product_type');
				return false;
			}
			return true;
		}else if(!empty($xenAddons['Brivium_Credits']) && $xenAddons['Brivium_Credits'] >= 1000000){
			if($actionId && !XenForo_Application::get('brcEvents')->$actionId){
				$errorString = new XenForo_Phrase('BRS_you_must_create_credit_event_for_this_product_type');
				return false;
			}
			return true;
		}else{
			$errorString = new XenForo_Phrase('BRS_brivium_credit_premium_required');
			return false;
		}
	}

	public function canPurchaseCreditFree(&$errorString, $actionId = '')
	{
		$xenAddons = XenForo_Application::get('addOns');
		if(!empty($xenAddons['Brivium_Credits']) && $xenAddons['Brivium_Credits'] < 1000000){
			if($actionId && !XenForo_Application::get('brcActions')->$actionId){
				$errorString = new XenForo_Phrase('BRS_you_must_create_credit_action_for_this_product_type');
				return false;
			}
			return true;
		}else{
			$errorString = new XenForo_Phrase('BRS_brivium_credit_free_required');
			return false;
		}
	}

	protected function _processPurchaseTrophy($user, array $product, $productTypeId)
	{
		if(!empty($user['trophy_points']) && $user['trophy_points'] > 0 && ($user['trophy_points'] - $product['cost_amount']) >= 0){
			$this->_getDb()->update('xf_user',
				array('trophy_points' => $user['trophy_points'] - $product['cost_amount']),
				'user_id = ' . $this->_getDb()->quote($user['user_id'])
			);
			return;
		}
		return new XenForo_Phrase('BRS_you_cant_purchase_this_product');
	}

	public function createAlert($alertUserId, $userId, $username, $productId, $action,array $extraData = null)
	{
		XenForo_Model_Alert::alert(
			$alertUserId,
			$userId,
			$username,
			'store',
			$productId,
			$action,
			$extraData
		);
	}

	public function logProductBuy($userId, $extra,$purchaseRecordId)
	{
		$extra['product_purchase_id'] = $purchaseRecordId;
		//if(isset($extra)) $extra = serialize($extra);

		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
			$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ipAddress = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false);
		}
		if (is_string($ipAddress) && strpos($ipAddress, '.'))
		{
			$ipAddress = ip2long($ipAddress);
			$ipAddress = sprintf('%u', $ipAddress);
		}
		else
		{
			$ipAddress = 0;
		}

		$this->_getDb()->insert('xf_store_transaction', array(
			'user_id' => $userId,
			'product_id' => $extra['product']['product_id'],
			'money_type' => $extra['product']['money_type'],
			'action' => '',
			'transaction_date' => max(0, XenForo_Application::$time),
			'ip' => sprintf('%u', $ipAddress),
			'info' => serialize($extra)
		));

		return $this->_getDb()->lastInsertId();
	}

	public function processSubcribe($userId, $productPurchaseId, $recurring, $extraData)
	{
		$this->_getDb()->query('
			UPDATE xf_store_product_purchase_active SET
				recurring = ? , extra = ?
			WHERE product_purchase_id = ? AND user_id = ?
		',array($recurring,$extraData,$productPurchaseId,$userId));
	}

	/**
	 * Upgrades the user with the specified product.
	 *
	 * @param integer $userId
	 * @param array $product Info about product to apply
	 *
	 * @return integer|false Product purchase record ID
	 */
	public function productUsing($userId,array $product)
	{
		$db = $this->_getDb();
		if(empty($product['purchase'])){
			$active = $this->getActiveProductPurchase($userId, $product['product_id']);
		}else{
			$active = $product['purchase'];
		}
		if ($active)
		{
			$activeExtra = unserialize($active['extra']);
			if($activeExtra['length_unit']=='time'){
				$db->update('xf_store_product_purchase_active',
						array(
							'remained' => ($active['remained'] - 1)
						),
						'user_id = ' . $db->quote($userId) . ' AND product_purchase_id = ' . $db->quote($active['product_purchase_id'])
					);
				if($active['remained'] -1 ==0){
					if($active['recurring'] && $this->checkUserMoney($userId,$product)){
						$this->processPurchase($userId, $product, $active['recurring'], $active['gifted_user_id']);
					}else{
						$this->processExpiredProductPurchases(array($active),true);
					}
				}
			}
			return $active['product_purchase_id'];
		}
		else
		{
			return false;
		}
	}

	public function checkUserMoney($userId,array $product, $user = array())
	{
		if(!empty($product['money_type'])){
			if(!$user){
				$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($userId);
			}
			switch($product['money_type']){
				case 'brivium_credit_premium':
					return $this->_checkUserMoneyCreditPremium($userId,$product, $user);
					break;
				case 'brivium_credit_free':
					return $this->_checkUserMoneyCreditFree($userId,$product, $user);
					break;
				case 'trophy_points':
					return $this->_checkUserMoneyTrophy($userId,$product, $user);
					break;
			}
		}
		return false;
	}

	protected function _checkUserMoneyCreditPremium($userId,array $product, $user = array())
	{
		$currency = (XenForo_Application::isRegistered('brcCurrencies')
			? XenForo_Application::get('brcCurrencies')->$product['currency_id']
			: array());
		if(!$currency)return false;

		if(!empty($user[$currency['column']]) && ($user[$currency['column']] - $product['cost_amount']) >= 0){
			return $user[$currency['column']] - $product['cost_amount'];
		}
		return false;
	}

	protected function _checkUserMoneyCreditFree($userId,array $product, $user = array())
	{
		if(!empty($user['credits']) && ($user['credits'] - $product['cost_amount']) >= 0){
			return $user['credits'] - $product['cost_amount'];
		}
		return false;
	}

	protected function _checkUserMoneyTrophy($userId,array $product, $user = array())
	{
		if(!empty($user['trophy_points']) && ($user['trophy_points'] - $product['cost_amount']) >= 0){
			return $user['trophy_points'] - $product['cost_amount'];
		}
		return false;
	}



	public function updateProductPurchaseCurrent($productTypeId,$purchaseId, $userId)
	{
		$db = $this->_getDb();
		$db->query('
			UPDATE xf_store_product_purchase_active SET
				current = 0
			WHERE product_type_id = ? AND product_purchase_id <> ? AND user_id = ?
		',array($productTypeId,$purchaseId, $userId));
		$db->query('
			UPDATE xf_store_product_purchase_active SET
				current = 1
			WHERE product_type_id = ? AND product_purchase_id = ? AND user_id = ?
		',array($productTypeId,$purchaseId, $userId));
		$this->rebuildProductPurchaseCache($productTypeId);
		return;
	}

	public function updateProductPurchaseConfiguration($purchase, $config)
	{
		$this->rebuildProductPurchaseCache();
		return;
	}


	/**
	 * Removes the specified product change set.
	 *
	 * @param array $userId
	 *
	 * @return array [product purchase id] => info
	 */
	protected function _removeProductChange($purchase){
		return;
	}

	/**
	 * Get all purchase that have expired but are still listed as active.
	 *
	 * @return array [product purchase id] => info
	 */
	public function getExpiredProductPurchases()
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_store_product_purchase_active
			WHERE end_date < ?
				AND end_date > 0
		', 'product_purchase_id', XenForo_Application::$time);
	}

	public function autoRenewPurchased(array $purchaseRecord)
	{

		if ($purchaseRecord && !empty($purchaseRecord['user_id']) && !empty($purchaseRecord['product_id']))
		{
			$userId = $purchaseRecord['user_id'];
			$product = XenForo_Application::get('brsProducts')->$purchaseRecord['product_id'];
			if (!$product)
			{
				return false;
			}

			$activeExtra = unserialize($purchaseRecord['extra']);

			if(!empty($activeExtra['length_unit']) && $activeExtra['length_unit']!='time'){
				if($purchaseRecord['recurring'] && $this->checkUserMoney($userId,$product)){
					$this->processPurchase($userId, $product, $purchaseRecord['recurring'], $purchaseRecord['gifted_user_id']);
				}else{
					$this->processExpiredProductPurchases(array($purchaseRecord),true);
				}
			}
			return $purchaseRecord['product_purchase_id'];
		}
		return false;
	}

	/**
	 * Processes the specified product purchase records.
	 *
	 * @param array $purchases List of product purchase records to process
	 */
	public function processExpiredProductPurchases(array $purchases, $isRemove = false)
	{
		if (!$purchases)
		{
			return;
		}

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$purchaseRecordIds = array();

		foreach ($purchases AS $purchase)
		{
			if($isRemove || !$purchase['recurring'] || !$this->autoRenewPurchased($purchase)){
				$this->_removeProductChange($purchase);
				$purchaseRecordIds[] = $purchase['product_purchase_id'];
			}
		}
		if($purchaseRecordIds){
			$db->query('
				INSERT IGNORE INTO xf_store_product_purchase_expired
					  (product_purchase_id, user_id, gifted_user_id, product_id, product_type_id, money_type, recurring, remained, start_date, end_date	, last_purchase, current)
				SELECT product_purchase_id, user_id, gifted_user_id, product_id, product_type_id, money_type, recurring, remained, start_date, ? 		, last_purchase, current
				FROM xf_store_product_purchase_active
				WHERE product_purchase_id IN (' . $db->quote($purchaseRecordIds) . ')
			', XenForo_Application::$time);
			$db->delete('xf_store_product_purchase_active', 'product_purchase_id IN (' . $db->quote($purchaseRecordIds) . ')');
			$this->rebuildProductPurchaseCache();
		}
		XenForo_Db::commit($db);
	}

	public function rebuildProductPurchaseCache($productTypeId = '')
	{
		$cacheData = array();
		$productTypeIds = array();
		$this->_setUpPurchaseCache($cacheData,$productTypeIds);

		if($productTypeIds && (!$productTypeId || in_array($productTypeId, $productTypeIds)))
		{
			if($productTypeId)$productTypeIds = array($productTypeId);
			$conditions = array(
				'active'	=> true,
				'product_type_id'	=> $productTypeIds,
			);
			$productActives = $this->getProductPurchaseRecords($conditions);

			foreach ($productActives AS $purchase)
			{
				$this->_processPurchaseCache($purchase, $cacheData);
			}
			if($cacheData){
				$this->_getDataRegistryModel()->set('brsCacheData', $cacheData);
				$this->_finishPurchaseCache($cacheData,$productTypeIds);
			}
		}
		return $cacheData;
	}

	protected function _setUpPurchaseCache(array &$cacheData,array &$productTypeIds)
	{
		return;
	}

	protected function _processPurchaseCache(array &$purchase, array &$cacheData)
	{
		return;
	}

	protected function _finishPurchaseCache(array &$cacheData,array &$productTypeIds)
	{
		return;
	}

	protected function _getStylesProductPurchasedConfiguration($configuration)
	{
		$styles = '';
		$style = 'font-weight:';
		if(!empty($configuration['bold'])){
			$style .= 'bold';
		}
		$styles .= $style!='font-weight:'?($style.';'):'';

		$style = 'text-decoration:';
		if(!empty($configuration['underline'])){
			$style .= ' underline';
		}
		if(!empty($configuration['overline'])){
			$style .= ' overline';
		}
		if(!empty($configuration['line_through'])){
			$style .= ' line-through';
		}
		$styles .= $style!='text-decoration:'?($style.';'):'';

		$style = 'font-style:';
		if(!empty($configuration['italic'])){
			$style .= ' italic';
		}
		$styles .= $style!='font-style:'?($style.';'):'';

		$style = 'font-variant:';
		if(!empty($configuration['small-caps'])){
			$style .= ' small_caps';
		}
		$styles .= $style!='font-variant:'?($style.';'):'';

		$style = 'text-transform:';
		if(!empty($configuration['uppercase'])){
			$style .= ' uppercase';
		}
		$styles .= $style!='text-transform:'?($style.';'):'';

		$style = 'color:';
		if(!empty($configuration['colour'])){
			$style .= $configuration['colour'];
		}
		$styles .= $style!='color:'?($style.';'):'';

		$style = 'text-shadow:';
		if(!empty($configuration['shadow']) && !empty($configuration['shadow_colour'])){
			if(!empty($configuration['glow'])){
				$style .= '0px 0px 2px ' . $configuration['shadow_colour'] .',';
				$style .= '0px 0px 2px ' . $configuration['shadow_colour'] .',';
				$style .= '0px 0px 2px ' . $configuration['shadow_colour'] .'';
			}else{
				$style .= '2px 2px 4px ' . $configuration['shadow_colour'] .'';
			}
		}
		$styles .= $style!='text-shadow:'?($style.';'):'';

		return $styles;
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

?>