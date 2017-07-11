<?php

class KomuKu_DiscussionsPerPage_Listener
{
	public static $globalData = null;

	public static $userOptions = null;

	public static function load_proxy($class, array &$extend)
	{
		$proxy = array(
			'XenForo_DataWriter_User',

			'XenForo_ControllerPublic_Account',
			'XenForo_ControllerPublic_Conversation'
		);

		if (in_array($class, $proxy))
		{
			$extend[] = str_replace('Listener', '', __CLASS__) . $class;
		}
	}

	public static function visitor_setup(XenForo_Visitor &$visitor)
	{
		if (empty($visitor['user_id']))
		{
			return;
		}

		$userOptions = $visitor['custom_messages'];

		if (!is_array($userOptions))
		{
			$userOptions = @unserialize($userOptions);
			$userOptions = !is_array($userOptions) ? array() : $userOptions;
		}
		self::$userOptions = $userOptions;

		$options = XenForo_Application::get('options');

		if (array_key_exists('posts', $userOptions) && $userOptions['posts'])
		{
			$options->set('messagesPerPage', $userOptions['posts']);
		}

		if (array_key_exists('threads', $userOptions) && $userOptions['threads'])
		{
			$options->set('discussionsPerPage', $userOptions['threads']);
		}
	}

	public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		switch($hookName)
		{
			case 'account_preferences_options':
				$params = $template->getParams();
				$params += $hookParams;
				
				$choices = XenForo_Application::getOptions()->DiscussionsPerPage_AddChoices;
				$choices = array_unique($choices);
				
				$params += array(
					'choices' => $choices,
					'selected' => self::$userOptions
				);

				$contents .= $template->create('account_preferences_options_dicussion', $params);
				break;
		}
	}

	public static function template_create(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if($templateName == 'account_preferences')
		{
			$template->preloadTemplate('account_preferences_options_dicussion');
		}
		else if($templateName == 'post_permalink')
		{
			$templateName = 'post_permalink_customize';
		}
	}


}