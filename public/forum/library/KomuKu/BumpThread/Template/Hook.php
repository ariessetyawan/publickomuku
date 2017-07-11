<?php

/**
 * Modify the templates to add the previous and next actions
 */
class KomuKu_BumpThread_Template_Hook
{

	/**
	 * Preload the templates we need
	 */
	public static function templateCreate($templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if ($templateName == 'thread_view')
		{
			$template->addRequiredExternal('js', 'js/jsbumpthread/bump-thread.js');
		}
		if ($templateName == 'forum_view')
		{
			$template->addRequiredExternal('js', 'js/jsbumpthread/bump-thread.js');
		}
	}

}