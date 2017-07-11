<?php
class KomuKu_FollowingAlerts_Listener {
	public static function loadControllers($class, array &$extend) {
		static $controllers = array(
			'XenForo_ControllerPublic_Member',
			//'XenForo_ControllerPublic_Account',

			'XenForo_Model_ForumWatch',
			'XenForo_Model_ThreadWatch',
			
			'XenForo_DataWriter_Follower',
			'XenForo_DataWriter_DiscussionMessage_Post',
			'XenForo_DataWriter_DiscussionMessage_ProfilePost',
			
			'XenResource_Model_CategoryWatch',
			'XenResource_Model_ResourceWatch',
			'XenResource_DataWriter_Update'
		);

		if (in_array($class, $controllers)) {
			$extend[] = 'KomuKu_FollowingAlerts_' . $class;
		}
	}

	public static function template_create(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{
		switch($templateName)
		{
			case 'member_follow':
				$model = XenForo_Model::create('KomuKu_FollowingAlerts_Model_Follow');
				
				$params += array(
					'followthread' => $model->canFollowThread(),
					'followpost' => $model->canFollowPost(),
					'followstatus' => $model->canFollowStatus(),
					'followresource' => $model->canFollowResource()
				);

				$templateName = 'member_follow_alerts';
				break;
			case 'member_unfollow':
				$templateName = 'member_unfollow_alerts';
				break;
		}
	}

	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		XenForo_Template_Helper_Core::$helperCallbacks['followhtml'] = array(
			'KomuKu_FollowingAlerts_Template_Helper_Core', 'helperFollowHtml'
		);
	}

}