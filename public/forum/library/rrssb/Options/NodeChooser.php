<?php

class rrssb_Options_NodeChooser extends XenForo_Option_NodeChooser
{
	public static function renderMultiSelectBox(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		return self::_render('rrssb_option_excludeForums', $view, $fieldPrefix, $preparedOption, $canEdit);
	}
}