<?php

class KomuKuJVC_Listeners_TemplateHooks
{
	public static function templateCreate(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{
		switch ($templateName)
		{
			case 'forum_list_sidebar':
				$template->preloadTemplate('sfdir_new_business_listings');
			break;
		}
	}

	public static function templateHook($name, &$contents, array $params, XenForo_Template_Abstract $template)
	{
		$globalParams = $template->getParams();
		$options = XenForo_Application::get('options');
		$dirModel = XenForo_Model::create('KomuKuJVC_Model_Dir');


		switch ($name)
		{
			case 'forum_list_sidebar':
			if ($options->DisplayRecentBusinessListingsOnForum == 1)
			{
				$viewParams = array(
					'recentBusinessListings'	=> $dirModel->getRecentBusinessListings()
				);
				$recentEntryTemplate = $template->create('sfdir_new_business_listings', $viewParams);
				$needle = '<!-- end block: sidebar_online_users -->';
				$contents = str_replace($needle, $needle . $recentEntryTemplate, $contents);
			}
			break;
		}
	}
}