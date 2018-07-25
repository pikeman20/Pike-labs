<?php

class Brivium_Store_Route_Prefix_Store implements XenForo_Route_Interface
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('Brivium_Store_ControllerPublic_Store', $routePath, 'BR_store');
	}
}