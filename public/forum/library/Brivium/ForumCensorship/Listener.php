<?php
class Brivium_ForumCensorship_Listener extends Brivium_BriviumLibrary_EventListeners
{
	public static function loadClassController($class, &$extend)
	{
		switch($class){
			case 'XenForo_ControllerPublic_Forum':
				$extend[] = 'Brivium_ForumCensorship_ControllerPublic_Forum';
				break;
			case 'XenForo_ControllerPublic_Thread':
				$extend[] = 'Brivium_ForumCensorship_ControllerPublic_Thread';
				break;
		}
	}
	
	public static function loadClassModel($class, &$extend)
	{
		switch($class){
			case 'XenForo_Model_Thread':
				$extend[] = 'Brivium_ForumCensorship_Model_Thread';
				break;
		}
	}
	
	public static function loadClassBbCode($class, &$extend)
	{
		switch($class){
			case 'XenForo_BbCode_Formatter_Base':
				$extend[] = 'Brivium_ForumCensorship_BbCode_Formatter_Base';
				break;
		}
	}
	
	
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
		self::_templateHook($hookName, $contents, $hookParams, $template);
    }
}