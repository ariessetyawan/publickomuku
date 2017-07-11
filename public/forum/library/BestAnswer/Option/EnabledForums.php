<?php

abstract class BestAnswer_Option_EnabledForums
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		// Get all of the forums
		$preparedOption['formatParams'] = XenForo_Option_NodeChooser::getNodeOptions(0, false, 'Forum');
		
		return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
			'option_best_answer_enabled_forums',
			$view, $fieldPrefix, $preparedOption, $canEdit
		);
	}
}