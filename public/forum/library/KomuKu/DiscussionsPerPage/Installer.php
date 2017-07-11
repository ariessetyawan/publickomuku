<?php

class KomuKu_DiscussionsPerPage_Installer
{
	public static function install($existing)
	{
		$db = XenForo_Application::getDb();

		try
		{
			$db->query("ALTER TABLE kmk_user_option ADD custom_messages blob default null");
		}
		catch(Zend_Db_Exception $e) {}

		if ($existing['version_id'] < 200)
		{
			try
			{
				$db->query("alter table kmk_user_option change custom_messages custom_messages blob default null");
			}
			catch(Zend_Db_Exception $e) {}
		}
		
	}

	public static function uninstall()
	{
		$db = XenForo_Application::getDb();

		try
		{
			$db->query("alter table kmk_user_option drop column custom_messages");
		}
		catch(Zend_Db_Exception $e) {}
	}

}