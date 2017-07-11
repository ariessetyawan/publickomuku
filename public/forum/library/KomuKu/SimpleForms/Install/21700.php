<?php

class KomuKu_SimpleForms_Install_21700 extends KomuKu_SimpleForms_Install_Abstract
{
    public function install(&$db)
    {
        $db->query("UPDATE `kmkform__destination_option` SET `evaluate_template` = 1 WHERE `option_id` = 'thread_poll'");
        
        return true;
    }
}