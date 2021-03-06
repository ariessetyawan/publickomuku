<?php

class XenForo_Install_Upgrade_1050970 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.5.9';
	}

	public function step1()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_session_activity CHANGE controller_name controller_name VARBINARY(75) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_search_type CHANGE handler_class handler_class VARCHAR(75) NOT NULL
		");
	}
}