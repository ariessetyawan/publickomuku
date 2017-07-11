<?php

class XenForo_Install_Upgrade_1040670 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.4.6';
	}

	public function step1()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_image_proxy
			ADD INDEX is_processing (is_processing)
		");
	}

	public function step2()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_login_attempt
			ADD INDEX ip_address_attempt_date (ip_address, attempt_date)
		");
	}
}