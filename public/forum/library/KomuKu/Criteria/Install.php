<?php

class KomuKu_Criteria_Install
{
	public static function install($installedAddon)
	{
		if (XenForo_Application::$versionId < 1020070)
		{
			throw new XenForo_Exception('This add-on requires XenForo 1.2.0 or newer.', true);
		}
	}
}