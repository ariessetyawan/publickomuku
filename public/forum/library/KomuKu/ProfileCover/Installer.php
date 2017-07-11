<?php


class KomuKu_ProfileCover_Installer
{
	private static $db;

	protected static function getDb()
	{
		if (static::$db) return static::$db;

		static::$db = XenForo_Application::getDb();
		return static::getDb();
	}

	public static function install()
	{
		$db = static::getDb();

		try
		{
			$db->query("ALTER TABLE kmk_user ADD COLUMN cover_date int unsigned not null default 0");
		}
		catch(Zend_Db_Exception $e) {}

		try
		{
			$db->query("
				INSERT IGNORE INTO kmk_content_type
					(content_type, addon_id)
				VALUES
					('profile_cover', 'ProfileCover')
			");
			
			$db->query("
				INSERT IGNORE INTO kmk_content_type_field
					(content_type, field_name, field_value)
				VALUES
					('profile_cover', 'news_feed_handler_class', 'KomuKu_ProfileCover_NewsFeedHandler_Cover')
			");
		}
		catch (Zend_Db_Exception $e) {}
		

		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
	}

	public static function uninstall()
	{
		try
		{
			static::getDb()->query("ALTER TABLE kmk_user DROP COLUMN cover_date");
		}
		catch(Zend_Db_Exception $e) {}

		try
		{
			static::getDb()->query("ALTER TABLE kmk_user DROP COLUMN cover_drag_details");
		}
		catch(Zend_Db_Exception $e) {}

		$db = static::getDb();

		$db->query("DELETE FROM kmk_content_type WHERE addon_id = ?", array('ProfileCover'));
		$db->query("DELETE FROM kmk_content_type_field WHERE content_type = ?", array(KomuKu_ProfileCover_Cover::PROFILE_COVER));
		$db->delete('kmk_news_feed', 'content_type = ' . $db->quote(KomuKu_ProfileCover_Cover::PROFILE_COVER));

		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
	}

}