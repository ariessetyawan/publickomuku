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
class KomuKuYJB_Installer_Data_1000031 extends GFNCore_Installer_Data_Abstract
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
        $handler = new GFNCore_Installer_Handler_ContentType('KomuKuYJB');

        // Comments...
        $handler->add('classified_comment', $handler::ALERT_HANDLER, 'KomuKuYJB_AlertHandler_Comment');
        $handler->add('classified_comment', $handler::LIKE_HANDLER, 'KomuKuYJB_LikeHandler_Comment');
        $handler->add('classified_comment', $handler::MODERATION_QUEUE_HANDLER, 'KomuKuYJB_ModerationQueueHandler_Comment');
        $handler->add('classified_comment', $handler::MODERATOR_LOG_HANDLER, 'KomuKuYJB_ModeratorLogHandler_Comment');
        $handler->add('classified_comment', $handler::NEWS_FEED_HANDLER, 'KomuKuYJB_NewsFeedHandler_Comment');
        $handler->add('classified_comment', $handler::REPORT_HANDLER, 'KomuKuYJB_ReportHandler_Comment');
        $handler->add('classified_comment', $handler::SPAM_HANDLER, 'KomuKuYJB_SpamHandler_Comment');
        $handler->add('classified_comment', $handler::WARNING_HANDLER, 'KomuKuYJB_WarningHandler_Comment');
    }
}