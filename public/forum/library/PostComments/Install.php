<?php

class PostComments_Install
{
	public static function install()
	{
		$db = XenForo_Application::get('db');

		// Add the post comments table
		$db->query("
		CREATE TABLE IF NOT EXISTS kmk_post_comments (
				comment_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
				user_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
				username VARCHAR(50) NOT NULL DEFAULT '',
				content_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
				comment MEDIUMTEXT NOT NULL,
				comment_date INT(10) UNSIGNED NOT NULL DEFAULT '0',
				PRIMARY KEY (comment_id),
				KEY date (comment_date)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci

			");	

        // Add the comment_count field in the posts table
	    if (!$db->fetchRow("SHOW columns FROM kmk_post WHERE Field = 'comment_count'"))
		{
			$db->query("ALTER TABLE `kmk_post` ADD COLUMN comment_count INT(11) DEFAULT '0'");
		}

		// Add the comment_count field in the forum table
	    if (!$db->fetchRow("SHOW columns FROM kmk_forum WHERE Field = 'comment_count'"))
		{
			$db->query("ALTER TABLE `kmk_forum` ADD COLUMN comment_count INT(11) DEFAULT '0'");
		}

		// Adds the necessary content type for the news-handler
		if (!$db->fetchRow("SELECT * FROM kmk_content_type WHERE content_type = 'post_comment'"))
		{
			$db->query("INSERT INTO kmk_content_type (content_type, addon_id, fields) VALUES ('post_comment', 'PostComments', '')");
			$db->query("INSERT INTO kmk_content_type_field (content_type, field_name, field_value) VALUES ('post_comment', 'news_feed_handler_class', 'PostComments_NewsFeedHandler_Comment')");
		}

		// Rebuild content cache
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
    }

	public static function uninstall()
	{
		$db = XenForo_Application::get('db');
		//Drop the post comments table
		$db->query("
			DROP TABLE IF EXISTS `kmk_post_comments`
		");

		// Drop the comment_count field from the users table
		if ($db->fetchRow("SHOW columns FROM kmk_post WHERE Field = 'comment_count'"))
		{
			$db->query("ALTER TABLE `kmk_post` DROP comment_count");
		}

		// Drop the comment_count field from the forum table
		if ($db->fetchRow("SHOW columns FROM kmk_forum WHERE Field = 'comment_count'"))
		{
			$db->query("ALTER TABLE `kmk_forum` DROP comment_count");
		}

		// Delete content type of post comments
		if ($db->fetchRow("SELECT * FROM kmk_content_type WHERE content_type = 'post_comment'"))
		{
			$db->query("DELETE FROM `kmk_content_type` WHERE `content_type` = 'post_comment'");
			$db->query("DELETE FROM `kmk_content_type_field` WHERE `content_type` = 'post_comment'");

			XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
		}

		// Delete all info of post comments from news handler
		if ($db->fetchRow("SELECT * FROM kmk_news_feed WHERE content_type = 'post_comment'"))
		{
			$db->query("DELETE FROM `kmk_news_feed` WHERE `content_type` = 'post_comment'");
		}
	}
}