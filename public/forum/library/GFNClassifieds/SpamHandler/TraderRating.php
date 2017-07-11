<?php /*64817fc6c737e3d08961e2377c680c9d5d6b6e10*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_SpamHandler_TraderRating extends XenForo_SpamHandler_Abstract
{
    public function cleanUpConditionCheck(array $user, array $options)
    {
        return !empty($options['delete_messages']);
    }

    public function cleanUp(array $user, array &$log, &$errorKey)
    {
        /** @var GFNClassifieds_Model_TraderRating $model */
        $model = $this->getModelFromCache('GFNClassifieds_Model_TraderRating');

        if ($ratings = $model->getTraderRatings(array('user_id' => $user['user_id'])))
        {
            $feedbackIds = array_keys($ratings);

            $this->getModelFromCache('XenForo_Model_SpamPrevention')->submitSpamCommentData('classified_trader_rating', $feedbackIds);

            $deleteType = (XenForo_Application::get('options')->spamMessageAction == 'delete' ? 'hard' : 'soft');

            $log['classified_trader_rating'] = array(
                'deleteType' => $deleteType,
                'ratingFeedbackIds' => $feedbackIds
            );

            foreach ($ratings as $rating)
            {
                $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRating', XenForo_DataWriter::ERROR_SILENT);
                $writer->setExistingData($rating);

                if ($deleteType == 'soft')
                {
                    $writer->set('feedback_state', 'deleted');
                    $writer->save();
                }
                else
                {
                    $writer->delete();
                }
            }
        }

        return true;
    }

    public function restore(array $log, &$errorKey = '')
    {
        if ($log['deleteType'] == 'soft')
        {
            /** @var GFNClassifieds_Model_TraderRating $model */
            $model = $this->getModelFromCache('GFNClassifieds_Model_TraderRating');
            $ratings = $model->getTraderRatingsByIds($log['ratingFeedbackIds']);
            foreach ($ratings as $rating)
            {
                $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRating', XenForo_DataWriter::ERROR_SILENT);
                $writer->setExistingData($rating);
                $writer->set('feedback_state', 'visible');
                $writer->save();
            }
        }

        return true;
    }
}