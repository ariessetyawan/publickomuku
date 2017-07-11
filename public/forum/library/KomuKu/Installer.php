<?php
class KomuKu_Installer {
	protected static $_tables = array(
		'given' => array(
			'createQuery' => 'CREATE TABLE IF NOT EXISTS `kmk_KomuKu_given` (
				`given_id` INT(10) UNSIGNED AUTO_INCREMENT
				,`post_id` INT(10) UNSIGNED NOT NULL
				,`received_user_id` INT(10) UNSIGNED NOT NULL
				,`received_username` VARCHAR(50) NOT NULL
				,`given_user_id` INT(10) UNSIGNED NOT NULL
				,`given_username` VARCHAR(50) NOT NULL
				,`give_date` INT(10) UNSIGNED NOT NULL
				,`points` INT(11) NOT NULL
				,`comment` VARCHAR(255)
				, PRIMARY KEY (`given_id`)
				,UNIQUE INDEX `post_id_given_user_id` (`post_id`,`given_user_id`)
				, INDEX `received_user_id` (`received_user_id`)
				, INDEX `given_user_id` (`given_user_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
			'dropQuery' => false
		)
	);
	protected static $_patches = array(
		array(
			'table' => 'kmk_user',
			'field' => 'kmk_KomuKu_given',
			'showColumnsQuery' => 'SHOW COLUMNS FROM `kmk_user` LIKE \'kmk_KomuKu_given\'',
			'alterTableAddColumnQuery' => 'ALTER TABLE `kmk_user` ADD COLUMN `kmk_KomuKu_given` INT(11) DEFAULT \'0\'',
			'alterTableDropColumnQuery' => false
		)
	);

	public static function install() {
		$db = XenForo_Application::get('db');

		foreach (self::$_tables as $table) {
			$db->query($table['createQuery']);
		}
		
		foreach (self::$_patches as $patch) {
			$existed = $db->fetchOne($patch['showColumnsQuery']);
			if (empty($existed)) {
				$db->query($patch['alterTableAddColumnQuery']);
			}
		}
		
		// since 1.2
		$db->query("REPLACE INTO `kmk_content_type` VALUES ('reputation', 'KomuKu', '')");
		$db->query("REPLACE INTO `kmk_content_type_field` VALUES ('reputation', 'alert_handler_class', 'KomuKu_AlertHandler_Reputation')");
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
		
		// since 1.3
		$existed = $db->fetchOne("SHOW COLUMNS FROM `kmk_post` LIKE 'kmk_KomuKu_latest_given'");
		if (empty($existed)) {
			$db->query("ALTER TABLE `kmk_post` ADD COLUMN `kmk_KomuKu_latest_given` BLOB");
		}
	}
	
	public static function uninstall() {
		// TODO
	}
}