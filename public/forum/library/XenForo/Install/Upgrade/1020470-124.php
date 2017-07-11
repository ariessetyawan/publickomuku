<?php

class XenForo_Install_Upgrade_1020470 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.2.4';
	}

	public function step1()
	{
		$this->executeUpgradeQuery("
			DELETE log
			FROM kmk_admin_template_modification_log AS log
			LEFT JOIN kmk_admin_template AS template ON (log.template_id = template.template_id)
			WHERE template.template_id IS NULL
		");

		$this->executeUpgradeQuery("
			DELETE log
			FROM kmk_email_template_modification_log AS log
			LEFT JOIN kmk_email_template AS template ON (log.template_id = template.template_id)
			WHERE template.template_id IS NULL
		");

		$this->executeUpgradeQuery("
			DELETE log
			FROM kmk_template_modification_log AS log
			LEFT JOIN kmk_template AS template ON (log.template_id = template.template_id)
			WHERE template.template_id IS NULL
		");
	}
}