<?php /*6b3035cfce18832047a67ee8d8b49ca53bd9f0a8*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Installer_Data_1000012 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $this->table()->alter('kmk_classifieds_classified', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->integer('last_comment_id')->unsigned(true)->after('last_update')->default(0);
                $table->integer('last_comment_user_id')->unsigned(true)->after('last_comment_date')->default(0);
                $table->string('last_comment_username', 100)->after('last_comment_user_id')->default('');
            });

            $this->table()->alter('kmk_classifieds_comment', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->integer('reply_count')->unsigned(true)->default(0)->after('reply_parent_comment_id');
                $table->integer('likes')->unsigned(true)->default(0);
                $table->blob('like_users');

                $table->dropIndex('reply_comment_id');
                $table->index('reply_parent_comment_id');
            });

            $this->table()->alter('kmk_classifieds_review', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->integer('likes')->unsigned(true)->default(0);
                $table->blob('like_users');
            });
        }

        $this->_insertContentTypes();
    }

    public function uninstall()
    {
        // Nothing! :D
    }

    protected function _insertContentTypes()
    {
        $handler = new GFNCore_Installer_Handler_ContentType('GFNClassifieds');

        // Comments...
        $handler->add('classified_comment', $handler::ALERT_HANDLER, 'GFNClassifieds_AlertHandler_Comment');
        $handler->add('classified_comment', $handler::LIKE_HANDLER, 'GFNClassifieds_LikeHandler_Comment');
        $handler->add('classified_comment', $handler::MODERATION_QUEUE_HANDLER, 'GFNClassifieds_ModerationQueueHandler_Comment');
        $handler->add('classified_comment', $handler::MODERATOR_LOG_HANDLER, 'GFNClassifieds_ModeratorLogHandler_Comment');
        $handler->add('classified_comment', $handler::NEWS_FEED_HANDLER, 'GFNClassifieds_NewsFeedHandler_Comment');
        $handler->add('classified_comment', $handler::REPORT_HANDLER, 'GFNClassifieds_ReportHandler_Comment');
        $handler->add('classified_comment', $handler::SPAM_HANDLER, 'GFNClassifieds_SpamHandler_Comment');
        $handler->add('classified_comment', $handler::WARNING_HANDLER, 'GFNClassifieds_WarningHandler_Comment');
    }
}