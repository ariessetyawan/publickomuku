<?php

class KomuKu_SCPermissions_Listener
{
	public static function loadController($class, array &$extend)
	{
		if ($class == "XenForo_ControllerAdmin_SmilieCategory")
		{
			$extend[] = "KomuKu_SCPermissions_ControllerAdmin_SmilieCategory";
		}
	}
	public static function loadWriter($class, array &$extend)
	{
		if ($class == "XenForo_DataWriter_SmilieCategory")
		{
			$extend[] = "KomuKu_SCPermissions_DataWriter_SmilieCategory";
		}
	}
	public static function loadModel($class, array &$extend)
	{
		if ($class == "XenForo_Model_Smilie")
		{
			$extend[] = "KomuKu_SCPermissions_Model_Smilie";
		}
	}
}