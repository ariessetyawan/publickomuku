<?php /*e180b5d7263d1f5aaa9290b98bcde2ed827de805*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ModeratorLogHandler_Comment extends XenForo_ModeratorLogHandler_Abstract
{
    protected $_skipLogSelfActions = array(
        'edit'
    );

    protected function _log(array $logUser, array $content, $action, array $actionParams = array(), $parentContent = null)
    {
        $contentTitle = $content['username'];
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_ModeratorLog');

        $writer->bulkSet(array(
            'user_id' => $logUser['user_id'],
            'content_type' => 'classified_comment',
            'content_id' => $content['comment_id'],
            'content_user_id' => $content['user_id'],
            'content_username' => $content['username'],
            'content_title' => $contentTitle,
            'content_url' => XenForo_Link::buildPublicLink('classifieds/comments', $content),
            'discussion_content_type' => 'classified',
            'discussion_content_id' => $content['classified_id'],
            'action' => $action,
            'action_params' => $actionParams
        ));

        $writer->save();
        return $writer->get('moderator_log_id');
    }

    protected function _prepareEntry(array $entry)
    {
        $entry['content_title'] = new XenForo_Phrase('classified_comment_by_x', array('username' => $entry['content_title']));

        return $entry;
    }
}