<?php

class KomuKu_SimpleForms_Install_12 extends KomuKu_SimpleForms_Install_Abstract
{
	public function install(&$db)
	{
		// add WYSIWYG to kmkform__field.field_type
		$db->query("ALTER TABLE `kmkform__field` MODIFY COLUMN `field_type` ENUM('textbox','textarea','select','radio','checkbox','multiselect','wysiwyg');");
		
		// add destination option conversation_hide_empty_fields
		$count = $db->fetchOne("
			SELECT
				COUNT(*)
			FROM `kmkform__destination_option`
			WHERE `option_id` = 'conversation_hide_empty_fields'
		");
		if ($count == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (`option_id`,`destination_id`,`display_order`,`field_type`,`format_params`,`field_choices`,`match_type`,`match_regex`,`match_callback_class`,`match_callback_method`,`max_length`,`required`,`evaluate_template`) VALUES
				('conversation_hide_empty_fields', 4, 110, 'checkbox', '', 'a:1:{s:7:\"enabled\";s:7:\"Enabled\";}', 'none', '', '', '', 0, 0, 0)
			");
		}
		
		// add destination option thread_hide_empty_fields
		$count = $db->fetchOne("
			SELECT
				COUNT(*)
			FROM `kmkform__destination_option`
			WHERE `option_id` = 'thread_hide_empty_fields'
		");
		if ($count == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (`option_id`,`destination_id`,`display_order`,`field_type`,`format_params`,`field_choices`,`match_type`,`match_regex`,`match_callback_class`,`match_callback_method`,`max_length`,`required`,`evaluate_template`) VALUES
				('thread_hide_empty_fields', 1, 110, 'checkbox', '', 'a:1:{s:7:\"enabled\";s:7:\"Enabled\";}', 'none', '', '', '', 0, 0, 0)
			");
		}		
		
		// add destination option post_hide_empty_fields
		$count = $db->fetchOne("
			SELECT
				COUNT(*)
			FROM `kmkform__destination_option`
			WHERE `option_id` = 'post_hide_empty_fields'
		");
		if ($count == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (`option_id`,`destination_id`,`display_order`,`field_type`,`format_params`,`field_choices`,`match_type`,`match_regex`,`match_callback_class`,`match_callback_method`,`max_length`,`required`,`evaluate_template`) VALUES
				('post_hide_empty_fields', 2, 50, 'checkbox', '', 'a:1:{s:7:\"enabled\";s:7:\"Enabled\";}', 'none', '', '', '', 0, 0, 0)
			");
		}
		
		// add destination option email_hide_empty_fields
		$count = $db->fetchOne("
			SELECT
				COUNT(*)
			FROM `kmkform__destination_option`
			WHERE `option_id` = 'email_hide_empty_fields'
		");
		if ($count == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (`option_id`,`destination_id`,`display_order`,`field_type`,`format_params`,`field_choices`,`match_type`,`match_regex`,`match_callback_class`,`match_callback_method`,`max_length`,`required`,`evaluate_template`) VALUES
				('email_hide_empty_fields', 3, 60, 'checkbox', '', 'a:1:{s:7:\"enabled\";s:7:\"Enabled\";}', 'none', '', '', '', 0, 0, 0)
			");
		}
		
		// add destination option conversation_enable_attachments
		$count = $db->fetchOne("
			SELECT
				COUNT(*)
			FROM `kmkform__destination_option`
			WHERE `option_id` = 'conversation_enable_attachments'	
		");
		if ($count == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (`option_id`,`destination_id`,`display_order`,`field_type`,`format_params`,`field_choices`,`match_type`,`match_regex`,`match_callback_class`,`match_callback_method`,`max_length`,`required`,`evaluate_template`) VALUES
				('conversation_enable_attachments', 4, 120, 'checkbox', '', 'a:1:{s:7:\"enabled\";s:7:\"Enabled\";}', 'none', '', '', '', 0, 0, 0);
			");
		}

		// add destination option thread_enable_attachments
		$count = $db->fetchOne("
			SELECT
				COUNT(*)
			FROM `kmkform__destination_option`
			WHERE `option_id` = 'thread_enable_attachments'
		");
		if ($count == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (`option_id`,`destination_id`,`display_order`,`field_type`,`format_params`,`field_choices`,`match_type`,`match_regex`,`match_callback_class`,`match_callback_method`,`max_length`,`required`,`evaluate_template`) VALUES
				('thread_enable_attachments', 1, 120, 'checkbox', '', 'a:1:{s:7:\"enabled\";s:7:\"Enabled\";}', 'none', '', '', '', 0, 0, 0)
			");
		}
		
		// add destination option post_enable_attachments
		$count = $db->fetchOne("
			SELECT
				COUNT(*)
			FROM `kmkform__destination_option`
			WHERE `option_id` = 'post_enable_attachments'
		");
		if ($count == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (`option_id`,`destination_id`,`display_order`,`field_type`,`format_params`,`field_choices`,`match_type`,`match_regex`,`match_callback_class`,`match_callback_method`,`max_length`,`required`,`evaluate_template`) VALUES
				('post_enable_attachments', 2, 60, 'checkbox', '', 'a:1:{s:7:\"enabled\";s:7:\"Enabled\";}', 'none', '', '', '', 0, 0, 0)
			");
		}

		// add destination option email_enable_attachments
		$count = $db->fetchOne("
			SELECT
				COUNT(*)
			FROM `kmkform__destination_option`
			WHERE `option_id` = 'email_enable_attachments'
		");
		if ($count == 0)
		{
			$db->query("
				INSERT INTO `kmkform__destination_option` (`option_id`,`destination_id`,`display_order`,`field_type`,`format_params`,`field_choices`,`match_type`,`match_regex`,`match_callback_class`,`match_callback_method`,`max_length`,`required`,`evaluate_template`) VALUES
				('email_enable_attachments', 3, 70, 'checkbox', '', 'a:1:{s:7:\"enabled\";s:7:\"Enabled\";}', 'none', '', '', '', 0, 0, 0)
			");
		}

		

		
		
				
		// add the default value column to kmkform__field
		$table = $this->describeTable('kmkform__field');
		
		// check to see if default_value exists
		if (!array_key_exists('default_value', $table))
		{
			$db->query("ALTER TABLE `kmkform__field` ADD COLUMN `default_value` MEDIUMTEXT;");
		}
		
		// add the min length column to kmkform__field
		if (!array_key_exists('min_length', $table))
		{
			$db->query("ALTER TABLE `kmkform__field` ADD COLUMN `min_length` int;");
		}
		
		// add redirect method to kmkform__destination
		$table = $this->describeTable('kmkform__destination');
		if (!array_key_exists('redirect_method', $table))
		{
			$db->query("ALTER TABLE `kmkform__destination` ADD COLUMN `redirect_method` varchar(35);");
		}
		
		// set redirect methods
		$db->query("UPDATE `kmkform__destination` SET `redirect_method` = 'redirect' WHERE `name` IN ('Thread', 'Post');");
		
		// add redirect_method column to kmkform__form
		$table = $this->describeTable('kmkform__form');
		if (!array_key_exists('redirect_method', $table))
		{
			$db->query("ALTER TABLE `kmkform__form` ADD COLUMN `redirect_method` ENUM('url', 'destination') NOT NULL DEFAULT 'url' AFTER `complete_Message`;");
		}
		
		// rename complete_url to redirect_url in kmkform__form
		if (!array_key_exists('redirect_url', $table))
		{
			$db->query("ALTER TABLE `kmkform__form` CHANGE `complete_url` `redirect_url` varchar(250) NOT NULL;");
		}
		
		// add redirect_destination to kmkform__form
		if (!array_key_exists('redirect_destination', $table))
		{
			$db->query("ALTER TABLE `kmkform__form` ADD COLUMN `redirect_destination` int(10) unsigned AFTER `redirect_url`;");
		}
		
		$db->query("
			ALTER TABLE `kmkform__form`
			ADD CONSTRAINT `fk_kmkform__form_kmkform__destination1` FOREIGN KEY (`redirect_destination`) REFERENCES `kmkform__destination` (`destination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
		");
		
		return true;
	}
}