<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_Install
{
	//Add the custom stuff in the db
	public static function install()
	{
		//Get the db
	    $db = XenForo_Application::getDb();
		XenForo_Db::beginTransaction($db);
		
		//Add the custom table in the db
		try
		{
		    $db->query("
			    CREATE TABLE IF NOT EXISTS `kmk_liked_threads` (
				`like_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`thread_id`	INT(10) UNSIGNED NOT NULL,
				`user_id` INT(10) UNSIGNED NOT NULL,
				`username` VARCHAR(50) NOT NULL,
				`like_date`	INT(10) UNSIGNED NOT NULL,
				`message` VARCHAR(255),
				PRIMARY KEY (`like_id`),
				UNIQUE KEY `UNIQUE` (`thread_id`,`user_id`),
				KEY `thread_id` (`thread_id`)
			    )ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci 
		    ");
		}
		
		catch (Zend_Db_Exception $e) {}
		
		//Add the `like_count` field in the thread table
		try
		{
			$db->query("
				ALTER TABLE `kmk_thread` ADD COLUMN `like_count` INT(10) UNSIGNED NOT NULL DEFAULT '0'
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		//Add the `liked_thread_count` field in the user table
		try
		{
			$db->query("
				ALTER TABLE kmk_user ADD liked_thread_count INT UNSIGNED NOT NULL DEFAULT 0
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		//Insert our field in the kmk_content_type table
		try
			{
		       $db->query("
			        INSERT INTO kmk_content_type
				      (content_type, addon_id, fields)
			        VALUES
				       ('th', 'LikeThreads', '')
		        ");
			}
		catch (Zend_Db_Exception $e) {}	
		
		//Insert our field in the kmk_content_type_field table
		try
			{
		        $db->query("
			        INSERT INTO kmk_content_type_field
				        (content_type, field_name, field_value)
			        VALUES
				        ('th', 'news_feed_handler_class', 'KomuKu_LikeThreads_NewsFeedHandler_LikeThreads')
		            ");
			}
		catch (Zend_Db_Exception $e) {}	
		
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

		XenForo_Db::commit($db);

		$addOn = XenForo_Model::create('XenForo_Model_AddOn')->getAddOnById('LikeThreads');

		if ($addOn) {
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
			$dw->setExistingData('LikeThreads');
			$dw->set('install_callback_class', '');
			$dw->set('install_callback_method', '');
			$dw->set('uninstall_callback_class', '');
			$dw->set('uninstall_callback_method', '');
			$dw->delete();
		}
	}
	
	 //Drop the custom stuff from the db
	public static function uninstall()
	{
		//Get the db
	    $db = XenForo_Application::getDb();
		XenForo_Db::beginTransaction($db);

		//Drop the custom table from the db
		try
		{
		   $db->query("
			    DROP TABLE IF EXISTS `kmk_liked_threads`
		   ");
		}
		catch (Zend_Db_Exception $e) {}
		
		//Drop the `like_count` field from the thread table
		try
		{
			$db->query("
				ALTER TABLE kmk_thread DROP COLUMN `like_count`
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		//Drop the `liked_thread_count` field from the user table
		try
		{
			$db->query("
				ALTER TABLE kmk_user DROP COLUMN liked_thread_count
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		//Remove our fields from the kmk_content_type table
		try
		{
           $db->query("DELETE FROM kmk_content_type WHERE content_type IN ('th');");
        }
		catch (Zend_Db_Exception $e) {}
		
		//Remove our field from the kmk_content_type_field table
		try
		{
           $db->query("DELETE FROM kmk_content_type_field WHERE content_type IN ('th');");
		}
		catch (Zend_Db_Exception $e) {}
		
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

		XenForo_Db::commit($db);
	}
}