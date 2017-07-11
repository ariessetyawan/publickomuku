<?php

class KomuKu_ProfileCover_Listener
{
	public static function loadControllers($class, array &$extend)
	{
		static $controllers = array(
			'XenForo_ControllerPublic_Account',
			'XenForo_ControllerPublic_Member',

			'XenForo_DataWriter_User',
			'XenForo_Image_Gd',
		);

		if (in_array($class, $controllers))
		{
			$extend[] = 'KomuKu_ProfileCover_' . $class;
		}
	}

	public static function init_dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		if ($dependencies instanceof XenForo_Dependencies_Public)
		{
			XenForo_Template_Helper_Core::$helperCallbacks['profile_cover'] = array(
				'KomuKu_ProfileCover_Cover', 'helperUserCover'
			);
		}
	}

	public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		if (! KomuKu_ProfileCover_Cover::assertCanUploadCover())
		{
			return;
		}

		$contents .= $template->create('profile_cover_navigation_link', array_merge($template->getParams(), $hookParams))->render();
	}

	public static function template_create(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if (KomuKu_ProfileCover_Cover::assertCanUploadCover())
		{
			$template->preloadTemplate('profile_cover_navigation_link');
		}
	}

}

