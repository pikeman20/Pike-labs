<?php
class Brivium_StoreProduct_UserUpgrade_Installer extends Brivium_BriviumHelper_Installer
{
	protected $_installerType = 2;
	
	public static function install($existingAddOn, $addOnData)
	{
		self::$_addOnInstaller = __CLASS__;
		if (self::$_addOnInstaller && class_exists(self::$_addOnInstaller))
		{
			$installer = self::create(self::$_addOnInstaller);
			$installer->installAddOn($existingAddOn, $addOnData);
		}
		return true;
	}
	
	public static function uninstall($addOnData)
	{
		self::$_addOnInstaller = __CLASS__;
		if (self::$_addOnInstaller && class_exists(self::$_addOnInstaller))
		{
			$installer = self::create(self::$_addOnInstaller);
			$installer->uninstallAddOn($addOnData);
		}
	}

	protected function _postInstall()
	{
		$this->rebuildCache();
	}

	public function rebuildCache()
	{
		XenForo_Model::create('Brivium_Store_Model_Product')->rebuildProductCache();
		XenForo_Model::create('Brivium_Store_Model_ProductType')->rebuildProductTypeCache();
	}
	
	protected function _getPrerequisites()
	{
		return array(
			'Brivium_Store'	=>	array(
				'title'	=>	'Brivium - Store',
				'version_id'	=>	0,
			),
		);
	}
	public function getAlters()
	{
		$alters = array();
		$alters["xf_store_product"] = array(
			'extra_group_ids' => "varbinary(255) NOT NULL DEFAULT ''",
		);
		return $alters;
	}
	
	public function getData()
	{
		$data = array();
		$data['xf_store_product_type'] = "
			INSERT IGNORE INTO `xf_store_product_type` 
				(`product_type_id`, `title`, `purchase_type`, `active`) 
			VALUES
				('UserUpgrade', 'User Upgrade', 'only_one', 1);
		";
		return $data;
	}
	public function getQueryFinal()
	{
		$query = array();
		$query[] = "
			DELETE FROM `xf_brivium_listener_class` WHERE `addon_id` = 'BRS_UserUpgrade';
		";
		if($this->_triggerType != "uninstall"){
			$query[] = "
				REPLACE INTO `xf_brivium_addon` 
					(`addon_id`, `title`, `version_id`, `copyright_removal`, `start_date`, `end_date`) 
				VALUES
					('BRS_UserUpgrade', 'Brivium - Store Product User Upgrade', '1000000', 0, 0, 0);
			";
			$query[] = "
				REPLACE INTO `xf_brivium_listener_class` 
					(`class`, `class_extend`, `event_id`, `addon_id`) 
				VALUES
					('Brivium_Store_ControllerAdmin_Product', 'Brivium_StoreProduct_UserUpgrade_ControllerAdmin_Product', 'load_class_controller', 'BRS_UserUpgrade'),
					('Brivium_Store_DataWriter_Product', 'Brivium_StoreProduct_UserUpgrade_DataWriter_Product', 'load_class_datawriter', 'BRS_UserUpgrade'),
					('Brivium_Store_Model_ProductPurchase', 'Brivium_StoreProduct_UserUpgrade_Model_ProductPurchase', 'load_class_model', 'BRS_UserUpgrade');
			";
		}else{
			$query[] = "
				DELETE FROM `xf_brivium_addon` WHERE `addon_id` = 'BRS_UserUpgrade';
			";
		}
		return $query;
	}
}

?>