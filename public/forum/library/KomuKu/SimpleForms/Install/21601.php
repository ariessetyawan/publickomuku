<?php

class KomuKu_SimpleForms_Install_21601 extends KomuKu_SimpleForms_Install_Abstract
{
    public function install(&$db)
    {
        // delete orphaned kmkform__response_field rows
        $db->query("
			DELETE FROM `kmkform__response_field`
            WHERE `response_id` NOT IN (
                SELECT
                    `response_id`
                FROM `kmkform__response`
            )
		");
        
        return true;
    }
}