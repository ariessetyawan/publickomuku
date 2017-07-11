<?php

class HtmlCustomTitles_Listener
{
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		$helpers = XenForo_Template_Helper_Core::$helperCallbacks;

		$helpers['usertitle'] = array(
			'HtmlCustomTitles_Template_Helper_Core', 'helperUserTitle'
		);
		
		XenForo_Template_Helper_Core::$helperCallbacks = $helpers;
	}
}
