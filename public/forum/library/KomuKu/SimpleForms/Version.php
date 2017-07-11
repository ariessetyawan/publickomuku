<?php

class KomuKu_SimpleForms_Version {
	const ASCENDING_ORDER = 'ascendingOrder';
	const DESCENDING_ORDER = 'descendingOrder';
	
	public static function Install()
	{
		if (XenForo_Application::$versionId < 1030000)
		{
			throw new XenForo_Exception('This add-on requires XenForo 1.3.0 or higher.', true);
		}   

		$installedVersionId = self::_addonExists();

		// get the upgrade files
		$upgradeFiles = self::_getFiles(self::ASCENDING_ORDER);
		
		// get the database connection and begin a transaction
		$db = XenForo_Application::get('db');
		$db->beginTransaction();
		
		try
		{
			foreach ($upgradeFiles as $upgradeFile)
			{
				$versionId = str_replace('.php', '', $upgradeFile);
				
				if (!$installedVersionId || $installedVersionId < $versionId)
				{
					// execute the upgrade
					$className = 'KomuKu_SimpleForms_Install_' . $versionId;
					
					$upgrade = new $className();
					if ($upgrade->install($db))
					{
						// set the installed version as the version we just upgraded to
						$installedVersionId = $versionId;
					}
					else
					{
						throw new Exception('Error upgrading to version ' . $versionId);
					}
				}
			}
		}
		catch (Exception $e)
		{
			$db->rollback();
			
			$controllerResponse = new XenForo_ControllerResponse_Error();
			$controllerResponse->errorText = 'There was an error while upgrading your add-on to version ' . $versionId . '. Please contact KomuKu technical support.<br/><br/><pre>' . $e . '</pre>';
			$controllerResponse->responseCode = 200;
			
			throw new XenForo_ControllerResponse_Exception($controllerResponse);
		}
		
		$db->commit();
	}
 
	public static function Uninstall() 
	{
		$db = XenForo_Application::get('db');
		
		// delete content types
		$db->delete('kmk_content_type_field', "content_type = 'form'");
		$db->delete('kmk_content_type', "content_type = 'form'");
		
		// drop tables
		$db->query("DROP TABLE IF EXISTS `kmkform__response_field`");
		$db->query("DROP TABLE IF EXISTS `kmkform__response`");
		$db->query("DROP TABLE IF EXISTS `kmkform__field`");
		$db->query("DROP TABLE IF EXISTS `kmkform__form_destination`");
		$db->query("DROP TABLE IF EXISTS `kmkform__form_destination_option`");
		$db->query("DROP TABLE IF EXISTS `kmkform__form`");
		$db->query("DROP TABLE IF EXISTS `kmkform__destination_option`");
		$db->query("DROP TABLE IF EXISTS `kmkform__destination`");
		$db->query("DROP TABLE IF EXISTS `kmkform__forum_form`");
		$db->query("DROP TABLE IF EXISTS `kmkform__page`");
		
		// delete permissions
		$db->delete('kmk_permission_cache_content', "content_type = 'form'");
		$db->delete('kmk_permission_entry_content', "content_type = 'form'");
		
		// remove the local key
		$dataRegistry = new XenForo_Model_DataRegistry();
		$dataRegistry->delete('kmkform__localkey');
	}
	
	protected static function _getFiles($order)
	{
		$files = scandir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Install');
		foreach ($files as $key => $file)
		{
			if ($file == '.' || $file == '..' || $file == 'Abstract.php')
			{
				unset($files[$key]);
			}
		}

		switch ($order)
		{
		    case self::DESCENDING_ORDER:
		        {
		            ksort($files, SORT_STRING);
		        }
		    default:
		        {
		            sort($files, SORT_STRING);
		        }
		}
		
		return $files;
	}
	
	protected static function _contentTypeExists() 
	{
		$db = XenForo_Application::get('db');
		$result = $db->fetchOne("SELECT COUNT(*) FROM kmk_content_type WHERE content_type = 'form'");
		
		if ($result == 1) 
		{
			return true;
		}
		
		return false;
	}
	
	protected static function _addonExists()
	{
		$db = XenForo_Application::get('db');
		$result = $db->fetchOne("SELECT version_id FROM kmk_addon WHERE addon_id = 'KomuKu_SimpleForms'");
		
		if (!$result)
		{
			return false;
		}
		
		return $result;
	}
}