<?php

class KChaUser_Installer
{
	public static function install()
	{
		$db = XenForo_Application::get('db');
		
		$db->query("
		     CREATE TABLE if not exists xu_username_change_logs
		     (
			     change_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				 user_id INT(10) UNSIGNED NOT NULL,
				 old_username VARCHAR(200),
				 new_username VARCHAR(200),
				 is_private INT(10) UNSIGNED NOT NULL,
				 change_date INT(10) UNSIGNED NOT NULL,
				 PRIMARY KEY (change_id)
			 )
		");
	}
	public static function uninstall()
	{
		$db = XenForo_Application::get('db');
		
		$db->query("DROP TABLE xu_username_change_logs");
	}
}