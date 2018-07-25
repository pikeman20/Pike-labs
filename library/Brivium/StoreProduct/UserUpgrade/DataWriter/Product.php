<?php

/**
* Data writer for threads.
*
* @package XenForo_Discussion
*/
class Brivium_StoreProduct_UserUpgrade_DataWriter_Product extends XFCP_Brivium_StoreProduct_UserUpgrade_DataWriter_Product
{
	protected function _getFields()
	{
		$fields = parent::_getFields();	
		$fields['xf_store_product']['extra_group_ids'] = array('type' => self::TYPE_UNKNOWN,   'default' => '', 
			'verification' => array('XenForo_DataWriter_Helper_User', 'verifyExtraUserGroupIds'));
		return $fields;
	}
}