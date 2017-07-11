<?php

class KomuKu_SimpleForms_Install_21900 extends KomuKu_SimpleForms_Install_Abstract
{
    public function install(&$db)
    {
        $db->query("ALTER TABLE `kmkform__field` MODIFY COLUMN `field_type` ENUM('textbox','textarea','select','radio','checkbox','multiselect','wysiwyg','date','rating','datetime','time');");
        
        return true;
    }
}