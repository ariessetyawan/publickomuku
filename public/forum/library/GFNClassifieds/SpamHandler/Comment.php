<?php /*e392731782cda10e43452626662f32484fbeef11*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_SpamHandler_Comment extends XenForo_SpamHandler_Abstract
{
    public function cleanUpConditionCheck(array $user, array $options)
    {
        return !empty($options['delete_messages']);
    }

    public function cleanUp(array $user, array &$log, &$errorKey)
    {
        /** @var GFNClassifieds_Model_Comment $model */
        $model = $this->getModelFromCache('GFNClassifieds_Model_Comment');

        if ($comments = $model->getComments(array('user_id' => $user['user_id'])))
        {
            $commentIds = array_keys($comments);

            $this->getModelFromCache('XenForo_Model_SpamPrevention')->submitSpamCommentData('classified_comment', $commentIds);

            $deleteType = (XenForo_Application::get('options')->spamMessageAction == 'delete' ? 'hard' : 'soft');

            $log['classified_comment'] = array(
                'deleteType' => $deleteType,
                'classifiedCommentIds' => $commentIds
            );

            foreach ($comments as $comment)
            {
                $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
                $writer->setExistingData($comment);

                if ($deleteType == 'soft')
                {
                    $writer->set('message_state', 'deleted');
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
            /** @var GFNClassifieds_Model_Comment $model */
            $model = $this->getModelFromCache('GFNClassifieds_Model_Comment');
            $comments = $model->getCommentsByIds($log['classifiedCommentIds']);
            foreach ($comments as $comment)
            {
                $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
                $writer->setExistingData($comment);
                $writer->set('message_state', 'visible');
                $writer->save();
            }
        }

        return true;
    }
}