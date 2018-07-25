<?php

/**
 * Store ProductTypes accessor class.
 *
 * @package Brivium_Store_ProductTypes
 */
class Brivium_Store_ProductTypes
{
	/**
	 * Collection of productTypes.
	 *
	 * @var array
	 */
	protected $_productTypes = array();

	/**
	 * Constructor. Sets up the accessor using the provided productTypes.
	 *
	 * @param array $productTypes Collection of productTypes. Keys represent productType names.
	 */
	public function __construct(array $productTypes)
	{
		$this->setProductTypes($productTypes);
	}

	/**
	 * Gets an productType. If the productType exists and is an array, then...
	 * If the productType is not an array, then the value of the productType is returned (provided no sub-productType is specified).
	 * Otherwise, null is returned.
	 *
	 * @param string $productTypeName Name of the productType
	 *
	 * @return null|mixed Null if the productType doesn't exist (see above) or the productType's value.
	 */
	public function get($productTypeName)
	{
		if (!isset($this->_productTypes[$productTypeName]))
		{
			return null;
		}

		$productType = $this->_productTypes[$productTypeName];

		if (is_array($productType))
		{
			return $productType;
		}
		else
		{
			return null;
		}
	}
	public function getAll()
	{
		if (!empty($this->_productTypes))
		{
			return $this->_productTypes;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Gets all productTypes in their raw form.
	 *
	 * @return array
	 */
	public function getProductTypes()
	{
		return $this->_productTypes;
	}

	/**
	 * Sets the collection of productTypes manually.
	 *
	 * @param array $productTypes
	 */
	public function setProductTypes(array $productTypes)
	{
		$this->_productTypes = $productTypes;
	}
	
	/**
	 * Magic getter for first-order productTypes.
	 * @param string $productType
	 *
	 * @return null|mixed
	 */
	public function __get($productType)
	{
		return $this->get($productType);
	}

	/**
	 * Returns true if the named productType exists. 
	 *
	 * @param string $productType
	 *
	 * @return boolean
	 */
	public function __isset($productType)
	{
		return ($this->get($productType) !== null);
	}

}