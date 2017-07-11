<?php
class KomuKu_NodesGrid_Install_Controller
{
	public static function install($existingAddOn, $addOnData, $xml = null)
	{
		if(XenForo_Application::$versionId < 1020000)
			throw new XenForo_Exception('This add-on requires XenForo 1.2 or higher.', true);

		if(!$existingAddOn) // New installation
		{
			self::runStep(1, $addOnData['version_id']);
		}
		else // Upgrades
		{
			if($addOnData['version_id'] > $existingAddOn['version_id'])
			{
				self::runStep($existingAddOn['version_id'] + 1, $addOnData['version_id']);
			}
		}
	}

	public static function uninstall()
	{
		if(self::isFieldExists('grid_column', 'kmk_node'))
		{
			XenForo_Application::get('db')->query("
				ALTER TABLE `kmk_node` DROP `grid_column`
			");
		}
	}

	protected static function runStep($step, $limit)
	{
		while($step <= $limit)
		{
			$fn = 'version'.$step;
			if(method_exists(get_class(), $fn))
				self::$fn();
			++$step;
		}
	}

	/**
	* Check if $field exists in $table
	*/
	protected static function isFieldExists($field, $table)
	{
		if(XenForo_Application::get('db')->fetchRow("
			SHOW columns FROM `{$table}`
			WHERE field = '{$field}'
		"))
			return true;

		return false;
	}

	/**
	* v1.0.0
	*/
	protected static function version1()
	{
		if(!self::isFieldExists('grid_column', 'kmk_node'))
		{
			XenForo_Application::get('db')->query("
				ALTER TABLE `kmk_node` 
				ADD (`grid_column` BOOLEAN NOT NULL DEFAULT FALSE)
			");
		}
	}
}
?>