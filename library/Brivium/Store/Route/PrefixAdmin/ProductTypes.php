<?php

/**
 * Route prefix handler for products in the admin control panel.
 *
 * @package Brivium_Store
 */
class Brivium_Store_Route_PrefixAdmin_ProductTypes implements XenForo_Route_Interface
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$components = explode('/', $routePath);
		$action = strtolower(array_shift($components));
		if($action!='save'){
			$action = $router->resolveActionWithStringParam($routePath, $request, 'product_type_id');
		}
		return $router->getRouteMatch('Brivium_Store_ControllerAdmin_ProductType', $action, 'BR_store');
	}

	/**
	 * Method to build a link to the specified page/action with the provided
	 * data and params.
	 *
	 * @see XenForo_Route_BuilderInterface
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'product_type_id', 'title');
	}
	
}