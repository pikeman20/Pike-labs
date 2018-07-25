<?php
class Brivium_Store_Installer extends Brivium_BriviumHelper_Installer
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
		if(!empty($this->_existingAddOn['version_id']) && $this->_existingAddOn['version_id'] < 1020000){
			$this->applyPermissionDefaults($this->_existingAddOn['version_id']);
		}
	}
	public function applyPermissionDefaults($previousVersion)
	{
		if (!$previousVersion || $previousVersion < 1020000)
		{
			$this->applyGlobalPermission('BR_storePermission', 'view', 'general', 'viewNode', false);
			$this->applyGlobalPermission('BR_storePermission', 'gift', 'general', 'viewNode', false);
			$this->applyGlobalPermission('BR_storePermission', 'purchase', 'general', 'viewNode', false);
		}
	}
	public function applyGlobalPermission($applyGroupId, $applyPermissionId, $dependGroupId = null, $dependPermissionId = null)
	{
		$db = XenForo_Application::getDb();

		XenForo_Db::beginTransaction($db);

		if ($dependGroupId && $dependPermissionId)
		{
			$db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, ?, ?, 'allow', 0
				FROM xf_permission_entry
				WHERE permission_group_id = ?
					AND permission_id = ?
					AND permission_value = 'allow'
			", array($applyGroupId, $applyPermissionId, $dependGroupId, $dependPermissionId));
		}
		else
		{
			$db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT DISTINCT user_group_id, user_id, ?, ?, 'allow', 0
				FROM xf_permission_entry
			", array($applyGroupId, $applyPermissionId));
		}
		XenForo_Db::commit($db);
	}

	public function getTables()
	{
		$tables = array();
		$tables["xf_store_category"] = "
			CREATE TABLE IF NOT EXISTS `xf_store_category` (
			  `product_category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `category_title` varchar(100) NOT NULL,
			  `category_description` text NOT NULL,
			  `parent_category_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `product_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `display_order` int(10) unsigned NOT NULL DEFAULT '0',
			  `lft` int(10) unsigned NOT NULL DEFAULT '0',
			  `rgt` int(10) unsigned NOT NULL DEFAULT '0',
			  `depth` int(10) unsigned NOT NULL DEFAULT '0',
			  `display_in_list` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'If 0, hidden from category list. Still counts for lft/rgt.',
			  `last_post` int(10) unsigned NOT NULL DEFAULT '0',
			  `last_product_title` varchar(100) NOT NULL DEFAULT '',
			  `last_product_id` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`product_category_id`),
			  KEY `parent_category_id` (`parent_category_id`),
			  KEY `display_order` (`display_order`),
			  KEY `display_in_list` (`display_in_list`,`lft`),
			  KEY `lft` (`lft`),
			  KEY `product_count` (`product_count`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		";
		$tables["xf_store_product"] = "
			CREATE TABLE IF NOT EXISTS `xf_store_product` (
			  `product_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `product_category_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `title` varchar(150) NOT NULL,
			  `description` text NOT NULL,
			  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `username` varchar(50) NOT NULL DEFAULT '',
			  `product_type_id` varchar(50) NOT NULL DEFAULT '',
			  `cost_amount` decimal(10,2) unsigned NOT NULL,
			  `money_type` varchar(100) NOT NULL DEFAULT '',
			  `currency_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `length_amount` int(10) unsigned NOT NULL DEFAULT '0',
			  `length_unit` enum('day','month','year','time','') NOT NULL DEFAULT '',
			  `recurring` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `permissions` mediumblob,
			  `product_unique` text,
			  `can_purchase` tinyint(3) unsigned NOT NULL DEFAULT '1',
			  `unique` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `product_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `buy_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `quantity` int(10) NOT NULL DEFAULT '-1',
			  `display_order` int(10) unsigned NOT NULL DEFAULT '0',
			  `display_in_list` tinyint(3) unsigned NOT NULL DEFAULT '1',
			  `style_css` mediumblob,
			  `image_type` varchar(100) NOT NULL DEFAULT '',
			  `can_gift` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `has_icon` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `product_state` varchar(25) NOT NULL DEFAULT '',
			  `extra_group_ids` varbinary(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`product_id`),
			  KEY `product_category_id` (`product_category_id`),
			  KEY `user_id` (`user_id`),
			  KEY `product_type_id` (`product_type_id`),
			  KEY `money_type` (`money_type`),
			  KEY `currency_id` (`currency_id`),
			  KEY `can_purchase` (`can_purchase`),
			  KEY `unique` (`unique`),
			  KEY `sticky` (`sticky`),
			  KEY `display_in_list` (`display_in_list`),
			  KEY `image_type` (`image_type`),
			  KEY `product_state` (`product_state`),
			  KEY `money_currency` (`money_type`,`currency_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		";
		$tables["xf_store_product_change"] = "
			CREATE TABLE IF NOT EXISTS `xf_store_product_change` (
			  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `change_data` mediumblob NOT NULL,
			  UNIQUE KEY `user_id_product_id` (`user_id`,`product_id`),
			  KEY `product_id` (`product_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		";
		$tables["xf_store_product_purchase_active"] = "
			CREATE TABLE IF NOT EXISTS `xf_store_product_purchase_active` (
			  `product_purchase_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `gifted_user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `product_type_id` varchar(50) NOT NULL DEFAULT '',
			  `extra` mediumblob NOT NULL,
			  `product_unique` text,
			  `money_type` varchar(100) NOT NULL DEFAULT '',
			  `recurring` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `remained` int(10) unsigned NOT NULL DEFAULT '0',
			  `start_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `end_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `last_purchase` int(10) unsigned NOT NULL DEFAULT '0',
			  `current` tinyint(3) unsigned NOT NULL DEFAULT '1',
			  PRIMARY KEY (`product_purchase_id`),
			  KEY `user_id` (`user_id`),
			  KEY `gifted_user_id` (`gifted_user_id`),
			  KEY `product_id` (`product_id`),
			  KEY `user_product` (`user_id`,`product_id`),
			  KEY `end_date` (`end_date`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		";
		$tables["xf_store_product_purchase_expired"] = "
			CREATE TABLE IF NOT EXISTS `xf_store_product_purchase_expired` (
			  `product_purchase_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `gifted_user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `product_type_id` varchar(50) NOT NULL DEFAULT '',
			  `style_css` text NOT NULL,
			  `recurring` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `money_type` varchar(100) NOT NULL DEFAULT '',
			  `remained` int(10) unsigned NOT NULL DEFAULT '0',
			  `start_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `end_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `last_purchase` int(10) unsigned NOT NULL DEFAULT '0',
			  `current` tinyint(3) unsigned NOT NULL DEFAULT '1',
			  KEY `user_id` (`user_id`),
			  KEY `gifted_user_id` (`gifted_user_id`),
			  KEY `product_id` (`product_id`),
			  KEY `user_product` (`user_id`,`product_id`),
			  KEY `end_date` (`end_date`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		";
		$tables["xf_store_product_type"] = "
			CREATE TABLE IF NOT EXISTS `xf_store_product_type` (
			  `product_type_id` varchar(50) NOT NULL,
			  `title` varchar(100) NOT NULL,
			  `purchase_type` varchar(50) NOT NULL DEFAULT '',
			  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
			  PRIMARY KEY (`product_type_id`),
			  KEY `active` (`active`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		";
		$tables["xf_store_transaction"] = "
			CREATE TABLE IF NOT EXISTS `xf_store_transaction` (
			  `transaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `money_type` varchar(100) NOT NULL DEFAULT '',
			  `action` varchar(50) NOT NULL,
			  `transaction_date` int(10) unsigned NOT NULL DEFAULT '0',
			  `ip` int(10) unsigned NOT NULL DEFAULT '0',
			  `info` mediumblob NOT NULL,
			  PRIMARY KEY (`transaction_id`),
			  KEY `transaction_id` (`transaction_id`),
			  KEY `user_id` (`user_id`),
			  KEY `user_product` (`user_id`,`product_id`),
			  KEY `money_type` (`money_type`),
			  KEY `action` (`action`),
			  KEY `transaction_date` (`transaction_date`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		";
		return $tables;
	}

	public function getData()
	{
		$data = array();
		$data['xf_content_type'] = "
			INSERT IGNORE INTO xf_content_type
				(content_type, addon_id, fields)
			VALUES
				('store', 'Brivium_Store', '');
		";

		$data['xf_content_type_field'] = "
			INSERT IGNORE INTO `xf_content_type_field`
				(`content_type`, `field_name`, `field_value`)
			VALUES
				('store', 'alert_handler_class', 'Brivium_Store_AlertHandler_Store');
		";
		return $data;
	}

	public function getAlters()
	{
		$alters = array();
		$alters["xf_store_product_purchase_active"] = array(
			"gifted_user_id"	=> "int(10) unsigned NOT NULL DEFAULT '0'"
		);
		$alters["xf_store_product_purchase_expired"] = array(
			"gifted_user_id"	=> "int(10) unsigned NOT NULL DEFAULT '0'"
		);
		return $alters;
	}

	public function getQueryBeforeAlter()
	{
		$query = array();
		if($this->_triggerType != "uninstall" && $this->_existingVersionId > 0 && $this->_existingVersionId < 1020500){
			$query[] = "
				ALTER TABLE  `xf_store_category`
					ADD KEY `parent_category_id` (`parent_category_id`),
					ADD KEY `display_order` (`display_order`),
					ADD KEY `display_in_list` (`display_in_list`,`lft`),
					ADD KEY `lft` (`lft`),
					ADD KEY `product_count` (`product_count`);
			";
			$query[] = "
				ALTER TABLE  `xf_store_product`
					ADD KEY `product_category_id` (`product_category_id`),
					ADD KEY `user_id` (`user_id`),
					ADD KEY `product_type_id` (`product_type_id`),
					ADD KEY `money_type` (`money_type`),
					ADD KEY `currency_id` (`currency_id`),
					ADD KEY `can_purchase` (`can_purchase`),
					ADD KEY `unique` (`unique`),
					ADD KEY `sticky` (`sticky`),
					ADD KEY `display_in_list` (`display_in_list`),
					ADD KEY `image_type` (`image_type`),
					ADD KEY `product_state` (`product_state`),
					ADD KEY `money_currency` (`money_type`,`currency_id`);
			";
			$query[] = "
				ALTER TABLE  `xf_store_product_purchase_active`
					ADD KEY `user_id` (`user_id`),
					ADD KEY `gifted_user_id` (`gifted_user_id`),
					ADD KEY `product_id` (`product_id`),
					ADD KEY `user_product` (`user_id`, `product_id`),
					ADD KEY `end_date` (`end_date`);
			";
			$query[] = "
				ALTER TABLE  `xf_store_product_purchase_expired`
					ADD KEY `user_id` (`user_id`),
					ADD KEY `gifted_user_id` (`gifted_user_id`),
					ADD KEY `product_id` (`product_id`),
					ADD KEY `user_product` (`user_id`, `product_id`),
					ADD KEY `end_date` (`end_date`);
			";
			$query[] = "
				ALTER TABLE  `xf_store_product_type`
					ADD KEY `active` (`active`);
			";
			$query[] = "
				ALTER TABLE  `xf_store_transaction`
					ADD KEY `user_id` (`user_id`),
					ADD KEY `user_product` (`user_id`,`product_id`),
					ADD KEY `money_type` (`money_type`),
					ADD KEY `action` (`action`),
					ADD KEY `transaction_date` (`transaction_date`);
			";
		}
		$query[] = "
			ALTER TABLE  `xf_store_product`
				CHANGE  `length_amount`  `length_amount` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0';
		";
		return $query;
	}

	public function getQueryFinal()
	{
		$query = array();
		if($this->_triggerType != "uninstall"){
			$query[] = "
				REPLACE INTO `xf_brivium_addon`
					(`addon_id`, `title`, `version_id`, `copyright_removal`, `start_date`, `end_date`)
				VALUES
					('Brivium_Store', 'Brivium - Store', '1020600', 0, 0, 0);
			";
		}else{
			$query[] = "
				DELETE FROM `xf_brivium_addon` WHERE `addon_id` = 'Brivium_Store';
			";
		}
		return $query;
	}
}