<?php
class Brivium_Store_EventListeners_Helpers extends XenForo_Template_Helper_Core
{
	public static function helperStoreCostFormat($amount,$productId, $moneyType = '', $currencyId = 0, $product = array(), $isProductFormat = true)
	{
		if(!$moneyType){
			if(!$product){
				$product = XenForo_Application::get('brsProducts')->$productId;
			}
			$moneyType = !empty($product['money_type'])?$product['money_type']:'';
		}
		if(!$amount && $isProductFormat){
			if(!$product){
				$product = XenForo_Application::get('brsProducts')->$productId;
			}
			$amount = !empty($product['cost_amount'])?$product['cost_amount']:0;
		}
		if($moneyType && $amount){
			switch($moneyType){
				case 'brivium_credit_premium':
					if(XenForo_Application::isRegistered('brcCurrencies')){
						return XenForo_Application::get('brcCurrencies')->currencyFormat($amount,false,$currencyId);
					}
					break;
				case 'brivium_credit_free':
					if(XenForo_Application::isRegistered('brcCurrencies') && !XenForo_Application::isRegistered('brcEvents') ){
						return Brivium_Credits_Currency::currencyFormat($amount,false);
					}

					break;
				case 'trophy_points':
					return XenForo_Template_Helper_Core::numberFormat($amount) .' '. new XenForo_Phrase('points');
					break;
			}

		}
		XenForo_Application::get('brcCurrencies')->currencyFormat($product['cost_amount'],false,$product['currency_id']);
		return $amount;

	}
	// -------------------------------------------------
	// ProductImage-related methods

	/**
	 * Returns an <a> tag for use as a product image
	 *
	 * @param array $product
	 * @param boolean If true, use an <img> tag, otherwise use a block <span> with the product as a background image
	 * @param array Extra tag attributes
	 * @param string Additional tag contents (inserted after image element)
	 */
	public static function helperProductImageHtml(array $product, $img, array $attributes = array(), $content = '')
	{
		$forceType = (isset($attributes['forcetype']) ? $attributes['forcetype'] : null);

		$canonical = (isset($attributes['canonical']) && self::attributeTrue($attributes['canonical']));

		$src = call_user_func(self::$helperCallbacks['productimage'], $product, $forceType, $canonical);

		$href = self::getUserHref($product, $attributes);
		unset($attributes['href']);

		if ($img)
		{
			$title = htmlspecialchars($product['title']);
			$dimension = 48;

			$image = "<img src=\"{$src}\" width=\"{$dimension}\" height=\"{$dimension}\" alt=\"{$title}\" />";
		}
		else
		{
			$text = (empty($attributes['text']) ? '' : htmlspecialchars($attributes['text']));

			$image = "<span class=\"img {$size}\" style=\"background-image: url('{$src}')\">{$text}</span>";
		}

		$class = (empty($attributes['class']) ? '' : ' ' . htmlspecialchars($attributes['class']));

		unset($attributes['img'], $attributes['text'], $attributes['class']);

		$attribs = self::getAttributes($attributes);

		if ($content !== '')
		{
			$content = " {$content}";
		}

		return "<a{$href} class=\"product-image Av{$product['user_id']}{$size}{$class}\"{$attribs} data-imageHtml=\"true\">{$image}{$content}</a>";
	}

	/**
	 * Helper to fetch the URL of a product's image.
	 *
	 * @param array $product product info
	 * @param boolean Serve the default no image, even if the product has a custom image
	 * @param boolean Serve the full canonical URL
	 *
	 * @return string Path to image
	 */
	public static function helperProductImageUrl($product, $size = 'o', $forceType = null, $canonical = false)
	{
		if (!is_array($product))
		{
			$product = array();
		}

		if ($forceType)
		{
			switch ($forceType)
			{
				case 'default':
				case 'custom':
					break;

				default:
					$forceType = null;
					break;
			}
		}

		$url = self::getProductImageUrl($product, $size, $forceType);

		if ($canonical)
		{
			$url = XenForo_Link::convertUriToAbsoluteUri($url, true);
		}

		return htmlspecialchars($url);
	}

	/**
	 * Returns the URL to the appropriate image type for the given product
	 *
	 * @param array $product
	 * @param string Force 'default' or 'custom' type
	 *
	 * @return string
	 */
	public static function getProductImageUrl(array $product, $size, $forceType = '')
	{
		if (!empty($product['product_id']) && $forceType != 'default')
		{
			if (!empty($product['image_type']))
			{
				return self::_getCustomProductImageUrl($product, $size);
			}
		}

		return self::_getDefaultProductImageUrl($product, $size);
	}

	/**
	 * Returns the default gender-specific avatar URL
	 *
	 * @param string $gender - male / female / other
	 *
	 * @return string
	 */
	protected static function _getDefaultProductImageUrl($size)
	{
		$imagePath = 'styles/brivium/Store';
		return "{$imagePath}/no_image.png";
	}

	/**
	 * Returns the URL to a product's custom image
	 *
	 * @param array $product
	 *
	 * @return string
	 */
	protected static function _getCustomProductImageUrl(array $product, $size)
	{
		$group = floor($product['product_id'] / 100);
		return XenForo_Application::$externalDataUrl . "/brsimages/products/$size/$group/$product[product_id]$product[image_type]?$product[product_date]";
	}

}