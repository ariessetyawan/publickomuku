<?php

class KomuKu_ProfileBbCode_Listener_Proxy
{
	public static function loadViewMemberView($class, array &$extend)
	{
		$extend[] = 'KomuKu_ProfileBbCode_ViewPublic_Member_View';
	}

	public static function loadViewProfilePostEdit($class, array &$extend)
	{
		$extend[] = 'KomuKu_ProfileBbCode_ViewPublic_ProfilePost_Edit';
	}

	public static function loadControllerPublicMember($class, array &$extend)
	{
		$extend[] = 'KomuKu_ProfileBbCode_ControllerPublic_Member';
	}

	public static function loadControllerPublicProfilePost($class, array &$extend)
	{
		$extend[] = 'KomuKu_ProfileBbCode_ControllerPublic_ProfilePost';
	}

	public static function loadBbCodeFormatterBase($class, array &$extend)
	{
		$extend[] = 'KomuKu_ProfileBbCode_BbCode_Formatter_Restricted';
	}
}