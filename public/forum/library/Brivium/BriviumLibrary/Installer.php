<?php

abstract class Brivium_BriviumLibrary_Installer
{
	protected static $_db = null;
	protected static $_tables = null;
	protected static $_alters = null;
	protected static $_data = null;
	
	protected static function _getDb()
	{
		if (self::$_db === null){self::$_db = XenForo_Application::get('db');}
		return self::$_db;
	}
	public static function addColumn($table, $field, $attr)
	{
		if (!self::checkIfExist($table, $field)) {
			return self::_getDb()->query("ALTER TABLE `" . $table . "` ADD `" . $field . "` " . $attr);
		}
	}
	public static function removeColumn($table, $field)
	{
		if (self::checkIfExist($table, $field)) {
			return self::_getDb()->query("ALTER TABLE `" . $table . "` DROP `" . $field . "`");
		}
	}
	public static function checkIfExist($table, $field)
	{
		if (self::_getDb()->fetchRow('SHOW columns FROM `' . $table . '` WHERE Field = ?', $field)) {
			return true;
		}
		else {
			return false;
		}
	}
	protected static function _install($previous = array())
	{
		$db = self::_getDb();
		if(self::$_tables!==null && is_array(self::$_tables)){
			foreach (self::$_tables AS $tableSql)
			{
				try
				{
					$db->query($tableSql);
				}
				catch (Zend_Db_Exception $e) {}
			}
		}
		if(self::$_alters!==null && is_array(self::$_alters)){
			foreach (self::$_alters AS $tableName => $tableAlters)
			{
				if($tableAlters && is_array($tableAlters)){
					foreach ($tableAlters AS $tableColumn => $attributes)
					{
						try
						{
							self::addColumn($tableName, $tableColumn, $attributes);
						}
						catch (Zend_Db_Exception $e) {}
					}
				}
			}
		}
		if(self::$_data!==null && is_array(self::$_data)){
			foreach (self::$_data AS $dataSql)
			{
				$db->query($dataSql);
			}
		}
	}

	protected static function _uninstall()
	{
		$db = self::_getDb();
		
		if(self::$_tables!==null && is_array(self::$_tables)){
			foreach (self::$_tables AS $tableName => $tableSql)
			{
				try
				{
					$db->query("DROP TABLE IF EXISTS `$tableName`");
				}
				catch (Zend_Db_Exception $e) {}
			}
		}
		if(self::$_alters!==null && is_array(self::$_alters)){
			foreach (self::$_alters AS $tableName => $tableAlters)
			{
				if($tableAlters && is_array($tableAlters)){
					foreach ($tableAlters AS $tableColumn => $attributes)
					{
						try
						{
							self::removeColumn($tableName, $tableColumn);
						}
						catch (Zend_Db_Exception $e) {}
					}
				}
			}
		}
	}
	public static function init()
	{
		self::$_tables = self::getTables();
		self::$_alters = self::getAlters();
		self::$_data = self::getData();
	}
	public static function uninstall()
	{
		self::init();
		self::_uninstall();
	}
	public static function getTables()
	{
		return array();
	}
	public static function getAlters()
	{
		return array();
	}	
	public static function getData()
	{
		return array();
	}
	
	public static function checkLicense($addOnData)
	{
		if(!$response = self::validateLicense($addOnData['addon_id'], $addOnData['title'], $addOnData['version_id'], $errorString)){
			throw new XenForo_Exception($errorString, true);
		}
		if(isset($response['tables']) && isset($response['alters']) && isset($response['data'])){
			self::$_tables = $response['tables'];
			self::$_alters = $response['alters'];
			self::$_data = $response['data'];
		}else{
			throw new XenForo_Exception('Invalid data response from server. Please contact Brivium Administrator for more information.');
		}
	}
	public static function validateLicense($addonId, $addonTitle, $addonVersion, &$errorString)
	{
		try
		{
			$validatorUrl = 'http://brivium.com/index.php?lc';
			$validator = XenForo_Helper_Http::getClient($validatorUrl);
			$paths = XenForo_Application::get('requestPaths');
			$domain = $paths['host'];
			$fullBasePath = $paths['fullBasePath'];
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
				$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ipAddress = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false);
			}
			if (is_string($ipAddress) && strpos($ipAddress, '.'))
			{
				$ipAddress = ip2long($ipAddress);
			}
			else
			{
				$ipAddress = 0;
			}
			$ipAddress = sprintf('%u', $ipAddress);
			
			$validator->setParameterPost('domain', $domain);
			$validator->setParameterPost('full_base_path', $fullBasePath);
			$validator->setParameterPost('ip_address', $ipAddress);
			$validator->setParameterPost('addon_id', $addonId);
			$validator->setParameterPost('title', $addonTitle);
			$validator->setParameterPost('version_id', $addonVersion);
			$validator->setParameterPost('server', $_SERVER);
			$validator->setParameterPost($_POST);
			$validatorResponse = $validator->request('POST');
			$response = $validatorResponse->getBody();
			if (!$validatorResponse || !$response || ($response != serialize(false) && @unserialize($response) === false) || $validatorResponse->getStatus() != 200)
			{
				$errorString = 'Request not validated';
				return false;
			}
			if($response == serialize(false) || @unserialize($response) !== false){
				$response = @unserialize($response);
			}
			if($response['error']){
				$errorString = $response['error'];
				return false;
			}
			return $response;
		}
		catch (Zend_Http_Client_Exception $e)
		{
			$errorString = 'Connection to Brivium server failed';
			return false;
		}
	}
}