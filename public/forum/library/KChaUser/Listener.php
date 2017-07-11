<?php

class KChaUser_Listener
{
	public static function loadController($class, array &$extend)
	{
		if ($class == "XenForo_ControllerPublic_Account")
		{
			$extend[] = "KChaUser_ControllerPublic_Account";
		}
		if ($class == "XenForo_ControllerPublic_Member")
		{
			$extend[] =  "KChaUser_ControllerPublic_Members";
		}
	}
	public static function templateCreate(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if ($templateName == 'member_view')
		{
			$template->preloadTemplate('KChaUser_change_username_member_view_tabs_heading');
			$template->preloadTemplate('KChaUser_change_username_member_view_tabs_content');
		}
	}
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		switch ($hookName)
		{
		        case 'account_wrapper_sidebar_settings':
				    $visitor = XenForo_Visitor::getInstance();
					if ($visitor->hasPermission('usernameChanges', 'changeUsername')) 
			        {
				        $wrapper = $template->create('KChaUser_change_username_account_wrapper', $template->getParams());
		                $rendered = $wrapper->render();
				        $contents .= $rendered;
					}
			    break;
				
				case 'navigation_visitor_tab_links1':
				    $visitor = XenForo_Visitor::getInstance();
					if ($visitor->hasPermission('usernameChanges', 'changeUsername')) 
			        {
				        $wrapper = $template->create('xu_change_username_navigation_visitor_tab', $template->getParams());
		                $rendered = $wrapper->render();
				        $contents .= $rendered;
					}
			    break;
				
				case 'member_view_tabs_heading':
				     $model = XenForo_Model::create("KChaUser_Model_Changes");
				     $usernameChanges = $model->getAllChangesForUser($hookParams['user']['user_id']);
					 
				     if (isset($hookParams['user']) && $usernameChanges)
					 {
						 $wrapper = $template->create('xu_change_username_member_view_tabs_heading', $template->getParams());
		                 $rendered = $wrapper->render();
				         $contents .= $rendered;
					 }
				break;
				
				case 'member_view_tabs_content':
				     $model = XenForo_Model::create("KChaUser_Model_Changes");
				     $usernameChanges = $model->getAllChangesForUser($hookParams['user']['user_id']);
					 
				     if (isset($hookParams['user']) && $usernameChanges)
					 {
						 $wrapper = $template->create('xu_change_username_member_view_tabs_content', $template->getParams());
		                 $rendered = $wrapper->render();
				         $contents .= $rendered;
					 }
				break;
		}
	}
}