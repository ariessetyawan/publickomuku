<?php

class KomuKu_SimpleForms_Install_21901 extends KomuKu_SimpleForms_Install_Abstract
{
    public function install(&$db)
    {
        $db->query("ALTER TABLE `kmkform__form` ADD COLUMN `header_html` mediumtext;");
        $db->query("ALTER TABLE `kmkform__form` ADD COLUMN `footer_html` mediumtext;");
        
        return true;
    }
}