<?php

class KomuKu_featuredmembers_Install {
    /**
     * Install
     */
    public static function install($previous) {
        $db = XenForo_Application::getDb();

        XenForo_Db::beginTransaction($db);

		if(!$previous)
		{
			self::installQuery();
		} else {

			if($previous['version_id'] <= 901010090)
			{
				self::update901010090Query();
			}

            if($previous['version_id'] == 901010090)
            {
                self::update901020090Query();
            }
		}
        /**
         * Alter
         */
        try {
            // kmk_user
            $db->query("ALTER TABLE `kmk_user` ADD `dad_fm_is_featured` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_staff`");
            $db->query("ALTER TABLE `kmk_user` ADD `dad_fm_is_verified` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_staff`");
        } catch (Zend_Db_Exception $e) {}

        XenForo_Db::commit($db);
    }

    /**
     * Uninstall
     */
    public static function uninstall() {
        $db = XenForo_Application::getDb();

        XenForo_Db::beginTransaction($db);

        /**
         * Alter
         */
        try {
            // kmk_user
            $db->query("ALTER TABLE `kmk_user` DROP `dad_fm_is_featured`");
            $db->query("ALTER TABLE `kmk_user` DROP `dad_fm_is_verified`");
        } catch (Zend_Db_Exception $e) {}

        XenForo_Db::commit($db);
    }
	
	/**
		Install Query
	**/
	protected static function installQuery(){
		$db = XenForo_Application::getDb();

        XenForo_Db::beginTransaction($db);

        /**
         * Alter
         */
        try {
            // kmk_user
            $db->query("ALTER TABLE `kmk_user` ADD `dad_fm_is_featured` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_staff`");
            $db->query("ALTER TABLE `kmk_user` ADD `dad_fm_is_verified` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_staff`");
        } catch (Zend_Db_Exception $e) {}

        XenForo_Db::commit($db);
	}

	/**
		Update Query for version 901010090
	**/
	protected static function update901010090Query(){
		$db = XenForo_Application::getDb();

        XenForo_Db::beginTransaction($db);

        /**
         * Alter
         */
        try {
            // kmk_user
            $db->query("ALTER TABLE `kmk_user` ADD `dad_fm_is_featured` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_staff`");
            $db->query("ALTER TABLE `kmk_user` ADD `dad_fm_is_verified` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_staff`");
        } catch (Zend_Db_Exception $e) {}

        XenForo_Db::commit($db);
	}

    /*
	protected static function update901020090Query(){
        $db = XenForo_Application::getDb();

        XenForo_Db::beginTransaction($db);

        try {
            // kmk_user
            $db->query("ALTER TABLE `kmk_user` CHANGE `is_featured` `dad_fm_is_featured` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'");
            $db->query("ALTER TABLE `kmk_user` CHANGE `is_verified` `dad_fm_is_verified` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'");
        } catch (Zend_Db_Exception $e) {}

        XenForo_Db::commit($db);
    }*/
    protected static function update901020090Query(){
        $db = XenForo_Application::getDb();

        XenForo_Db::beginTransaction($db);

        try {
            // kmk_user
            $db->query("ALTER TABLE `kmk_user` DROP `is_featured`");
            $db->query("ALTER TABLE `kmk_user` DROP `is_verified`");
        } catch (Zend_Db_Exception $e) {}

        XenForo_Db::commit($db);
    }

}