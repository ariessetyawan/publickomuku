<?php

//######################## Extra Thread View Settings By KomuKu ###########################
class KomuKu_ThreadExtras_Install
{
    //Add our custom fields to the thread table
	public static function install()
	{
		$db = XenForo_Application::getDb();

		XenForo_Db::beginTransaction($db);
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD posts INT(9) NOT NULL DEFAULT '0'
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD daily_posts INT(9) NOT NULL DEFAULT '0'
			");
		}
		catch (Zend_Db_Exception $e) {}

		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD thread_count INT(9) NOT NULL DEFAULT '0'
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD user_likes INT(9) NOT NULL DEFAULT '0'
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD user_trophy INT(9) NOT NULL DEFAULT '0'
			");
		}
		catch (Zend_Db_Exception $e) {}
		
	    try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD reg_days INT(9) NOT NULL DEFAULT '0'
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD age INT(9) NOT NULL DEFAULT '0'
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD user_gender VARCHAR(255) DEFAULT ''
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD user_moderation VARCHAR(255) DEFAULT ''
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD specific_users VARCHAR(500) DEFAULT ''
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					ADD specific_users_extra mediumblob
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		XenForo_Db::commit($db);

    }
	
	
	//Drop our custom fields upon mod un-installation
	public static function uninstall()
	{
		$db = XenForo_Application::getDb();

		XenForo_Db::beginTransaction($db);

		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN posts
			");
		}
		catch (Zend_Db_Exception $e) {}

	    try
		{
			$db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN daily_posts
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN thread_count
			");
		}
		catch (Zend_Db_Exception $e) {}
	    
	    try
		{
			$db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN user_likes
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN user_trophy
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{
			$db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN reg_days
			");
		}
		catch (Zend_Db_Exception $e) {}
	    
	   try
	   {
		   $db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN age
		   ");
	   }
	   catch (Zend_Db_Exception $e) {}
	   
	   try
	   {
		   $db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN user_gender
		   ");
	   }
	   catch (Zend_Db_Exception $e) {}
	  
	   try
	   {
		   $db->query("
				ALTER TABLE kmk_thread
					DROP COLUMN user_moderation
		   ");
	   }
	   catch (Zend_Db_Exception $e) {}
		
	   XenForo_Db::commit($db);

	}
	
}