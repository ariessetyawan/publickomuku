<?php

class XenForo_Install_Upgrade_1020032 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.2.0 Beta 2';
	}

	public function step1()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_addon
				MODIFY addon_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_navigation
				MODIFY navigation_id VARBINARY(25) NOT NULL,
				MODIFY parent_navigation_id VARBINARY(25) NOT NULL,
				MODIFY admin_permission_id VARBINARY(25) NOT NULL DEFAULT '',
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_permission
				MODIFY admin_permission_id VARBINARY(25) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_permission_entry
				MODIFY admin_permission_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_search_type
				MODIFY search_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_template
				MODIFY title VARBINARY(50) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_template_compiled
				MODIFY title VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_template_modification
				MODIFY addon_id VARBINARY(25) NOT NULL,
				MODIFY template VARBINARY(50) NOT NULL,
				MODIFY modification_key VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_admin_template_phrase
				MODIFY phrase_title VARBINARY(75) NOT NULL
		");
	}

	public function step2()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_attachment
				MODIFY content_type VARBINARY(25) NOT NULL
		");
	}

	public function step3()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_bb_code_media_site
				MODIFY media_site_id VARBINARY(25) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_bb_code_parse_cache
				MODIFY content_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_code_event
				MODIFY event_id VARBINARY(50) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_code_event_listener
				MODIFY event_id VARBINARY(50) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_content_spam_cache
				MODIFY content_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_content_type
				MODIFY content_type VARBINARY(25) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_content_type_field
				MODIFY content_type VARBINARY(25) NOT NULL,
				MODIFY field_name VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_cron_entry
				MODIFY entry_id VARBINARY(25) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_data_registry
				MODIFY data_key VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_deferred
				MODIFY unique_key VARBINARY(50) default NULL
		");
	}

	public function step4()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_deletion_log
				MODIFY content_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_draft
				MODIFY draft_key VARBINARY(75) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_edit_history
				MODIFY content_type VARBINARY(25) NOT NULL
		");
	}

	public function step5()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_email_template
				MODIFY title VARBINARY(50) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_email_template_compiled
				MODIFY title VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_email_template_modification
				MODIFY addon_id VARBINARY(25) NOT NULL,
				MODIFY template VARBINARY(50) NOT NULL,
				MODIFY modification_key VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_email_template_phrase
				MODIFY title VARBINARY(50) NOT NULL,
				MODIFY phrase_title VARBINARY(75) NOT NULL
		");
	}

	public function step6()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_liked_content
				MODIFY content_type VARBINARY(25) NOT NULL
		");
	}

	public function step7()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_moderation_queue
				MODIFY content_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_moderator_content
				MODIFY content_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_moderator_log
				MODIFY content_type VARBINARY(25) NOT NULL
		");
	}

	public function step8()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_news_feed
				MODIFY content_type VARBINARY(25) NOT NULL COMMENT 'eg: thread'
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_option
				MODIFY option_id VARBINARY(50) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_option_group
				MODIFY group_id VARBINARY(50) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_option_group_relation
				MODIFY option_id VARBINARY(50) NOT NULL,
				MODIFY group_id VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_permission
				MODIFY permission_id VARBINARY(25) NOT NULL,
				MODIFY permission_group_id VARBINARY(25) NOT NULL,
				MODIFY interface_group_id VARBINARY(50) NOT NULL,
				MODIFY depend_permission_id VARBINARY(25) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");
	}

	public function step9()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_permission_cache_content
				MODIFY content_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_permission_entry
				MODIFY permission_group_id VARBINARY(25) NOT NULL,
				MODIFY permission_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_permission_entry_content
				MODIFY content_type VARBINARY(25) NOT NULL,
				MODIFY permission_group_id VARBINARY(25) NOT NULL,
				MODIFY permission_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_permission_group
				MODIFY permission_group_id VARBINARY(25) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_permission_interface_group
				MODIFY interface_group_id VARBINARY(50) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");
	}

	public function step10()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_phrase
				MODIFY title VARBINARY(75) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_phrase_compiled
				MODIFY title VARBINARY(75) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_phrase_map
				MODIFY title VARBINARY(75) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_poll
				MODIFY content_type VARBINARY(25) NOT NULL
		");
	}

	public function step11()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_registration_spam_cache
				MODIFY cache_key VARBINARY(128) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_captcha_log
				MODIFY hash VARBINARY(40) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_report
				MODIFY content_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_route_filter
				MODIFY route_type VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_route_prefix
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");
	}

	public function step12()
	{

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_stats_daily
				MODIFY stats_type VARBINARY(25) NOT NULL
		");
	}

	public function step13()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_style_property_definition
				MODIFY group_name VARBINARY(25),
				MODIFY property_name VARBINARY(100) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_style_property_group
				MODIFY group_name VARBINARY(25) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_template
				MODIFY title VARBINARY(50) NOT NULL,
				MODIFY addon_id VARBINARY(25) NOT NULL DEFAULT ''
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_template_compiled
				MODIFY title VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_template_history
				MODIFY title VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_template_map
				MODIFY title VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_template_modification
				MODIFY addon_id VARBINARY(25) NOT NULL,
				MODIFY template VARBINARY(50) NOT NULL,
				MODIFY modification_key VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_template_phrase
				MODIFY phrase_title VARBINARY(75) NOT NULL
		");
	}

	public function step14()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE kmk_user_alert
				MODIFY content_type VARBINARY(25) NOT NULL COMMENT 'eg: trophy',
				MODIFY action VARBINARY(25) NOT NULL COMMENT 'eg: edit'
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_user_alert_optout
				MODIFY alert VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_user_field
				MODIFY field_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_user_field_value
				MODIFY field_id VARBINARY(25) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_user_group_change
				MODIFY change_key VARBINARY(50) NOT NULL
		");

		$this->executeUpgradeQuery("
			ALTER TABLE kmk_warning
				MODIFY content_type VARBINARY(25) NOT NULL
		");
	}
}