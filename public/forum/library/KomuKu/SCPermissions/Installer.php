<?php

class KomuKu_SCPermissions_Installer
{
	public static function install()
	{
		$db = XenForo_Application::get('db');
		
		try
		{
		    $db->query("
		        ALTER TABLE kmk_smilie_category
			    ADD allowed_user_group_ids BLOB DEFAULT NULL
		   ");
		}
		catch (Zend_Db_Exception $e) {}
	}
	public static function uninstall()
	{
		 $db = XenForo_Application::get('db');
		 
		 try
		 {
		     $db->query("ALTER TABLE kmk_smilie_category DROP allowed_user_group_ids");
		 }
		 catch (Zend_Db_Exception $e) {}
	}
}