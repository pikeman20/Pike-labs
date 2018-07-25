<?php

/**
 * Cron entry for Store.
 */
class Brivium_Store_CronEntry_Store
{
	public static function runProductPurchaseExpiredHandle()
	{
		$productPurchaseModel = XenForo_Model::create('Brivium_Store_Model_ProductPurchase');
		$productPurchaseModel->processExpiredProductPurchases(
			$productPurchaseModel->getExpiredProductPurchases()
		);
	}
}