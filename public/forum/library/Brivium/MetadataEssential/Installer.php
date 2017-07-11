<?php
class Brivium_MetadataEssential_Installer extends Brivium_BriviumHelper_Installer
{	
	protected $_installerType = 1;
	
	public static function install($existingAddOn, $addOnData)
	{
		self::$_addOnInstaller = __CLASS__;
		if (self::$_addOnInstaller && class_exists(self::$_addOnInstaller))
		{
			$installer = self::create(self::$_addOnInstaller);
			$installer->installAddOn($existingAddOn, $addOnData);
		}
		return true;
	}
	
	public static function uninstall($addOnData)
	{
		self::$_addOnInstaller = __CLASS__;
		if (self::$_addOnInstaller && class_exists(self::$_addOnInstaller))
		{
			$installer = self::create(self::$_addOnInstaller);
			$installer->uninstallAddOn($addOnData);
		}
	}
	public function getTables()
	{
		$tables = array();
		$tables["kmk_brivium_metadata"] = "
			CREATE TABLE IF NOT EXISTS `kmk_brivium_metadata` (
			  `metadata_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `content_type` varchar(50) NOT NULL,
			  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `title` text,
			  `description` text,
			  `keywords` text,
			  `author` text,
			  `index` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `follow` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `use_child` tinyint(3) unsigned NOT NULL DEFAULT '1',
			  PRIMARY KEY (`metadata_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8
		";
		return $tables;
	}
	
	public function getAlters()
	{
		$alters = array();
		$alters['kmk_brivium_metadata'] = array(
			'title'		=>	"  TEXT NULL",
			'index'		=>	" TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '0'",
			'follow'	=>	" TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '0'",
			'use_child'	=>	" TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '1'",
		);
		return $alters;
	}
	
	
	public function getQueryFinal()
	{
		$query = array();
		$query[] = "
			DELETE FROM `kmk_brivium_listener_class` WHERE `addon_id` = 'Brivium_MetadataEssential';
		";
		if($this->_triggerType != "uninstall"){
			$query[] = "
				REPLACE INTO `kmk_brivium_addon` 
					(`addon_id`, `title`, `version_id`, `copyright_removal`, `start_date`, `end_date`) 
				VALUES
					('Brivium_MetadataEssential', 'Brivium - Metadata Essential', '1080000', 0, 0, 0);
			";
			$query[] = "
				REPLACE INTO `kmk_brivium_listener_class` 
					(`class`, `class_extend`, `event_id`, `addon_id`) 
				VALUES
					('XenForo_BbCode_Formatter_Base', 'Brivium_MetadataEssential_BbCode_Formatter_Base', 'load_class_bb_code', 'Brivium_MetadataEssential'),
					('XenResource_DataWriter_Resource', 'Brivium_MetadataEssential_DataWriter_Resource', 'load_class_datawriter', 'Brivium_MetadataEssential'),
					('XenResource_DataWriter_Category', 'Brivium_MetadataEssential_DataWriter_Category', 'load_class_datawriter', 'Brivium_MetadataEssential'),
					('XenResource_ControllerPublic_Resource', 'Brivium_MetadataEssential_ControllerPublic_Resource', 'load_class_controller', 'Brivium_MetadataEssential'),
					('XenResource_ControllerAdmin_Category', 'Brivium_MetadataEssential_ControllerAdmin_Category', 'load_class_controller', 'Brivium_MetadataEssential'),
					('XenForo_ViewPublic_Thread_View', 'Brivium_MetadataEssential_ViewPublic_Thread_View', 'load_class_view', 'Brivium_MetadataEssential'),
					('XenForo_DataWriter_Forum', 'Brivium_MetadataEssential_DataWriter_Forum', 'load_class_datawriter', 'Brivium_MetadataEssential'),
					('XenForo_DataWriter_Discussion_Thread', 'Brivium_MetadataEssential_DataWriter_Discussion_Thread', 'load_class_datawriter', 'Brivium_MetadataEssential'),
					('XenForo_ControllerPublic_Thread', 'Brivium_MetadataEssential_ControllerPublic_Thread', 'load_class_controller', 'Brivium_MetadataEssential'),
					('XenForo_ControllerPublic_Forum', 'Brivium_MetadataEssential_ControllerPublic_Forum', 'load_class_controller', 'Brivium_MetadataEssential'),
					('XenForo_ControllerAdmin_Forum', 'Brivium_MetadataEssential_ControllerAdmin_Forum', 'load_class_controller', 'Brivium_MetadataEssential'),
					('XenResource_ViewPublic_Resource_Description', 'Brivium_MetadataEssential_ViewPublic_Resource_Description', 'load_class_view', 'Brivium_MetadataEssential');
			";
		}else{
			$query[] = "
				DELETE FROM `kmk_brivium_addon` WHERE `addon_id` = 'Brivium_MetadataEssential';
			";
		}
		return $query;
	}
}

?>