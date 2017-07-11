<?php

class KomuKu_SimpleForms_Install_21400 extends KomuKu_SimpleForms_Install_Abstract
{
	public function install(&$db)
	{
		// add Rating to kmkform__field.field_type
		$db->query("ALTER TABLE `kmkform__field` MODIFY COLUMN `field_type` ENUM('textbox','textarea','select','radio','checkbox','multiselect','wysiwyg','date','rating');");
		$db->query("ALTER TABLE `kmkform__response` CHANGE `ip_address` `ip_address` VARCHAR(45) NOT NULL;");
		
		return true;
	}
}