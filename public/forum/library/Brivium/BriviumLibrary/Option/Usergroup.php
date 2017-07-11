<?php

/**
 * Helper for choosing what happens by default to spam threads.
 *
 * @package XenForo_Options
 */
abstract class Brivium_LocationBBCode_Option_Render
{
	public static function renderUserGroups(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$userGroups = XenForo_Model::create('XenForo_Model_UserGroup')->getAllUserGroups();
		foreach ($userGroups AS $userGroupId => $userGroup)
		{
			$formatParams[$userGroupId] = array(
				'label' => $userGroup['title'],
				'value' => $userGroup['user_group_id'],
				'selected' => in_array($userGroup['user_group_id'], $preparedOption['option_value'])
			);
		}
		$preparedOption['formatParams'] = $formatParams;
		
		return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal('option_list_option_checkbox', $view, $fieldPrefix, $preparedOption, $canEdit);
	}
	
}