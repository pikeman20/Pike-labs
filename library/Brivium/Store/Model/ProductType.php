<?php

/**
 * Model for Products.
 *
 * @package Brivium_Store
 */
class Brivium_Store_Model_ProductType extends XenForo_Model
{
	
	/**  PRODUCT TYPE   **/
	
	public function getAllProductTypes()
	{
		if (($productTypes = $this->_getLocalCacheData('allProductTypes')) === false)
		{
			$productTypes = $this->fetchAllKeyed('
				SELECT *
				FROM xf_store_product_type
			', 'product_type_id');

			$this->setLocalCacheData('allProductTypes', $productTypes);
		}
		return $productTypes;
	}
	/**
	*	get product type by its id
	* 	@param integer $productTypeId
	*	@return array|false product_type info
	*/
	public function getProductTypeById($productTypeId){
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_store_product_type
			WHERE product_type_id = ?
		',$productTypeId);
	}
		/**
	 * Gets all productTypes in the format expected by the productType cache.
	 *
	 * @return array Format: [productType id] => info, with phrase cache as array
	 */
	public function getAllProductTypesForCache()
	{
		$this->resetLocalCacheData('allProductTypes');

		$productTypes = $this->getAllProductTypes();
		return $productTypes;
	}

	/**
	 * Rebuilds the full ProductType cache.
	 *
	 * @return array Format: [productType id] => info, with phrase cache as array
	 */
	public function rebuildProductTypeCache()
	{
		$this->resetLocalCacheData('allProductTypes');

		$productTypes = $this->getAllProductTypesForCache();

		$this->_getDataRegistryModel()->set('brsProductTypes', $productTypes);

		return $productTypes;
	}
	
	/**
	 * Rebuilds all productType caches.
	 */
	public function rebuildProductTypeCaches()
	{
		$this->rebuildProductTypeCache();
	}
	
}
?>