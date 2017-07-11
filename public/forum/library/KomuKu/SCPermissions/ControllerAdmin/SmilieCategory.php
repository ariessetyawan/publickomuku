<?php

class KomuKu_SCPermissions_ControllerAdmin_SmilieCategory extends XFCP_KomuKu_SCPermissions_ControllerAdmin_SmilieCategory
{
	public function actionAdd()
	{
		$response = parent::actionAdd();
		
		if ($response instanceof XenForo_ControllerResponse_View)
		{
			$userGroups = $this->_getUserGroupModel()->getAllUserGroups();
			$allUserGroups = true;
			$selUserGroupIds = array_keys($userGroups);
			
			$response->params['userGroups'] = $userGroups;
			$response->params['allUserGroups'] = $allUserGroups;
			$response->params['selUserGroupIds'] = $selUserGroupIds;
		}
		
		return $response;
	}
	public function actionEdit()
	{
		$response = parent::actionEdit();
		
		if ($response instanceof XenForo_ControllerResponse_View)
		{
			$smilieCategoryId = $this->_input->filterSingle('smilie_category_id', XenForo_Input::UINT);
		    $smilieCategory = $this->_getSmilieCategoryOrError($smilieCategoryId);
			
			$userGroups = $this->_getUserGroupModel()->getAllUserGroups();
			
			$selUserGroupIds = explode(',', $smilieCategory['allowed_user_group_ids']);
			if (in_array(-1, $selUserGroupIds))
			{
				$allUserGroups = true;
				$selUserGroupIds = array_keys($userGroups);
			}
			else
			{
				$allUserGroups = false;
			}
			
			$response->params['userGroups'] = $userGroups;
			$response->params['allUserGroups'] = $allUserGroups;
			$response->params['selUserGroupIds'] = $selUserGroupIds;
		}
		
		return $response;
	}
	public function actionSave()
	{
		$response = parent::actionSave();
		
		if (isset($response->redirectType) && $response->redirectType == XenForo_ControllerResponse_Redirect::SUCCESS && $smilieCategory = XenForo_Application::get(KomuKu_SCPermissions_DataWriter_SmilieCategory::SCID))
		{
			$input = $this->_input->filter(array(
			    'usable_user_group_type' => XenForo_Input::STRING,
			    'user_group_ids' => array(XenForo_Input::UINT, 'array' => true),
			));
			
			if ($input['usable_user_group_type'] == 'all')
		    {
			    $allowedGroupIds = array(-1);
		    }
		    else
		    {
			    $allowedGroupIds = $input['user_group_ids'];
		    }
			
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_SmilieCategory');
			$dw->setExistingData($smilieCategory);
			$dw->bulkSet(array(
			    'allowed_user_group_ids' => $allowedGroupIds
			));
		    $dw->save();
		}
		
		return $response;
	}
	protected function _getUserGroupModel()
	{
		return $this->getModelFromCache('XenForo_Model_UserGroup');
	}
}