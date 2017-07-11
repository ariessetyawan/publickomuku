<?php
class Brivium_JumpToPage_Installer extends Brivium_BriviumHelper_Installer
{
	protected $_installerType = 2;
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
	
	public function getQueryFinal()
	{
		$query = array();
		if($this->_triggerType != "uninstall"){
			$query[] = "
				REPLACE INTO `kmk_brivium_addon` 
					(`addon_id`, `title`, `version_id`, `copyright_removal`, `start_date`, `end_date`) 
				VALUES
					('Brivium_JumpToPage', 'Brivium - Jump to Page', '1000000', 0, 0, 0);
			";
		}else{
			$query[] = "
				DELETE FROM `kmk_brivium_addon` WHERE `addon_id` = 'Brivium_JumpToPage';
			";
		}
		return $query;
	}
}
?>