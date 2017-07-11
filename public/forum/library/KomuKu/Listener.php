<?php
class KomuKu_Listener {
	public static function load_class($class, array &$extend) {
		static $classes = array(
			'XenForo_ControllerPublic_Thread',
			'XenForo_ControllerPublic_Post',
			'XenForo_ControllerPublic_Member',
		
			'XenForo_DataWriter_DiscussionMessage_Post',
		);
		
		if (in_array($class, $classes)) {
			$extend[] = str_replace('XenForo_', 'KomuKu_Extend_', $class);
		}
	}
	
	public static function load_class_importer($class, array &$extend) {
		if (strpos($class, 'vBulletin') != false AND !defined('KomuKu_Extend_Importer_vBulletin_LOADED')) {
			$extend[] = 'KomuKu_Extend_Importer_vBulletin';
		}		
	}
	
	public static function template_create($templateName, array &$params, XenForo_Template_Abstract $template) {
		switch ($templateName) {
			case 'thread_view':
				$template->preloadTemplate('KomuKu_injector');
				$template->preloadTemplate('KomuKu_message_user_info_extra');
				$template->preloadTemplate('KomuKu_message_latest_given');
				break;
			case 'member_view':
				$template->preloadTemplate('KomuKu_member_view_tabs_heading');
				$template->preloadTemplate('KomuKu_member_view_tabs_content');
				break;
			case 'account_alert_preferences':
				$template->preloadTemplate('KomuKu_account_alerts_achievements');
				break;
		}		
	}
	
	public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template) {
		switch ($hookName) {
			case 'message_user_info_extra':
				$ourTemplate = $template->create('KomuKu_message_user_info_extra', $hookParams);
				$ourTemplate->setParam('KomuKu_canViewUser', XenForo_Model::create('KomuKu_Model_Given')->canViewUser($hookParams['user']));
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'member_view_tabs_heading':
				$ourTemplate = $template->create('KomuKu_member_view_tabs_heading', $template->getParams());
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'member_view_tabs_content':
				$ourTemplate = $template->create('KomuKu_member_view_tabs_content', $template->getParams());
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'account_alerts_achievements':
				$ourTemplate = $template->create('KomuKu_account_alerts_achievements', $template->getParams());
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'message_content':
				// since 1.3
				if (KomuKu_Option::get('latestGiven') AND !empty($hookParams['message']['kmk_KomuKu_latest_given'])) {
					if (!is_array($hookParams['message']['kmk_KomuKu_latest_given'])) {
						$hookParams['message']['kmk_KomuKu_latest_given'] = unserialize($hookParams['message']['kmk_KomuKu_latest_given']);
					}
					
					if (!empty($hookParams['message']['kmk_KomuKu_latest_given'])) {
						$ourTemplate = $template->create('KomuKu_message_latest_given', $hookParams);
						$rendered = $ourTemplate->render();
						$contents .= $rendered;
					}
				}
				break;
			case 'footer':
				if (isset($GLOBALS['ReputationInjectorData']) AND KomuKu_Option::get('needsInjector')) {
					$ourParams = array(
						'ReputationInjectorData' => $GLOBALS['ReputationInjectorData'],
						'placeholder' => array(
							'postId' => '-35537',
						),
					);
					
					$ourTemplate = $template->create('KomuKu_injector');
					$ourTemplate->setParams($ourParams);
					$rendered = $ourTemplate->render();
					$contents .= $rendered;
				}
				break;
		}
	}
}