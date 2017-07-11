<?php /*0ffe85056b813d742d90a907356b1ef78d8026bf*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ModerationQueueHandler_TraderRating extends XenForo_ModerationQueueHandler_Abstract
{
    public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
    {
        /** @var KomuKuYJB_Model_TraderRating $model */
        $model = XenForo_Model::create('KomuKuYJB_Model_TraderRating');

        $ratings = $model->getTraderRatingsByIds($contentIds, array(
            'join' => $model::FETCH_USER
        ));

        $return = array();

        foreach ($ratings as $rating)
        {
            $canManage = true;

            if (!$model->canViewTraderRating($rating, $null, $viewingUser))
            {
                $canManage = false;
            }
            elseif (!$model->hasPermission('editAny') || !$model->hasPermission('deleteAny'))
            {
                $canManage = false;
            }

            if ($canManage)
            {
                $return[$rating['feedback_id']] = array(
                    'message' => $rating['message'],
                    'user' => array(
                        'user_id' => $rating['user_id'],
                        'username' => $rating['username']
                    ),
                    'title' => new XenForo_Phrase('trader_rating_by_x_for_y', array('by' => $rating['username'], 'for' => $rating['for_username'])),
                    'link' => XenForo_Link::buildPublicLink('classifieds/traders/ratings', $rating),
                    'contentTypeTitle' => new XenForo_Phrase('trader_rating'),
                    'titleEdit' => false
                );
            }
        }

        return $return;
    }

    public function approveModerationQueueEntry($contentId, $message, $title)
    {
        $message = XenForo_Helper_String::autoLinkBbCode($message);

        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_TraderRating', XenForo_DataWriter::ERROR_SILENT);
        $writer->setExistingData($contentId);
        $writer->set('feedback_state', 'visible');
        $writer->set('message', $message);

        if ($writer->save())
        {
            XenForo_Model_Log::logModeratorAction('classified_trader_rating', $writer->getMergedData(), 'approve');
            return true;
        }

        return false;
    }

    public function deleteModerationQueueEntry($contentId)
    {
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_TraderRating', XenForo_DataWriter::ERROR_SILENT);
        $writer->setExistingData($contentId);
        $writer->set('feedback_state', 'deleted');

        if ($writer->save())
        {
            XenForo_Model_Log::logModeratorAction('classified_trader_rating', $writer->getMergedData(), 'delete_soft', array('reason' => ''));
            return true;
        }

        return false;
    }
}