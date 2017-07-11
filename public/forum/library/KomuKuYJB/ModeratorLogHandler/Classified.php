<?php /*602bf0c13fd46c70e7b8a9b9eb27a8645c8e9dfe*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ModeratorLogHandler_Classified extends XenForo_ModeratorLogHandler_Abstract
{
    protected function _log(array $logUser, array $content, $action, array $actionParams = array(), $parentContent = null)
    {
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_ModeratorLog');

        $writer->bulkSet(array(
            'user_id' => $logUser['user_id'],
            'content_type' => 'classified',
            'content_id' => $content['classified_id'],
            'content_user_id' => $content['user_id'],
            'content_username' => $content['username'],
            'content_title' => $content['title'],
            'content_url' => XenForo_Link::buildPublicLink('classifieds', $content),
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
        $elements = json_decode($entry['action_params'], true);

        if ($entry['action'] == 'edit')
        {
            $entry['actionText'] = new XenForo_Phrase(
                'moderator_log_classified_edit',
                array('elements' => implode(', ', array_keys($elements)))
            );
        }

        return $entry;
    }
}