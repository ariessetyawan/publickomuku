<?php /*0c30d928b5074ea5729505e56a97cde6a54274ca*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ModeratorLogHandler_TraderRating extends XenForo_ModeratorLogHandler_Abstract
{
    protected function _log(array $logUser, array $content, $action, array $actionParams = array(), $parentContent = null)
    {
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_ModeratorLog');

        $writer->bulkSet(array(
            'user_id' => $logUser['user_id'],
            'content_type' => 'classified_trader_rating',
            'content_id' => $content['feedback_id'],
            'content_user_id' => $content['user_id'],
            'content_username' => $content['username'],
            'content_title' => json_encode(array('by' => $content['username'], 'for' => $content['for_username'])),
            'content_url' => XenForo_Link::buildPublicLink('classifieds/traders/ratings', $content),
            'discussion_content_type' => 'classified_trader_rating',
            'discussion_content_id' => $content['feedback_id'],
            'action' => $action,
            'action_params' => $actionParams
        ));

        $writer->save();
        return $writer->get('moderator_log_id');
    }

    protected function _prepareEntry(array $entry)
    {
        $contentType = json_decode($entry['content_title'], true);
        $elements = json_decode($entry['action_params'], true);

        if ($entry['action'] == 'edit')
        {
            $entry['actionText'] = new XenForo_Phrase(
                'moderator_log_classified_trader_rating_edit',
                array('elements' => implode(', ', array_keys($elements)))
            );
        }

        $entry['content_title'] = new XenForo_Phrase('trader_rating_by_x_for_y', array('by' => $contentType['by'], 'for' => $contentType['for']));

        return $entry;
    }
}