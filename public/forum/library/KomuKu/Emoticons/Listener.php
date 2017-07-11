<?php

class KomuKu_Emoticons_Listener
{
	/**
	 * Class to extending
	 *
	 * @var array
	 */
	private static $_classList = array(
		'XenForo_ControllerPublic_Account',
		'XenForo_ControllerAdmin_Log',

		'XenForo_DataWriter_User',

		'XenForo_Model_Smilie',

	//	'XenForo_BbCode_Formatter_Base',
		'XenForo_BbCode_Formatter_Wysiwyg',

		// Suport XenForo Thread System
		'XenForo_ViewPublic_Thread_View',
		'XenForo_ViewPublic_Thread_ViewPosts',
		'XenForo_ViewPublic_Thread_ViewNewPosts',
		'XenForo_ViewPublic_Thread_Create',
		'XenForo_ViewPublic_Thread_CreatePreview',
		'XenForo_ViewPublic_Thread_Reply',
		'XenForo_ViewPublic_Thread_ReplyPreview',

		// Support XenForo Post System
		'XenForo_ViewPublic_Post_Edit',
		'XenForo_ViewPublic_Post_EditInline',
		'XenForo_ViewPublic_Post_EditPreview',

		// Support XenForo Conversation System
		'XenForo_ViewPublic_Conversation_View',
		'XenForo_ViewPublic_Conversation_Reply',
		'XenForo_ViewPublic_Conversation_Preview',
		'XenForo_ViewPublic_Conversation_EditMessage',
		'XenForo_ViewPublic_Conversation_EditMessageInline',
		'XenForo_ViewPublic_Conversation_EditMessagePreview',
		'XenForo_ViewPublic_Conversation_ViewMessage',
		'XenForo_ViewPublic_Conversation_ViewNewMessages',

		// Support XenForo Resource Manager
		'XenResource_ViewPublic_Resource_Add',
		'XenResource_ViewPublic_Resource_Description',
		'XenResource_ViewPublic_Resource_Preview',
		'XenResource_ViewPublic_Resource_Updates',
		'XenResource_ViewPublic_Update_Add',
		'XenResource_ViewPublic_Update_Edit',
		'XenResource_ViewPublic_Update_EditInline',
		'XenResource_ViewPublic_Version_Add',

		// Support XenForo Media Gallery
		'XenGallery_ViewPublic_Media_View',
		'XenGallery_ViewPublic_Media_Edit',
		'XenGallery_ViewPublic_Media_CommentEdit',
		'XenGallery_ViewPublic_Media_CommentEditInline',
		'XenGallery_ViewPublic_Media_LatestComments',
		'XenGallery_ViewPublic_Media_Save_CommentListItem',

		// Support [KomuKu] Social Groups.
		'KomuKu_Teams_BbCode_Formatter_Base',
		'KomuKu_Teams_BbCode_Formatter_Comment',
		'KomuKu_Teams_ViewPublic_Team_Extra',
		'KomuKu_Teams_ViewPublic_Ajax_WallPost',
		'KomuKu_Teams_ViewPublic_Post_Show',
		'KomuKu_Teams_ViewPublic_Post_Edit',
		'KomuKu_Teams_ViewPublic_Post_EditInline',

		'KomuKu_Teams_ViewPublic_Event_Add',
		'KomuKu_Teams_ViewPublic_Event_Comment',
		'KomuKu_Teams_ViewPublic_Event_View',

		// Support sonnb XenGallery
		'sonnb_XenGallery_ViewPublic_Album_Comment',
		'sonnb_XenGallery_ViewPublic_Album_Comments',
		'sonnb_XenGallery_ViewPublic_Album_View',
		'sonnb_XenGallery_ViewPublic_Comment_EditInline',

		// Support TaigaChat Pro
		'Dark_TaigaChat_ViewPublic_TaigaChat_List',
		'Dark_TaigaChat_BbCode_Formatter_Tenori',
		'Dark_TaigaChat_ViewPublic_TaigaChat_Edit',

		// Support [KomuKu] Messenger
		'KomuKu_Messenger_BbCode_Formatter_Text',

		// Support Siropu Chat
		'Siropu_Chat_ViewPublic_Edit',
		'Siropu_Chat_ViewPublic_Public',
	);

	public static function loadProxy($class, array &$extend)
	{
		if($class === 'XenForo_Model_Import')
		{
			XenForo_Model_Import::$extraImporters['KomuKu_Emoticons'] = 'KomuKu_Emoticons_Importer_UserSmilie';
		}

		if(in_array($class, self::$_classList)/* && !isset(self::$_loaded[$class])*/)
		{
			if(strpos($class, 'KomuKu_Teams_') === 0 OR strpos($class, 'KomuKu_Messenger_') === 0)
			{
				// Group all class of add-on Social Group into folders.
				$extend[] = 'KomuKu_Emoticons_' . str_replace('KomuKu_', '', $class);
			}
			else
			{
				$extend[] = 'KomuKu_Emoticons_' . $class;
			}
		}
	}

	public static function loadBbCode($class, array &$extend)
	{
		$extend[] = 'KomuKu_Emoticons_'.$class;
	}

	public static function loadClass($class, array &$extend)
	{
		static $classes = array(
			'KomuKu_Messenger_Output',
			'KomuKu_Teams_GroupNewsFeedHandler_Post',
		);

		if(in_array($class, $classes))
		{
			$extend[] = 'KomuKu_Emoticons_'.$class;
		}
	}

	public static function init_dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		if(!($dependencies instanceof XenForo_Dependencies_Public))
		{
			return;
		}

		/* @var $emoticonModel KomuKu_Emoticons_Model_Emoticon */
		$emoticonModel = XenForo_Model::create('KomuKu_Emoticons_Model_Emoticon');
		$emoticonModel->registerLazyLoader();
	}

	public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		if(!XenForo_Visitor::getInstance()->hasPermission('general', 'emoticons_enable'))
		{
			return;
		}

		$params = $template->getParams();
		$params += $hookParams;

		if($hookName == 'navigation_visitor_tab_links2'
			OR $hookName == 'account_wrapper_sidebar_settings'
		)
		{
			$contents .= $template->create('emoticon_'.$hookName, $params)->render();
		}
	}

	public static function template_create(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if(!XenForo_Visitor::getInstance()->hasPermission('general', 'emoticons_enable'))
		{
			return;
		}

		$template->preloadTemplate('emoticon_navigation_visitor_tab_links2');
	}

	public static function file_health_check(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
	{
		$hashes[] = KomuKu_Emoticons_Filesums::getHashes();
	}

}
