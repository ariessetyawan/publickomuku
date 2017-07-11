<?php

class XenForo_Install_Upgrade_1000051 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.0.0 Release Candidate 1';
	}

	public function step1()
	{
		$db = $this->_getDb();

		// rename and repurpose kmk_ban_ip table to kmk_ip_match for banning/discourager
		try
		{
			$db->query("
				ALTER TABLE kmk_ban_ip
				RENAME kmk_ip_match
			");

			$db->query("
				ALTER TABLE kmk_ip_match
				CHANGE banned_ip
					ip VARCHAR(25) NOT NULL,
				ADD match_type ENUM('banned','discouraged') NOT NULL DEFAULT 'banned' AFTER ip,
				DROP PRIMARY KEY,
				ADD PRIMARY KEY (ip, match_type)
			");
		}
		catch (Zend_Db_Exception $e) {}

		// add support for long strings to kmk_style_property_definition and increase property name length
		try
		{
			$db->query("
				ALTER TABLE kmk_style_property_definition
				CHANGE property_name
					property_name VARCHAR(100) NOT NULL,
				CHANGE scalar_type
					scalar_type ENUM('', 'longstring', 'color', 'number', 'boolean', 'template') NOT NULL DEFAULT  ''
			");
		}
		catch(Zend_Db_Exception $e) {}

		// add description support to kmk_style
		try
		{
			$db->query("
				ALTER TABLE kmk_style
				ADD description VARCHAR(100) NOT NULL DEFAULT ''
				AFTER title
			");
		}
		catch(Zend_Db_Exception $e) {}

		// increase character limit for feed URLs to support the max limit (IE supports 2083 chars)
		try
		{
			$db->query("
				ALTER TABLE kmk_Feed
				CHANGE url
					url VARCHAR(2083) NOT NULL
			");
		}
		catch (Zend_Db_Exception $e) {}

		return true;
	}
}