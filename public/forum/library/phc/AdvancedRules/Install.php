<?php
// Team NullXF

class phc_AdvancedRules_Install
{
	public static function Install()
	{
		$db = XenForo_Application::get('db');

        $db->query("
                CREATE TABLE IF NOT EXISTS `phc_advanced_rules` (
                  `ar_id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` varchar(255) DEFAULT NULL,
                  `text` longtext,
                  `time` smallint(6) NOT NULL DEFAULT '10',
                  `actions` blob NOT NULL,
                  `node_ids` blob NOT NULL,
                  `group_ids` blob NOT NULL,
                  `active` tinyint(1) NOT NULL DEFAULT '1',
                  PRIMARY KEY (`ar_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");

        $db->query("
                CREATE TABLE IF NOT EXISTS `phc_advanced_rules_accepted` (
                  `user_id` int(11) NOT NULL,
                  `ar_id` int(11) NOT NULL,
                  `dateline` int(11) NOT NULL,
                  KEY `user_id` (`user_id`,`ar_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
	}

	public static function Uninstall()
	{
		$db = XenForo_Application::get('db');

        $db->query("DROP TABLE `phc_advanced_rules`");
        $db->query("DROP TABLE `phc_advanced_rules_accepted`");
	}
}