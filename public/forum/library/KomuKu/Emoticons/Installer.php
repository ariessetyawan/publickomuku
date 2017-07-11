<?php

class KomuKu_Emoticons_Installer
{
	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected static $_db;

	public static function install()
	{
		static::$_db = XenForo_Application::getDb();

		foreach(static::getTables() as $createSql) {
			static::$_db->query($createSql);
		}
	}

	public static function getTables()
	{
		$tables = array();

		$tables['kmk_user_emoticon'] = "
			CREATE TABLE IF NOT EXISTS kmk_user_emoticon(
				emoticon_id int auto_increment,
				user_id int unsigned not null,
				caption varchar(50) not null,
				text_replace varchar(25) not null,
				added_at int unsigned not null,
				width int unsigned not null,
				height int unsigned not null,
				file_size int unsigned not null,
				filename varchar(100) not null,
				filehash varchar(32) not null,
				extension varchar(8) not null,
				PRIMARY KEY (emoticon_id),
				KEY user_id (user_id),
				INDEX added_at (added_at) 
			)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
		";

		return $tables;
	}

	public static function uninstall()
	{
		static::$_db = XenForo_Application::getDb();

		foreach(array_keys(static::getTables()) as $tableName) {
			static::$_db->query("DROP TABLE IF EXISTS {$tableName}");
		}

		XenForo_Model::create('XenForo_Model_DataRegistry')->delete('KomuKu_emoticons');
	}
}