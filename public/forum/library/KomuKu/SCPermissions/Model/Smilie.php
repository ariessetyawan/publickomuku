<?php

class KomuKu_SCPermissions_Model_Smilie extends XFCP_KomuKu_SCPermissions_Model_Smilie
{
	public function getAllSmiliesCategorized($includeHidden = true)
	{
		$result = parent::getAllSmiliesCategorized($includeHidden);

		$smilies = $this->fetchAllKeyed('
			SELECT smilie.*, category.*
			FROM kmk_smilie AS smilie
			LEFT JOIN kmk_smilie_category AS category ON
				(category.smilie_category_id = smilie.smilie_category_id)
			' . ($includeHidden ? '' : 'WHERE smilie.display_in_editor = 1') . '
			ORDER BY category.display_order, smilie.display_order, smilie.title
		', 'smilie_id');
		$smilieCategories = $this->_getDefaultSmilieCategory();
		
		$viewingUser = XenForo_Visitor::getInstance()->toArray();
		foreach ($smilies AS $smilieId => $smilie)
		{
			$result[$smilie['smilie_category_id']]['viewCategory'] = $this->_verifyCategoryIsUsable($smilie, $viewingUser);
		}
		return $result;
	}
	protected function _verifyCategoryIsUsable(array $smilie, array $viewingUser)
	{
		if (!$smilie['allowed_user_group_ids'] && $smilie['smilie_category_id'])
		{
			return false;
		}
		if (!$smilie['allowed_user_group_ids'])
		{
			return true;
		}
		
		$userGroups = explode(',', $smilie['allowed_user_group_ids']);
		if (in_array(-1, $userGroups) || in_array($viewingUser['user_group_id'], $userGroups))
		{
			return true;
		}

		if ($viewingUser['secondary_group_ids'])
		{
			foreach (explode(',', $viewingUser['secondary_group_ids']) AS $userGroupId)
			{
				if (in_array($userGroupId, $userGroups))
				{
					return true;
				}
			}
		}

		return false;
	}
}