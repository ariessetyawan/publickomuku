<?php 

class KomuKu_MemberlistSorting_Listeners_Listener
{
	const COPYRIGHT_MODIFICATION_SIMPLE_CACHE_KEY = 'th_copyrightModification';

    public static function load_class_controller($class, array &$extend)
	{
	    if ($class == 'XenForo_ControllerPublic_Member')
		{
			$extend[] = 'KomuKu_MemberlistSorting_ControllerPublic_Member';
		}
	}

    public static function load_class_model($class, array &$extend)
	{
	    if ($class == 'XenForo_Model_User')
		{
			$extend[] = 'KomuKu_MemberlistSorting_Model_User';
		}
	}

	public static function copyrightNotice(array $matches)
	{
		$copyrightModification = XenForo_Application::getSimpleCacheData(self::COPYRIGHT_MODIFICATION_SIMPLE_CACHE_KEY);

		if ($copyrightModification < XenForo_Application::$time) {
			XenForo_Application::setSimpleCacheData(self::COPYRIGHT_MODIFICATION_SIMPLE_CACHE_KEY,
				XenForo_Application::$time);
		}

		return $matches[0] .
			'<xen:if is="!{$adCopyrightShown} && !{$thCopyrightShown}">' .
			'<xen:set var="$thCopyrightShown">1</xen:set>' .
			'<div id="thCopyrightNotice">' .
			'Some XenForo functionality crafted by <a href="https://www.themehouse.com/xenforo/addons" title="Premium XenForo Add-ons" target="_blank">ThemeHouse</a>.' .
			'</div>' .
			'</xen:if>';
	}
} 