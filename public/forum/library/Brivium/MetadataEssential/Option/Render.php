<?php

/**
 * Helper for choosing what happens by default to spam threads.
 *
 * @package XenForo_Options
 */
abstract class Brivium_MetadataEssential_Option_Render
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
	
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$value = $preparedOption['option_value'];
		$seleted = 0;
		if($value)$seleted = -1;
		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));

		$forumOptions = XenForo_Option_NodeChooser::getNodeOptions(
			$seleted,
			sprintf('(%s)', new XenForo_Phrase('unspecified')),
			'Forum'
		);
		return $view->createTemplateObject('BRQCT_option_template_forumSelector', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'formatParams' => $forumOptions,
			'editLink' => $editLink
		));
	}
	public static function renderThreadMetadataOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$xenAddons = XenForo_Application::get('addOns');
		$xenTagAddon = 0;
		if(!empty($xenAddons['Tinhte_XenTag'])){
			$xenTagAddon = $xenAddons['Tinhte_XenTag'];
		}
		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));
		return $view->createTemplateObject('BRME_option_template_threadMetaData', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'xenTagAddon' => $xenTagAddon,
			'editLink' => $editLink
		));
	}
	public static function renderResourceMetadataOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$xenAddons = XenForo_Application::get('addOns');
		$xenTagAddon = 0;
		$xenResourceAddon = 0;
		if(!empty($xenAddons['Tinhte_XenTag'])){
			$xenTagAddon = $xenAddons['Tinhte_XenTag'];
		}
		if(!empty($xenAddons['XenResource'])){
			$xenResourceAddon = $xenAddons['XenResource'];
		}
		if(!$xenResourceAddon) return '';
		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));
		return $view->createTemplateObject('BRME_option_template_resourceMetaData', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'xenTagAddon' => $xenTagAddon,
			'xenResourceAddon' => $xenResourceAddon,
			'editLink' => $editLink
		));
	}
	public static function renderResourceCategoryOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$xenAddons = XenForo_Application::get('addOns');
		$xenResourceAddon = 0;
		if(!empty($xenAddons['XenResource'])){
			$xenResourceAddon = $xenAddons['XenResource'];
		}
		if(!$xenResourceAddon) return '';
		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));
		
		return $view->createTemplateObject('BRME_option_template_resourceCategoryMetaData', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'xenResourceAddon' => $xenResourceAddon,
			'editLink' => $editLink
		));
	}
	
}