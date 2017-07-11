<?php /*1de240dcc40b76874b02fcbfbddf6e8392e345cf*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Installer_Data_1000031 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade && !$this->_db->fetchOne('SELECT COUNT(*) from kmk_content_type WHERE content_type = \'classified_comment\''))
        {
            $this->_insertContentTypes();
        }
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