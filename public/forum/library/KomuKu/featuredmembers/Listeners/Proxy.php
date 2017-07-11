<?php

class KomuKu_featuredmembers_Listeners_Proxy
{
	public static function extendWidgetPageView($class, array &$extend)
	{
		$extend[] = 'KomuKu_featuredmembers_ViewPublic_WidgetFramework_WidgetPage_Index';
	}
}