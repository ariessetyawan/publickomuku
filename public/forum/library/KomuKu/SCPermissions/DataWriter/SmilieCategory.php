<?php

class KomuKu_SCPermissions_DataWriter_SmilieCategory extends XFCP_KomuKu_SCPermissions_DataWriter_SmilieCategory
{
	const SCID = 'SCID';
	
	protected function _getFields()
	{
		$fields = parent::_getFields();
		
		$fields['kmk_smilie_category']['allowed_user_group_ids'] = array('type' => self::TYPE_UNKNOWN, 'default' => '', 'verification' => array('$this', '_verifyAllowedUserGroupIds'));
		
		return $fields;
	}
	protected function _verifyAllowedUserGroupIds(&$userGroupIds)
	{
		if (!is_array($userGroupIds))
		{
			$userGroupIds = preg_split('#,\s*#', $userGroupIds);
		}

		$userGroupIds = array_map('intval', $userGroupIds);
		$userGroupIds = array_unique($userGroupIds);
		sort($userGroupIds, SORT_NUMERIC);
		$userGroupIds = implode(',', $userGroupIds);

		return true;
	}
	protected function _postSave()
	{
		parent::_postSave();
		XenForo_Application::set(KomuKu_SCPermissions_DataWriter_SmilieCategory::SCID, $this->getMergedData());
	}
}