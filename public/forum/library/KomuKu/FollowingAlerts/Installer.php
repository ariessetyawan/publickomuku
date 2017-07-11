<?php
class KomuKu_FollowingAlerts_Installer {
	public static function install() {
		$db = XenForo_Application::get('db');
		
		try
		{
			$db->query('ALTER TABLE `kmk_user_follow` ADD COLUMN `alert_preferences` MEDIUMBLOB DEFAULT NULL');
		}
		catch (Zend_Db_Exception $e) {}
	}

	public static function uninstall() {
		$db = XenForo_Application::get('db');

		try
		{
			$db->query('ALTER TABLE `kmk_user_follow` DROP COLUMN `alert_preferences`');
		}
		catch (Zend_Db_Exception $d) {}
	}
}