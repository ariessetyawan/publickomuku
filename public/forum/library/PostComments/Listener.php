<?php

class PostComments_Listener
{
	// Load Class Controller
    public static function loadClassController($class, array &$extend)
	{
		switch ($class)
		{
			case 'XenForo_ControllerPublic_Thread':
				$extend[] = 'PostComments_ControllerPublic_Thread';
				break;
			case 'XenForo_ControllerPublic_Post':
				$extend[] = 'PostComments_ControllerPublic_Post';
				break;
			case 'XenForo_ControllerAdmin_Forum':
				$extend[] = 'PostComments_ControllerAdmin_Forum';
				break;
		}
	}

	// Load Class Datawriter
	public static function loadDataWriter($class, array &$extend)
	{
		switch ($class)
		{
			case 'XenForo_DataWriter_DiscussionMessage_Post':
				$extend[] = 'PostComments_DataWriter_DiscussionMessage_Post';
				break;
			case 'XenForo_DataWriter_Forum':
				$extend[] = 'PostComments_DataWriter_Forum';
				break;
		}
	}
	
	// Load Class Model
	public static function loadClassModel($class, array &$extend)
	{		
		switch ($class)
		{
			case 'XenForo_Model_Thread':
				$extend[] = 'PostComments_Model_Alert';
				break;
		}
	}

	// Template hook
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		switch ($hookName)
		{
			// Add the option in the forum acp 
			case 'forum_edit_basic_information':
				$viewParams = array_merge($template->getParams(), $hookParams);
				$ourTemplate = $template->create('forum_comment_count', $viewParams);
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			// Add the javascript necessary for the templates
			case 'thread_view_qr_after':
				$viewParams = array_merge($template->getParams(), $hookParams);
				$ourTemplate = $template->create('post_comment_js', $viewParams);
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
		}
	}

	// Initial Dependencies
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		$contentTypes = XenForo_Application::get('contentTypes');

		$contentTypes['post_comment']['report_handler_class'] = 'PostComments_ReportHandler_PostComment';

		XenForo_Application::set('contentTypes', $contentTypes);
	}
}