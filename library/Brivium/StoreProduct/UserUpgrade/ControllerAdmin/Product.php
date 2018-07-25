<?php

class Brivium_StoreProduct_UserUpgrade_ControllerAdmin_Product extends XFCP_Brivium_StoreProduct_UserUpgrade_ControllerAdmin_Product
{
	
	protected function _getProductAddEditResponse(array $viewParams, $editTemplate, $productTypeId)
	{
		if($productTypeId == 'UserUpgrade'){
			if(!$viewParams['noCreditPremium'])
			{
				$viewParams['currencies']  = $this->_getCurrencies('BRS_UserUpgrade');

				if(!$viewParams['currencies'])
				{
					$viewParams['noCreditPremium'] = true;
					$viewParams['needCreditEvent'] = true;
				}

				$userUpgradeModel = $this->_getUserUpgradeModel();
				if (!isset($viewParams['product']['extra_group_ids']))
				{
					$viewParams['product']['extra_group_ids'] = array();
				}
				$viewParams['userGroupOptions'] = $this->getModelFromCache('XenForo_Model_UserGroup')->getUserGroupOptions($viewParams['product']['extra_group_ids']);
			}
			return $this->responseView('Brivium_Store_ViewAdmin_Product_Edit', 'BRS_product_edit_user_upgrade',$viewParams);
		}
		return parent::_getProductAddEditResponse($viewParams, $editTemplate, $productTypeId);
	}
	protected function _processProductWriter(Brivium_Store_DataWriter_Product $writer, $writerData, $productTypeId)
	{
		if($productTypeId == 'UserUpgrade')
		{
			$userUpgradeId = $this->_input->filterSingle('extra_group_ids', array(XenForo_Input::UINT, 'array' => true));

			$writerData['extra_group_ids'] = $userUpgradeId;
		}
		return parent::_processProductWriter($writer, $writerData, $productTypeId);
	}

	protected function _getUserUpgradeModel()
	{
		return $this->getModelFromCache('XenForo_Model_UserUpgrade');
	}
}