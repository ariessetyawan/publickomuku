<?php

class KomuKu_SimpleForms_Install_21600 extends KomuKu_SimpleForms_Install_Abstract
{
    public function install(&$db)
    {
        // add kmkform__page
        $db->query("
			CREATE TABLE IF NOT EXISTS `kmkform__page` (
			  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `form_id` int(10) unsigned NOT NULL,
              `page_number` tinyint(3) unsigned NOT NULL,
              `title` varchar(50) NOT NULL,
              `description` mediumtext,
			  PRIMARY KEY (`page_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
		");
        
        // populate each form with a single page
        $pageCount = $db->fetchOne('SELECT COUNT(*) FROM `kmkform__page`');
        if ($pageCount == 0)
        {
            $db->query("
                INSERT INTO `kmkform__page` (
                    `form_id`,
                    `page_number`,
                    `title`
                )
    			SELECT
    				`form_id`
                    ,1
                    ,''
    			FROM `kmkform__form`
            ");
        }
        
        // add kmkform__field.page_id column
        $table = $this->describeTable('kmkform__field');
        if (!array_key_exists('page_id', $table))
        {        
            $db->query("ALTER TABLE `kmkform__field` ADD COLUMN `page_id` int(10) unsigned;");
        }
        
        // populate the kmkform__field.page_id column
        $db->query("UPDATE `kmkform__field` SET `page_id` = (SELECT `page_id` FROM `kmkform__page` WHERE `kmkform__page`.`form_id` = `kmkform__field`.`form_id`)");
        
        // add kmkform__form.display_order column
        $table = $this->describeTable('kmkform__form');
        if (!array_key_exists('display_order', $table))
        {
            $db->query("ALTER TABLE `kmkform__form` ADD COLUMN `display_order` int(10) unsigned NOT NULL DEFAULT '1';");
        }

        // populate kmkform__form.display_order
        /* @var $formModel KomuKu_SimpleForms_Model_Form */
        $formModel = XenForo_Model::create('KomuKu_SimpleForms_Model_Form');
        
        $displayOrder = array();
        $displayOrderCount = 1;
        $forms = $formModel->getForms();
        foreach ($forms as $formId => $form)
        {
            $displayOrder[$displayOrderCount++] = $formId;
        }
        $formModel->massUpdateDisplayOrder($displayOrder);

        return true;
    }
}