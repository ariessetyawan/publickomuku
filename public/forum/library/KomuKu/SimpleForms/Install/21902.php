<?php

class KomuKu_SimpleForms_Install_21902 extends KomuKu_SimpleForms_Install_Abstract
{
    public function install(&$db)
    {
        // add kmkform__form.header_html column
        $table = $this->describeTable('kmkform__form');
        if (!array_key_exists('header_html', $table))
        {
            $db->query("ALTER TABLE `kmkform__form` ADD COLUMN `header_html` mediumtext;");
        }        
        
        // add kmkform__form.header_html column
        if (!array_key_exists('footer_html', $table))
        {
            $db->query("ALTER TABLE `kmkform__form` ADD COLUMN `footer_html` mediumtext;");
        }
        
        return true;
    }
}