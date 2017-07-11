<?php /*a3cbe6cfb71ecf95cfc26f085f960f215390e1d2*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_DataWriter_TraderRating extends XenForo_DataWriter
{
    const DATA_DELETE_REASON            = 'deleteReason';

    const OPTION_SET_IP_ADDRESS = 'setIpAddress';
    const OPTION_PUBLISH_FEED = 'publishFeed';

    protected $_updateCriterias = null;

    protected $_isFirstVisible = false;

    protected function _getDefaultOptions()
    {
        return array(
            self::OPTION_SET_IP_ADDRESS => true,
            self::OPTION_PUBLISH_FEED => true
        );
    }

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_rating_feedback' => array(
                'feedback_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'classified_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'username' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'maxLength' => 50
                ),
                'for_user_id' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'for_username' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'maxLength' => 50
                ),
                'rating' => array(
                    'type' => self::TYPE_INT,
                    'min' => -1,
                    'max' => 1
                ),
                'parent_feedback_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'message' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ip_id' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'feedback_date' => array(
                    'type' => self::TYPE_UINT,
                    'default' => XenForo_Application::$time
                ),
                'feedback_state' => array(
                    'type' => self::TYPE_STRING,
                    'default' => 'visible',
                    'allowedValues' => array('visible', 'moderated', 'deleted')
                ),
                'likes' => array(
                    'type' => self::TYPE_UINT_FORCED,
                    'default' => 0
                ),
                'like_users' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'had_first_visible' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'criteria_feedbacks' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $feedbackId = $this->_getExistingPrimaryKey($data);
        if (!$feedbackId)
        {
            return false;
        }

        $feedback = $this->_getTraderRatingModel()->getTraderRatingById($feedbackId);
        if (!$feedback)
        {
            return false;
        }

        return array('kmk_classifieds_rating_feedback' => $feedback);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'feedback_id = ' . $this->_db->quote($this->getExisting('feedback_id'));
    }

    protected function _preSave()
    {
        if ($this->get('feedback_state') === null)
        {
            $this->set('feedback_state', 'visible');
        }

        if (!$this->get('had_first_visible') && $this->get('feedback_state') == 'visible')
        {
            $this->set('had_first_visible', 1);
            $this->_isFirstVisible = true;
        }
    }

    protected function _postSave()
    {
        $postSaveChanges = array();

        $this->updateCriteriaFeedbacks();

        if ($this->isInsert() && $this->getOption(self::OPTION_SET_IP_ADDRESS) && !$this->get('ip_id'))
        {
            $postSaveChanges['ip_id'] = XenForo_Model_Ip::log($this->get('user_id'), 'classified_trader_rating', $this->get('feedback_id'), 'insert');
        }

        $this->_getTraderRatingModel()->recalculateTraderRatings($this->get('for_user_id'));

        if ($this->_isFirstVisible && $this->getOption(self::OPTION_PUBLISH_FEED))
        {
            GFNClassifieds_Model_NewsFeed::publish('trader_rating', $this->getMergedData());
        }

        $removed = false;
        if ($this->isChanged('feedback_state'))
        {
            $this->_updateDeletionLog();
            $this->_updateModerationQueue();
        }

        if ($postSaveChanges)
        {
            $this->bulkSet($postSaveChanges, array('setAfterPreSave' => true));
            $this->_db->update('kmk_classifieds_rating_feedback', $postSaveChanges, 'feedback_id = ' . $this->_db->quote($this->get('feedback_id')));
        }
    }

    protected function _postSaveAfterTransaction()
    {
        if ($this->_isFirstVisible)
        {
            $this->_getTraderRatingModel()->sendNotificationToReviewee($this->getMergedData());
        }
    }

    protected function _postDelete()
    {
        $this->_getTraderRatingModel()->recalculateTraderRatings($this->get('for_user_id'));

        $this->_updateDeletionLog(true);
        $this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
            'classified_trader_rating', $this->get('feedback_id')
        );
    }

    protected function _updateDeletionLog($hardDelete = false)
    {
        /** @var XenForo_Model_DeletionLog $model */
        $model = $this->getModelFromCache('XenForo_Model_DeletionLog');

        if ($hardDelete || ($this->isChanged('feedback_state') && $this->getExisting('feedback_state') == 'deleted'))
        {
            $model->removeDeletionLog('classified_trader_rating', $this->get('feedback_id'));
        }

        if ($this->isChanged('feedback_state') && $this->get('feedback_state') == 'deleted')
        {
            $reason = $this->getExtraData(self::DATA_DELETE_REASON);
            $model->logDeletion('classified_trader_rating', $this->get('feedback_id'), $reason);
        }
    }

    protected function _updateModerationQueue()
    {
        if (!$this->isChanged('message_state'))
        {
            return;
        }

        /** @var XenForo_Model_ModerationQueue $model */
        $model = $this->getModelFromCache('XenForo_Model_ModerationQueue');

        if ($this->get('feedback_state') == 'moderated')
        {
            $model->insertIntoModerationQueue('classified_trader_rating', $this->get('feedback_id'), $this->get('feedback_date'));
        }
        elseif ($this->getExisting('feedback_state') == 'moderated')
        {
            $model->deleteFromModerationQueue('classified_trader_rating', $this->get('feedback_id'));
        }
    }

    /**
     * @return GFNClassifieds_Model_TraderRatingCriteria
     */
    protected function _getRatingCriteriaModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_TraderRatingCriteria');
    }

    /**
     * @return GFNClassifieds_Model_TraderRating
     */
    protected function _getTraderRatingModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_TraderRating');
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Classified');
    }

    public function setRatingCriteria(array $criteriaFeedbacks, array $criteriasShown = null)
    {
        $criteriaModel = $this->_getRatingCriteriaModel();
        $criterias = $criteriaModel->getRatingCriteriasByClassifiedForEdit($this->get('classified_id'));

        if (!is_array($criteriasShown))
        {
            $criteriasShown = array_keys($criterias);
        }

        if ($this->get('feedback_id') && !$this->_importMode)
        {
            $existingFeedbacks = $criteriaModel->getRatingCriteriaFeedbacks($this->get('feedback_id'));
        }
        else
        {
            $existingFeedbacks = array();
        }

        $finalFeedbacks = array();

        foreach ($criteriasShown as $criteriaId)
        {
            if (!isset($criterias[$criteriaId]))
            {
                continue;
            }

            $criteria = $criterias[$criteriaId];

            if (isset($criteriaFeedbacks[$criteriaId]))
            {
                $feedback = $criteriaFeedbacks[$criteriaId];

                if (!isset($feedback['message']))
                {
                    $feedback['message'] = '';
                }

                if (!isset($feedback['rating']))
                {
                    $feedback = null;
                }
            }
            else
            {
                $feedback = null;
            }

            $existingFeedback = isset($existingFeedbacks[$criteriaId]) ? $existingFeedbacks[$criteriaId] : array();

            if (!$this->_importMode)
            {
                if (!$feedback)
                {
                    if ($criteria['required'])
                    {
                        $this->error(new XenForo_Phrase('please_fill_in_all_required_criteria'), 'required');
                    }

                    continue;
                }

                if ($criteria['require_message'] && empty($feedback['message']))
                {
                    $this->error(new XenForo_Phrase('please_post_comment_for_rating'), "criteria_review_$criteriaId");
                    continue;
                }

                if (!$criteria['show_message'])
                {
                    $feedback['message'] = '';
                }

                $feedback['rating'] = max(-1, min(1, $feedback['rating']));
            }

            if ($feedback !== $existingFeedback)
            {
                $finalFeedbacks[$criteriaId] = $feedback;
            }
        }

        $this->_updateCriterias = $this->_filterValidCriteria($finalFeedbacks + $existingFeedbacks, $criterias);
        $this->set('criteria_feedbacks', $this->_updateCriterias);
    }

    protected function _filterValidCriteria(array $feedbacks, array $criterias)
    {
        $newFeedbacks = array();

        foreach ($criterias as $criteria)
        {
            if (isset($feedbacks[$criteria['criteria_id']]))
            {
                $newFeedbacks[$criteria['criteria_id']] = $feedbacks[$criteria['criteria_id']];
            }
        }

        return $newFeedbacks;
    }

    public function updateCriteriaFeedbacks()
    {
        if ($this->_updateCriterias)
        {
            $feedbackId = $this->get('feedback_id');
            $this->_db->query('DELETE FROM kmk_classifieds_rating_criteria_feedback WHERE feedback_id = ?', $feedbackId);

            foreach ($this->_updateCriterias as $criteriaId => $feedback)
            {
                $this->_db->query(
                    'INSERT INTO kmk_classifieds_rating_criteria_feedback
                      (feedback_id, criteria_id, rating, message)
                    VALUES
                      (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                      rating = VALUES(rating),
                      message = VALUES(message)', array($feedbackId, $criteriaId, $feedback['rating'], $feedback['message'])
                );
            }
        }
    }
}