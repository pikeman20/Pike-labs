<?php

/**
 * Store products accessor class.
 *
 * @package Brivium_Store_Products
 */
class Brivium_Store_Products
{
	/**
	 * Collection of products.
	 *
	 * @var array
	 */
	protected $_products = array();

	/**
	 * Constructor. Sets up the accessor using the provided products.
	 *
	 * @param array $products Collection of products. Keys represent product names.
	 */
	public function __construct(array $products)
	{
		$this->setProducts($products);
	}

	/**
	 * Gets an product. If the product exists and is an array, then...
	 * If the product is not an array, then the value of the product is returned (provided no sub-product is specified).
	 * Otherwise, null is returned.
	 *
	 * @param string $productName Name of the product
	 *
	 * @return null|mixed Null if the product doesn't exist (see above) or the product's value.
	 */
	public function get($productName)
	{
		if (!isset($this->_products[$productName]))
		{
			return null;
		}

		$product = $this->_products[$productName];

		if (is_array($product))
		{
			return $product;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Gets all products in their raw form.
	 *
	 * @return array
	 */
	public function getProducts()
	{
		return $this->_products;
	}

	/**
	 * Sets the collection of products manually.
	 *
	 * @param array $products
	 */
	public function setProducts(array $products)
	{
		$this->_products = $products;
	}

	/**
	 * Magic getter for first-order products.
	 * @param string $product
	 *
	 * @return null|mixed
	 */
	public function __get($product)
	{
		return $this->get($product);
	}

	/**
	 * Returns true if the named product exists. 
	 *
	 * @param string $product
	 *
	 * @return boolean
	 */
	public function __isset($product)
	{
		return ($this->get($product) !== null);
	}

	/**
	 * Sets an product or a particular sub-product (first level array key).
	 *
	 * @param string $product
	 * @param mixed $subProduct If $value is null, then this is treated as the value; otherwise, a specific array key to change
	 * @param mixed|null $value If null, ignored
	 */
	public function set($product, $subProduct, $value = null)
	{
		if ($value === null)
		{
			$value = $subProduct;
			$subProduct = false;
		}

		if ($subProduct === false)
		{
			$this->_products[$product] = $value;
		}
		else if (isset($this->_products[$product]) && is_array($this->_products[$product]))
		{
			$this->_products[$product][$subProduct] = $value;
		}
		else
		{
			throw new XenForo_Exception('Tried to write sub-product to invalid/non-array product.');
		}
	}

	/**
	 * Magic set method. Only sets whole products.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}
}