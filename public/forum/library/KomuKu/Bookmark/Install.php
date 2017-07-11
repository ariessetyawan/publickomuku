<?php

class KomuKu_Bookmark_Install
{
    public static function install()
    {
        $db = XenForo_Application::get('db');		
		
		try
		{	
			$db->query("
				CREATE TABLE kmk_bookmark (
				bookmark_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				user_id INT(10) UNSIGNED NOT NULL,
				post_id INT(10) UNSIGNED NOT NULL,
				note VARCHAR(36) NOT NULL
				) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
			");
		}
		catch (Zend_Db_Exception $e) {}
		
		try
		{	
			$db->query("
				ALTER TABLE kmk_bookmark ADD UNIQUE INDEX post_user_id (post_id, user_id)
			");		
		}
		catch (Zend_Db_Exception $e) {}		
    }
}