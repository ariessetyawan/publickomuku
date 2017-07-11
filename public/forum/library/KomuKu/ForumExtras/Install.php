<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_Install
{
    //Add our custom table to the database
	public static function install()
	{
		$db = XenForo_Application::get('db');

		$db->query("
			CREATE TABLE IF NOT EXISTS `kmk_forum_extra_view_settings` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
				`node_id` INT(9) NOT NULL DEFAULT '0',
				`message_count` INT(9) NOT NULL DEFAULT '0',
				`daily_posts` INT(9) NOT NULL DEFAULT '0',
		        `register_date` INT(9) NOT NULL DEFAULT '0',
				`user_age` INT(9) NOT NULL DEFAULT '0',
				`user_gender` VARCHAR(255) DEFAULT '',
				`ban` VARCHAR(255) DEFAULT '',
				PRIMARY KEY (`id`)
			)   ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci 
			");

    }
	
	
	//Drop our custom table upon mod un-installation
	public static function uninstall()
	{
		$db = XenForo_Application::get('db');

		$db->query("
			DROP TABLE IF EXISTS `kmk_forum_extra_view_settings`
		");

	}
}