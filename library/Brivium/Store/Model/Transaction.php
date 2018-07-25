<?php

class Brivium_Store_Model_Transaction extends XenForo_Model
{
	const FETCH_PRODUCT    			= 0x01;
	const FETCH_USER    			= 0x02;
	const FETCH_FULL    			= 0x07;
	
	public function getAllTransactions(array $fetchOptions = array())
	{
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		$joinOptions = $this->prepareTransactionFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults('
				SELECT transaction.*
					' . $joinOptions['selectFields'] . '
				FROM xf_store_transaction AS transaction
				' . $joinOptions['joinTables'] . '
				' . $whereClause . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'transaction_id');
	}
	/**
	*	get Category by its id
	* 	@param integer $transactionId
	* 	@param array $fetchOptions Collection of options related to fetching
	*	@return array|false Category info
	*/
	public function getTransactionById($transactionId,$fetchOptions = array()){
		$joinOptions = $this->prepareTransactionFetchOptions($fetchOptions);
		return $this->_getDb()->fetchRow('
			SELECT transaction.*
			' .$joinOptions['selectFields']. '
			FROM xf_store_transaction AS transaction
			' .$joinOptions['joinTables']. '
			WHERE transaction.transaction_id = ?
		',$transactionId);
	}
	/**
	*	Gets multi transactions.
	*
	*	@param array $transactionIds
	*	@param array $fetchOptions Collection of options related to fetching
	*	
	*	@return array Format: [transaction id] => info
	*/
	public function getTransactionsByIds(array $transactionIds)
	{
		if (!$transactionIds)
		{
			return array();
		}
		return $this->fetchAllKeyed('
			SELECT transaction.*
			FROM xf_store_transaction AS transaction
			WHERE transaction.transaction_id IN (' . $this->_getDb()->quote($transactionIds) . ')
		', 'transaction_id');
	}
	
	/**
	 * Prepares a collection of transaction fetching related conditions into an SQL clause
	 *
	 * @param array $conditions List of conditions
	 * @param array $fetchOptions Modifiable set of fetch options (may have joins pushed on to it)
	 *
	 * @return string SQL clause (at least 1=1)
	 */
	public function prepareTransactionConditions(array $conditions, array &$fetchOptions)
	{
		$sqlConditions = array();
		$db = $this->_getDb();

		if (!empty($conditions['transaction_id']))
		{
			if (is_array($conditions['transaction_id']))
			{
				$sqlConditions[] = 'transaction.transaction_id IN (' . $db->quote($conditions['transaction_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'transaction.transaction_id = ' . $db->quote($conditions['transaction_id']);
			}
		}
		if (!empty($conditions['product_id']))
		{
			if (is_array($conditions['product_id']))
			{
				$sqlConditions[] = 'transaction.product_id IN (' . $db->quote($conditions['product_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'transaction.product_id = ' . $db->quote($conditions['product_id']);
			}
		}
		if (!empty($conditions['user_id']))
		{
			if (is_array($conditions['user_id']))
			{
				$sqlConditions[] = 'transaction.user_id IN (' . $db->quote($conditions['user_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'transaction.user_id = ' . $db->quote($conditions['user_id']);
			}
		}
		if (!empty($conditions['ip']))
		{
			if (is_array($conditions['ip']))
			{
				$sqlConditions[] = 'transaction.ip IN (' . $db->quote($conditions['ip']) . ')';
			}
			else
			{
				$sqlConditions[] = 'transaction.ip = ' . $db->quote($conditions['ip']);
			}
		}
		
		if (!empty($conditions['transaction_date']) && is_array($conditions['transaction_date']))
		{
			list($operator, $cutOff) = $conditions['transaction_date'];

			$this->assertValidCutOffOperator($operator);
			$sqlConditions[] = "transaction.transaction_date $operator " . $db->quote($cutOff);
		}
		if (!empty($conditions['start']))
		{
			$sqlConditions[] = 'transaction.transaction_date >= ' . $db->quote($conditions['start']);
		}

		if (!empty($conditions['end']))
		{
			$sqlConditions[] = 'transaction.transaction_date <= ' . $db->quote($conditions['end']);
		}

		return $this->getConditionsForClause($sqlConditions);
	}
	public function prepareTransactionFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$orderBy = '';
		$orderBySecondary = '';
		if (!empty($fetchOptions['order']))
		{
			switch ($fetchOptions['order'])
			{
				case 'user_id':
					$orderBy = 'transaction.' . $fetchOptions['order'];
					$orderBySecondary = ', transaction.transaction_date DESC';
					break;
				default:
					$orderBy = 'transaction.transaction_date';	
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
			if ($fetchOptions['join'] & self::FETCH_USER)
			{
				$selectFields .= ', user.*';
				$joinTables .= '
					LEFT JOIN xf_user AS user ON
						(user.user_id = transaction.user_id)';
			}
		}
		if (!empty($fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::FETCH_PRODUCT)
			{
				$selectFields .= ', product.title';
				$joinTables .= '
					LEFT JOIN xf_store_product AS product ON
						(product.product_id = transaction.product_id)';
			}
		}
		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables,
			'orderClause'  => ($orderBy ? "ORDER BY $orderBy" : '')
		);
	}
	
	/**
	 * Gets transactions that match the given conditions.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * @return array Format: [transaction id] => info
	 */
	public function getTransactions(array $conditions, array $fetchOptions = array())
	{
		$whereConditions = $this->prepareTransactionConditions($conditions, $fetchOptions);

		$sqlClauses = $this->prepareTransactionFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		return $this->fetchAllKeyed($this->limitQueryResults(			'
				SELECT transaction.*
					' . $sqlClauses['selectFields'] . '
				FROM xf_store_transaction AS transaction
				' . $sqlClauses['joinTables'] . '
				WHERE ' . $whereConditions . '
				' . $sqlClauses['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'transaction_id');
	}
	
	
	public function deleteTransactionById($transactionId)
	{
		$this->_getDb()->delete('xf_store_transaction', 'transaction_id = ' . $this->_getDb()->quote($transactionId));
	}
	
	/**
	 * Gets the count of transactions with the specified criteria.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 *
	 * @return integer
	 */
	public function countTransactions(array $conditions)
	{
		$fetchOptions = array();
		$whereConditions = $this->prepareTransactionConditions($conditions, $fetchOptions);
		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xf_store_transaction AS transaction
			WHERE ' . $whereConditions . '
		');
	}
	public function prepareTransactions(array $transactions)
	{
		if(!$transactions) return array();
		foreach($transactions AS &$transaction){
			$transaction = $this->prepareTransaction($transaction);
		}
		return $transactions;
	}
	public function prepareTransaction(array $transaction)
	{
		if(!$transaction) return array();
		if (is_string($transaction['ip']) && strpos($transaction['ip'], '.'))
		{
			$transaction['ip'] = ip2long($transaction['ip']);
		}
		return $transaction;
	}
}

?>