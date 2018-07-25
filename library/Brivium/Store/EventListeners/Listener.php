<?php

class Brivium_Store_EventListeners_Listener extends Brivium_BriviumHelper_EventListeners
{
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data) {

		$cacheData = XenForo_Model::create('XenForo_Model_DataRegistry')->getMulti(array('brsProducts','brsProductTypes','brsCacheData'));
		$products = (!empty($cacheData['brsProducts']) && is_array($cacheData['brsProducts']) ? $cacheData['brsProducts'] : XenForo_Model::create('Brivium_Store_Model_Product')->rebuildProductCache());
		$productTypes = (!empty($cacheData['brsProductTypes']) && is_array($cacheData['brsProductTypes']) ? $cacheData['brsProductTypes'] : XenForo_Model::create('Brivium_Store_Model_ProductType')->rebuildProductTypeCache());

		if (!is_array($cacheData['brsCacheData']))
		{
			$cacheData['brsCacheData'] = array();
		}
		XenForo_Application::set('brsCacheData', $cacheData['brsCacheData']);

		$productsObj = new Brivium_Store_Products($products);
		XenForo_Application::set('brsProducts',$productsObj);
		$productTypesObj = new Brivium_Store_ProductTypes($productTypes);
		XenForo_Application::set('brsProductTypes',$productTypesObj);

		XenForo_Template_Helper_Core::$helperCallbacks['productimagehtml'] = array('Brivium_Store_EventListeners_Helpers', 'helperProductImageHtml');
		XenForo_Template_Helper_Core::$helperCallbacks['brs_producticonurl'] = array('Brivium_Store_EventListeners_Helpers', 'helperProductImageUrl');
		XenForo_Template_Helper_Core::$helperCallbacks['brs_storecostformat'] = array('Brivium_Store_EventListeners_Helpers', 'helperStoreCostFormat');
	}

	public static function navigationTabs(&$extraTabs, $selectedTabId)
	{
		if (XenForo_Visitor::getInstance()->hasPermission('BR_storePermission', 'view'))
		{
			$extraTabs['BR_store'] = array(
				'title' => new XenForo_Phrase('BRS_store'),
				'href' => XenForo_Link::buildPublicLink('full:store'),
				'position' => 'middle',
				'linksTemplate' => 'BRS_stores_tab_links'
			);
		}

	}

}