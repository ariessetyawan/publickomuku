<?php

class AnonymousPosting_Installer
{
    /* Start auto-generated lines of code. Change made will be overwriten... */

    protected static $_tables = array();
    protected static $_patches = array(
        array(
            'table' => 'kmk_post',
            'field' => 'anonymous_posting_real_user_id',
            'showTablesQuery' => 'SHOW TABLES LIKE \'kmk_post\'',
            'showColumnsQuery' => 'SHOW COLUMNS FROM `kmk_post` LIKE \'anonymous_posting_real_user_id\'',
            'alterTableAddColumnQuery' => 'ALTER TABLE `kmk_post` ADD COLUMN `anonymous_posting_real_user_id` INT(10) UNSIGNED NOT NULL DEFAULT \'0\'',
            'alterTableDropColumnQuery' => 'ALTER TABLE `kmk_post` DROP COLUMN `anonymous_posting_real_user_id`',
        ),
        array(
            'table' => 'kmk_post',
            'field' => 'anonymous_posting_real_username',
            'showTablesQuery' => 'SHOW TABLES LIKE \'kmk_post\'',
            'showColumnsQuery' => 'SHOW COLUMNS FROM `kmk_post` LIKE \'anonymous_posting_real_username\'',
            'alterTableAddColumnQuery' => 'ALTER TABLE `kmk_post` ADD COLUMN `anonymous_posting_real_username` VARCHAR(50) NOT NULL DEFAULT \'0\'',
            'alterTableDropColumnQuery' => 'ALTER TABLE `kmk_post` DROP COLUMN `anonymous_posting_real_username`',
        ),
    );

    public static function install($existingAddOn, $addOnData)
    {
        $db = XenForo_Application::get('db');

        foreach (self::$_tables as $table) {
            $db->query($table['createQuery']);
        }

        foreach (self::$_patches as $patch) {
            $tableExisted = $db->fetchOne($patch['showTablesQuery']);
            if (empty($tableExisted)) {
                continue;
            }

            $existed = $db->fetchOne($patch['showColumnsQuery']);
            if (empty($existed)) {
                $db->query($patch['alterTableAddColumnQuery']);
            }
        }

        self::installCustomized($existingAddOn, $addOnData);
    }

    public static function uninstall()
    {
        $db = XenForo_Application::get('db');

        foreach (self::$_patches as $patch) {
            $tableExisted = $db->fetchOne($patch['showTablesQuery']);
            if (empty($tableExisted)) {
                continue;
            }

            $existed = $db->fetchOne($patch['showColumnsQuery']);
            if (!empty($existed)) {
                $db->query($patch['alterTableDropColumnQuery']);
            }
        }

        foreach (self::$_tables as $table) {
            $db->query($table['dropQuery']);
        }

        self::uninstallCustomized();
    }

    /* End auto-generated lines of code. Feel free to make changes below */

    public static function installCustomized($existingAddOn, $addOnData)
    {
        if (XenForo_Application::$versionId < 1020000) {
            throw new XenForo_Exception('XenForo 1.2.0+ is required.');
        }

        $db = XenForo_Application::getDb();

        if (empty($existingAddOn)) {
            $effectiveVersionId = 0;
        } else {
            $effectiveVersionId = $existingAddOn['version_id'];
        }

        if ($effectiveVersionId == 0) {
            $db->query("
				INSERT IGNORE INTO kmk_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'forum', 'anonymous_posting_post', permission_value, 0
				FROM kmk_permission_entry
				WHERE permission_group_id = 'forum' AND permission_id = 'postThread'
			");

            $db->query("
				INSERT IGNORE INTO kmk_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'general', 'anonymous_posting_reveal', permission_value, 0
				FROM kmk_permission_entry
				WHERE permission_group_id = 'general' AND permission_id = 'bypassUserPrivacy'
			");
        }

        if ($effectiveVersionId < 12) {
            $db->query("
				INSERT IGNORE INTO kmk_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, 'forum', 'anonymous_posting_seeUser', permission_value, 0
				FROM kmk_permission_entry
				WHERE permission_group_id = 'forum' AND permission_id = 'postThread'
			");
        }
    }

    public static function uninstallCustomized()
    {
        AnonymousPosting_ShippableHelper_Updater::onUninstall(null, 'anonymous_posting');
    }

}