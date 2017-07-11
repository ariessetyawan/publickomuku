<?php
class Brivium_ExtraTrophiesAwarded_Installer extends Brivium_BriviumHelper_Installer
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
	
	protected function _postInstall()
	{
		XenForo_Application::defer('Brivium_ExtraTrophiesAwarded_Deferred_Trophy', array('batch' => 250), 'BRETA_rebuild_trophies', true);
	}
	
	public function getAlters()
	{
		$alters = array();
		
		$alters['kmk_trophy'] = array(
			"breta_select" => "text",
			"breta_url" => "text",
			"breta_fa" => "text",
			"breta_icon_date" => "int(10) unsigned NOT NULL DEFAULT '0'",
		);
		
		$alters['kmk_user'] = array(
			"breta_user_level" => "int(11) NOT NULL DEFAULT '1'",
			"breta_curent_level" => "int(11) NOT NULL DEFAULT '0'",
			"breta_next_level" => "int(11) NOT NULL DEFAULT '0'",
		);
		
		$alters['kmk_user_trophy'] = array(
			"breta_show_icon" => "tinyint(3) NOT NULL DEFAULT '1'"
		);
		
		return $alters;
	}
	
	public function getQueryFinal()
	{
		$query = array();
		$query[] = "
			DELETE FROM `kmk_brivium_listener_class` WHERE `addon_id` = 'BR_ExtraTrophiesAwarded';
		";
		if($this->_triggerType != "uninstall"){
			$query[] = "
				REPLACE INTO `kmk_brivium_addon` 
					(`addon_id`, `title`, `version_id`, `copyright_removal`, `start_date`, `end_date`) 
				VALUES
					('BR_ExtraTrophiesAwarded', 'Brivium - Extra Trophies Awarded', '1030000', 0, 0, 0);
			";
			$query[] = "
				REPLACE INTO `kmk_brivium_listener_class` 
					(`class`, `class_extend`, `event_id`, `addon_id`) 
				VALUES
					('XenForo_ControllerAdmin_Trophy', 'Brivium_ExtraTrophiesAwarded_ControllerAdmin_Trophy', 'load_class_controller', 'BR_ExtraTrophiesAwarded'),
					('XenForo_ControllerPublic_Conversation', 'Brivium_ExtraTrophiesAwarded_ControllerPublic_Conversation', 'load_class_controller', 'BR_ExtraTrophiesAwarded'),
					('XenForo_ControllerPublic_Help', 'Brivium_ExtraTrophiesAwarded_ControllerPublic_Help', 'load_class_controller', 'BR_ExtraTrophiesAwarded'),
					('XenForo_ControllerPublic_Member', 'Brivium_ExtraTrophiesAwarded_ControllerPublic_Member', 'load_class_controller', 'BR_ExtraTrophiesAwarded'),
					('XenForo_ControllerPublic_Thread', 'Brivium_ExtraTrophiesAwarded_ControllerPublic_Thread', 'load_class_controller', 'BR_ExtraTrophiesAwarded'),
					('XenForo_DataWriter_Trophy', 'Brivium_ExtraTrophiesAwarded_DataWriter_Trophy', 'load_class_datawriter', 'BR_ExtraTrophiesAwarded'),
					('XenForo_Model_Trophy', 'Brivium_ExtraTrophiesAwarded_Model_Trophy', 'load_class_model', 'BR_ExtraTrophiesAwarded');
			";
		}else{
			$query[] = "
				DELETE FROM `kmk_brivium_addon` WHERE `addon_id` = 'BR_ExtraTrophiesAwarded';
			";
		}
		return $query;
	}
}
?>