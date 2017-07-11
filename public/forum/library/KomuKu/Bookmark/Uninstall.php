<?php

class KomuKu_Bookmark_Uninstall
{
    public static function uninstall()
    {
        $db = XenForo_Application::get('db');
		
		try
		{		
			$db->query("
				DROP TABLE kmk_bookmark
			");
		}
		catch (Zend_Db_Exception $e) {}	
    }
}