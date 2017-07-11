<?php
class KomuKu_ProfileBbCode_Listener_InitDependencies
{
	public static function initDependenciesListener(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		XenForo_Template_Helper_Core::$helperCallbacks['profilebbcode'] = array('KomuKu_ProfileBbCode_Template_Helper_ProfileBbCode', 'helperProfileBbCode');
		XenForo_Template_Helper_Core::$helperCallbacks['snippetext'] = array('KomuKu_ProfileBbCode_Template_Helper_ProfileBbCode', 'helperSnippetExt');
	}
}
?>
