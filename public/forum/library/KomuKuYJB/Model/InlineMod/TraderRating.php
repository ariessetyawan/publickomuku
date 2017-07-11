<?php /*6a24e6043979aa8c14e283018e9bb0c6bc97c12d*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_InlineMod_TraderRating extends XenForo_Model
{
    public $enableLogging = true;

    public function approveTraderRatings(array $feedbackIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $ratings = $this->getTraderRatings($feedbackIds);

        if (empty($options['skipPermissions']) && !$this->canApproveTraderRatingsData($ratings, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateTraderRatingsMessageState($ratings, 'visible', 'moderated');
        return true;
    }

    public function canApproveTraderRatingsData(array $ratings, &$errorKey = '', array $viewingUser = null)
    {
        if (!$ratings)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $traderRatingModel = $this->_getTraderRatingModel();

        foreach ($ratings as $rating)
        {
            if ($rating['feedback_state'] == 'moderated' && !$traderRatingModel->canApproveTraderRating($rating, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function unapproveTraderRatings(array $feedbackIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $ratings = $this->getTraderRatings($feedbackIds);

        if (empty($options['skipPermissions']) && !$this->canUnapproveTraderRatingsData($ratings, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateTraderRatingsMessageState($ratings, 'moderated', 'visible');
        return true;
    }

    public function canUnapproveTraderRatingsData(array $ratings, &$errorKey = '', array $viewingUser = null)
    {
        if (empty($ratings))
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $traderRatingModel = $this->_getTraderRatingModel();

        foreach ($ratings as $rating)
        {
            if ($rating['feedback_state'] == 'visible' && !$traderRatingModel->canUnapproveTraderRating($rating, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function undeleteTraderRatings(array $feedbackIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $ratings = $this->getTraderRatings($feedbackIds);

        if (empty($options['skipPermissions']) && !$this->canUndeleteTraderRatingsData($ratings, $errorKey, $viewingUser))
        {
            return false;
        }

        $this->_updateTraderRatingsMessageState($ratings, 'visible', 'deleted');
        return true;
    }

    public function canUndeleteTraderRatingsData(array $ratings, &$errorKey = '', array $viewingUser = null)
    {
        if (empty($ratings))
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $traderRatingModel = $this->_getTraderRatingModel();

        foreach ($ratings as $rating)
        {
            if ($rating['feedback_state'] == 'deleted' && !$traderRatingModel->canUndeleteTraderRating($rating, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function deleteTraderRatings(array $feedbackIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
    {
        $options = array_merge(
            array(
                'deleteType' => '',
                'reason' => ''
            ), $options
        );

        if (!$options['deleteType'])
        {
            throw new XenForo_Exception('No deletion type specified.');
        }

        $ratings = $this->getTraderRatings($feedbackIds);

        if (empty($options['skipPermissions']) && !$this->canDeleteTraderRatingsData($ratings, $options['deleteType'], $errorKey, $viewingUser))
        {
            return false;
        }

        foreach ($ratings as $rating)
        {
            /** @var KomuKuYJB_DataWriter_TraderRating $writer */
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_TraderRating', XenForo_DataWriter::ERROR_SILENT);
            $writer->setExistingData($rating);

            if (!$writer->get('feedback_id'))
            {
                continue;
            }

            if ($options['deleteType'] == 'hard')
            {
                $writer->delete();
            }
            else
            {
                $writer->setExtraData($writer::DATA_DELETE_REASON, $options['reason']);
                $writer->set('feedback_state', 'deleted');
                $writer->save();
            }

            if ($this->enableLogging)
            {
                XenForo_Model_Log::logModeratorAction(
                    'classified_trader_rating', $rating, 'delete_' . $options['deleteType'], array('reason' => $options['reason'])
                );
            }
        }

        return true;
    }

    public function canDeleteTraderRatings(array $feedbackIds, $deleteType, &$errorKey = '', array $viewingUser = null)
    {
        $ratings = $this->getTraderRatings($feedbackIds);
        return $this->canDeleteTraderRatingsData($ratings, $deleteType, $errorKey, $viewingUser);
    }

    public function canDeleteTraderRatingsData(array $ratings, $deleteType, &$errorKey = '', array $viewingUser = null)
    {
        if (!$ratings)
        {
            return true;
        }

        $this->standardizeViewingUserReference($viewingUser);
        $traderRatingModel = $this->_getTraderRatingModel();

        foreach ($ratings as $rating)
        {
            if ($rating['feedback_state'] == 'deleted' && !$traderRatingModel->canDeleteTraderRating($rating, $deleteType, $errorKey, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function getTraderRatings(array $feedbackIds)
    {
        return $this->_getTraderRatingModel()->getTraderRatingsByIds($feedbackIds);
    }

    protected function _updateTraderRatingsMessageState(array $ratings, $newState, $expectedOldState = false)
    {
        switch ($newState)
        {
            case 'visible':
                switch (strval($expectedOldState))
                {
                    case 'visible': return;
                    case 'moderated': $logAction = 'approve'; break;
                    case 'deleted': $logAction = 'undelete'; break;
                    default: $logAction = 'undelete'; break;
                }
                break;

            case 'moderated':
                switch (strval($expectedOldState))
                {
                    case 'visible': $logAction = 'unapprove'; break;
                    case 'moderated': return;
                    case 'deleted': $logAction = 'unapprove'; break;
                    default: $logAction = 'unapprove'; break;
                }
                break;

            case 'deleted':
                switch (strval($expectedOldState))
                {
                    case 'visible': $logAction = 'delete_soft'; break;
                    case 'moderated': $logAction = 'delete_soft'; break;
                    case 'deleted': return;
                    default: $logAction = 'delete_soft'; break;
                }
                break;

            default: return;
        }

        foreach ($ratings as $rating)
        {
            if ($expectedOldState && $rating['feedback_state'] != $expectedOldState)
            {
                continue;
            }

            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_TraderRating', XenForo_DataWriter::ERROR_SILENT);
            $writer->setExistingData($rating);
            $writer->set('feedback_state', $newState);
            $writer->save();

            if ($this->enableLogging)
            {
                XenForo_Model_Log::logModeratorAction('classified_trader_rating', $rating, $logAction);
            }
        }
    }

    /**
     * @return KomuKuYJB_Model_TraderRating
     */
    protected function _getTraderRatingModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_TraderRating');
    }
}