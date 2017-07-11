<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_Install
{
    //Add the custom stuff in the db.
    public static function install()
    {
        //Get the db.
        $db = XenForo_Application::getDb();
        XenForo_Db::beginTransaction($db);

        $db->query('
			CREATE TABLE IF NOT EXISTS kmk_user_ratings (
  				`rating_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  				`from_user_id` INT(10) UNSIGNED NOT NULL,
  				`to_user_id` INT(10) UNSIGNED NOT NULL,
  				`from_username` VARCHAR(50) NOT NULL,
  				`to_username` VARCHAR(50) NOT NULL,
				`rating` TINYINT(1) NOT NULL,
  				`message` TEXT NOT NULL,
				`active` TINYINT(1) NOT NULL DEFAULT \'1\',
  				`rating_date` INT(10) unsigned NOT NULL,
  				PRIMARY KEY (`rating_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		');

        $db->query('
			CREATE TABLE IF NOT EXISTS kmk_user_ratings_stats (
				`user_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`positive` SMALLINT(10) UNSIGNED NOT NULL,
				`neutral` SMALLINT(10) UNSIGNED NOT NULL,
				`negative` SMALLINT(10) UNSIGNED unsigned NOT NULL,
				`total` SMALLINT(10) NOT NULL,
				`rating` TINYINT(5) unsigned NOT NULL,
				PRIMARY KEY (`user_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		');

        //Insert our field in the kmk_content_type table.
        try {
            $db->query("
			        INSERT INTO kmk_content_type
				      (content_type, addon_id, fields)
			        VALUES
				       ('ratings', 'ThemeHouse_UserRatings', '')
		        ");
        } catch (Zend_Db_Exception $e) {
        }

        //Insert our field in the kmk_content_type_field table.
        try {
            $db->query("
			        INSERT INTO kmk_content_type_field
				        (content_type, field_name, field_value)
			        VALUES
				        ('ratings', 'alert_handler_class', 'ThemeHouse_UserRatings_AlertHandler_Ratings'),
			            ('ratings', 'like_handler_class', 'ThemeHouse_UserRatings_LikeHandler_Ratings'),
                        ('ratings', 'moderator_log_handler_class', 'ThemeHouse_UserRatings_ModeratorLogHandler_Ratings'),
                        ('ratings', 'moderation_queue_handler_class', 'ThemeHouse_UserRatings_ModerationQueueHandler_Ratings'),
		                ('ratings', 'news_feed_handler_class', 'ThemeHouse_UserRatings_NewsFeedHandler_Ratings'),
			            ('ratings', 'report_handler_class', 'ThemeHouse_UserRatings_ReportHandler_Ratings')
		        ");
        } catch (Zend_Db_Exception $e) {
        }

		// Fix totals not being able to go negative`
		try {
			$db->query('
				ALTER TABLE `kmk_user_ratings_stats` CHANGE `total` `total` SMALLINT(10) NOT NULL
			');
        } catch (Zend_Db_Exception $e) {
        }

        XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

        XenForo_Db::commit($db);

        $addOn = XenForo_Model::create('XenForo_Model_AddOn')->getAddOnById('Borbole_UserRatings');

        if ($addOn) {
            $dw = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
            $dw->setExistingData('Borbole_UserRatings');
            $dw->set('install_callback_class', '');
            $dw->set('install_callback_method', '');
            $dw->set('uninstall_callback_class', '');
            $dw->set('uninstall_callback_method', '');
            $dw->delete();
        }
    }

    //Drop the custom stuff from the db.
    public static function uninstall()
    {
        //Get the db.
        $db = XenForo_Application::getDb();
        XenForo_Db::beginTransaction($db);

        //Drop the custom tables from the db.
        try {
            $db->query('
			    DROP TABLE IF EXISTS `kmk_user_ratings`, `kmk_user_ratings_stats`
		   ');
        } catch (Zend_Db_Exception $e) {
        }

        //Remove our fields from the kmk_content_type table.
        try {
            $db->query("DELETE FROM kmk_content_type WHERE content_type = 'ratings'");
        } catch (Zend_Db_Exception $e) {
        }

        //Remove our fields from the kmk_content_type_field table.
        try {
            $db->query("DELETE FROM kmk_content_type_field WHERE content_type = 'ratings'");
        } catch (Zend_Db_Exception $e) {
        }

        XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();

        XenForo_Db::commit($db);
    }
}
