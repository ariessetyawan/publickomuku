<?php /*8c581ee23512fceb1b2b9810c967de408238d27a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_TraderRating extends XenForo_Model
{
    const FETCH_USER            = 0x01;
    const FETCH_USER_OPTION     = 0x02;
    const FETCH_CLASSIFIED      = 0x04;
    const FETCH_CATEGORY        = 0x08;
    const FETCH_DELETION_LOG    = 0x10;

    public static $voteThreshold = 10;
    public static $averageVote = 3;

    public function getTraderRatingById($feedbackId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareTraderRatingFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            'SELECT feedback.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_rating_feedback AS feedback
            ' . $joinOptions['joinTables'] . '
            WHERE feedback_id = ?', $feedbackId
        );
    }

    public function getTraderRatings(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->prepareTraderRatingConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareTraderRatingFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT feedback.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_rating_feedback AS feedback
            ' . $joinOptions['joinTables'] . '
            WHERE' . $whereClause . '
            ORDER BY feedback_date DESC', $limitOptions['limit'], $limitOptions['offset']
        ), 'feedback_id');
    }

    public function getTraderRatingsByIds(array $feedbackIds, array $fetchOptions = array())
    {
        if (!$feedbackIds)
        {
            return array();
        }

        $joinOptions = $this->prepareTraderRatingFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            'SELECT feedback.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_rating_feedback AS feedback
            ' . $joinOptions['joinTables'] . '
            WHERE feedback.feedback_id IN (' . $this->_getDb()->quote($feedbackIds) . ')', 'feedback_id'
        );
    }

    public function getTraderRatingByParentFeedbackId($parentFeedbackId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareTraderRatingFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            'SELECT feedback.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_rating_feedback AS feedback
            ' . $joinOptions['joinTables'] . '
            WHERE feedback.parent_feedback_id = ?', $parentFeedbackId
        );
    }

    public function countTraderRatings(array $conditions)
    {
        $fetchOptions = array();

        $whereClause = $this->prepareTraderRatingConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareTraderRatingFetchOptions($fetchOptions);

        return $this->_getDb()->fetchOne(
            'SELECT COUNT(*)
            ' . 'FROM kmk_classifieds_rating_feedback AS feedback
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause
        );
    }

    public function prepareTraderRatingConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (isset($conditions['classified_id']))
        {
            if (is_array($conditions['classified_id']))
            {
                $sqlConditions[] = 'feedback.classified_id IN (' . $db->quote($conditions['classified_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'feedback.classified_id = ' . $db->quote($conditions['classified_id']);
            }
        }

        if (!empty($conditions['by_user_id']))
        {
            $conditions['user_id'] = $conditions['by_user_id'];
        }

        if (!empty($conditions['user_id']))
        {
            if (is_array($conditions['user_id']))
            {
                $sqlConditions[] = 'feedback.user_id IN (' . $db->quote($conditions['user_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'feedback.user_id = ' . $db->quote($conditions['user_id']);
            }
        }

        if (!empty($conditions['for_user_id']))
        {
            if (is_array($conditions['for_user_id']))
            {
                $sqlConditions[] = 'feedback.for_user_id IN (' . $db->quote($conditions['for_user_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'feedback.for_user_id = ' . $db->quote($conditions['for_user_id']);
            }
        }

        if (isset($conditions['hasParent']))
        {
            if ($conditions['hasParent'])
            {
                $sqlConditions[] = 'feedback.parent_feedback_id <> 0';
            }
            else
            {
                $sqlConditions[] = 'feedback.parent_feedback_id = 0';
            }
        }

        if (!empty($conditions['parent_feedback_id']))
        {
            if (is_array($conditions['parent_feedback_id']))
            {
                $sqlConditions[] = 'feedback.parent_feedback_id IN (' . $db->quote($conditions['parent_feedback_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'feedback.parent_feedback_id = ' . $db->quote($conditions);
            }
        }

        if (isset($conditions['visible']) || isset($conditions['moderated']) || isset($conditions['deleted']))
        {
            $sqlConditions[] = $this->prepareStateLimitFromConditions($conditions, 'feedback', 'feedback_state');
        }
        else
        {
            $sqlConditions[] = 'feedback.feedback_state = \'visible\'';
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareTraderRatingFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $db = $this->_getDb();

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_USER)
            {
                $selectFields .= ', user.*, user_profile.*, trader.*, IF(user.username IS NULL, feedback.username, user.username) AS username';
                $joinTables .= '
                    LEFT JOIN kmk_user AS user ON (user.user_id = feedback.user_id)
                    LEFT JOIN kmk_user_profile AS user_profile ON (user_profile.user_id = feedback.user_id)
                    LEFT JOIN kmk_classifieds_trader AS trader ON (trader.user_id = feedback.user_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_CATEGORY)
            {
                $this->addFetchOptionJoin($fetchOptions, self::FETCH_CLASSIFIED);
            }

            if ($fetchOptions['join'] & self::FETCH_CLASSIFIED)
            {
                $selectFields .= ', classified.*';
                $joinTables .= '
                    LEFT JOIN kmk_classifieds_classified ON (classified.classified_id = feedback.classified_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_CATEGORY)
            {
                $selectFields .= ', category.*';
                $joinTables .= '
                    LEFT JOIN kmk_classifieds_category ON (classified.category_id = category.category_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_DELETION_LOG)
            {
                $selectFields .= ', deletion_log.delete_date, deletion_log.delete_reason,
                    deletion_log.delete_user_id, deletion_log.delete_username';
                $joinTables .= '
                    LEFT JOIN kmk_deletion_log AS deletion_log
                        ON (deletion_log.content_type = \'classified_trader_rating\' AND deletion_log.content_id = feedback.feedback_id)';
            }
        }

        if (isset($fetchOptions['likeUserId']))
        {
            if (empty($fetchOptions['likeUserId']))
            {
                $selectFields .= ', 0 AS like_date';
            }
            else
            {
                $selectFields .= ', liked_content.like_date';
                $joinTables .= '
                    LEFT JOIN kmk_liked_content AS liked_content
                        ON (liked_content.content_type = \'classified_trader_rating\'
                            AND liked_content.content_id = feedback.feedback_id
                            AND liked_content.like_user_id = ' . $db->quote($fetchOptions['likeUserId']) . '
                        )';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    public function recalculateTraderRatings($userId = null)
    {
        if ($userId === null)
        {
            return;
        }

        $db = $this->_getDb();

        $ratings = $db->fetchCol('SELECT rating FROM kmk_classifieds_rating_feedback WHERE for_user_id = ? AND feedback_state = \'visible\'', $userId);
        $count = count($ratings);

        $average = $sum = $positive = $neutral = $negative = 0;

        if ($count)
        {
            foreach ($ratings as $rating)
            {
                switch (intval($rating))
                {
                    case 1:
                        $positive++;
                        break;
                    case 0:
                        $neutral++;
                        break;
                    case -1:
                        $negative++;
                        break;
                }
            }

            $sum = ($positive * 5) + ($neutral * 3) + ($negative * 1);
            $average = $sum / $count;
        }

        $bind = array(
            'user_id' => $userId,
            'rating_count' => $count,
            'rating_positive_count' => $positive,
            'rating_neutral_count' => $neutral,
            'rating_negative_count' => $negative,
            'rating_avg' => $average,
            'rating_weighted' => $this->getWeightedRating($count, $sum)
        );

        $db->query(
            'INSERT INTO kmk_classifieds_trader
              (user_id, classified_count, rating_count, rating_positive_count, rating_neutral_count, rating_negative_count, rating_avg, rating_weighted)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              rating_count = VALUES(rating_count),
              rating_positive_count = VALUES(rating_positive_count),
              rating_neutral_count = VALUES(rating_neutral_count),
              rating_negative_count = VALUES(rating_negative_count),
              rating_avg = VALUES(rating_avg),
              rating_weighted = VALUES(rating_weighted)', array(
                $bind['user_id'], 0, $bind['rating_count'], $bind['rating_positive_count'], $bind['rating_neutral_count'],
                $bind['rating_negative_count'], $bind['rating_avg'], $bind['rating_weighted']
            )
        );
    }

    public function getWeightedRating($count, $sum)
    {
        return (self::$voteThreshold * self::$averageVote + $sum) / (self::$voteThreshold + $count);
    }

    public function prepareTraderRating(array $rating, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $rating['isDeleted'] = $rating['feedback_state'] == 'deleted';
        $rating['isModerated'] = $rating['feedback_state'] == 'moderated';
        $rating['isTrusted'] = !empty($rating['user_id']) && (!empty($rating['is_admin']) || !empty($rating['is_moderator']));
        $rating['likeUsers'] = XenForo_Helper_Php::safeUnserialize($rating['like_users']);

        if (!isset($rating['criteriaFeedbacks']))
        {
            $rating['criteriaFeedbacks'] = XenForo_Helper_Php::safeUnserialize($rating['criteria_feedbacks']);
        }

        $rating['canView'] = $this->canViewTraderRating($rating, $null, $viewingUser);
        $rating['canLike'] = $this->canLikeTraderRating($rating, $null, $viewingUser);
        $rating['canRespond'] = $this->canRespondToTraderRating($rating, $null, $viewingUser);
        $rating['canApprove'] = $this->canApproveTraderRating($rating, $null, $viewingUser);
        $rating['canUnapprove'] = $this->canUnapproveTraderRating($rating, $null, $viewingUser);
        $rating['canDelete'] = $this->canDeleteTraderRating($rating, 'soft', $null, $viewingUser);
        $rating['canUndelete'] = $this->canUndeleteTraderRating($rating, $null, $viewingUser);
        $rating['canEdit'] = $this->canEditTraderRating($rating, $null, $viewingUser);
        $rating['canReport'] = $this->canReportTraderRating($rating, $null, $viewingUser);
        $rating['canWarn'] = $this->canWarnTraderRating($rating, $null, $viewingUser);

        if (!isset($rating['canInlineMod']))
        {
            $this->addInlineModOptionToTraderRating($rating, $viewingUser);
        }

        if (!empty($rating['delete_date']))
        {
            $rating['deleteInfo'] = array(
                'user_id' => $rating['delete_user_id'],
                'username' => $rating['delete_username'],
                'date' => $rating['delete_date'],
                'reason' => $rating['delete_reason'],
            );
        }

        return $rating;
    }

    public function prepareTraderRatings(array $ratings)
    {
        foreach ($ratings as &$rating)
        {
            $rating = $this->prepareTraderRating($rating);
        }

        return $ratings;
    }

    public function filterUnviewableTraderRatings(array $ratings, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        foreach ($ratings as $key => $rating)
        {
            if (!$this->canViewTraderRating($rating, $null, $viewingUser))
            {
                unset ($ratings[$key]);
            }
        }

        return $ratings;
    }

    public function addInlineModOptionToTraderRating(array &$rating, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $modOptions = array();
        $canInlineMod = ($viewingUser['user_id'] && (
                $this->hasPermission('editAny')
                || $this->hasPermission('deleteAny')
                || $this->hasPermission('undelete')
                || $this->hasPermission('approveUnapprove')
            ));

        if ($canInlineMod)
        {
            if ($this->hasPermission('editAny', $viewingUser))
            {
                $modOptions['edit'] = true;
            }

            if ($this->canDeleteTraderRating($rating, 'soft', $null, $viewingUser))
            {
                $modOptions['delete'] = true;
            }

            if ($this->canUndeleteTraderRating($rating, $null, $viewingUser))
            {
                $modOptions['undelete'] = true;
            }

            if ($this->canApproveTraderRating($rating, $null, $viewingUser))
            {
                $modOptions['approve'] = true;
            }

            if ($this->canUnapproveTraderRating($rating, $null, $viewingUser))
            {
                $modOptions['unapprove'] = true;
            }
        }

        $rating['canInlineMod'] = count($modOptions) > 0;
        return $modOptions;
    }

    public function getInlineModOptionsForTraderRatings(array $ratings, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        $inlineModOptions = array();

        foreach ($ratings as $rating)
        {
            $inlineModOptions += $this->addInlineModOptionToTraderRating($rating, $viewingUser);
        }

        return $inlineModOptions;
    }

    public function canAddTraderRating($classified = null, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified)
        {
            if (!$this->_getClassifiedModel()->canAssociateClassifiedToTraderRating($classified, $errorPhraseKey, $viewingUser))
            {
                return false;
            }
        }
        elseif (!KomuKuYJB_Options::getInstance()->get('allowOutsideRating'))
        {
            return false;
        }

        return $this->hasPermission('add', $viewingUser);
    }

    public function canViewTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$this->hasPermission('view', $viewingUser) && $rating['for_user_id'] != $viewingUser['user_id'])
        {
            return false;
        }

        if ($rating['feedback_state'] == 'moderated')
        {
            if (!$this->hasPermission('viewModerated', $viewingUser))
            {
                if (!$viewingUser['user_id'] || $viewingUser['user_id'] != $rating['user_id'])
                {
                    return false;
                }
            }
        }
        elseif ($rating['feedback_state'] == 'deleted')
        {
            if (!$this->hasPermission('viewDeleted', $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function canLikeTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($rating['feedback_state'] != 'visible')
        {
            return false;
        }

        if ($viewingUser['user_id'] == $rating['user_id'] || $viewingUser['user_id'] == $rating['for_user_id'])
        {
            $errorPhraseKey = 'liking_own_content_cheating';
            return false;
        }

        return $this->hasPermission('like', $viewingUser);
    }

    public function canRespondToTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($rating['feedback_state'] != 'visible')
        {
            return false;
        }

        if ($viewingUser['user_id'] == $rating['user_id'])
        {
            $errorPhraseKey = 'you_cannot_respond_to_your_own_trader_rating';
            return false;
        }

        if ($viewingUser['user_id'] != $rating['for_user_id'])
        {
            return false;
        }

        return $this->hasPermission('respond', $viewingUser);
    }

    public function canEditTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($this->hasPermission('editAny', $viewingUser))
        {
            return true;
        }

        if ($rating['feedback_state'] != 'visible')
        {
            return false;
        }

        if ($rating['user_id'] == $viewingUser['user_id'] && $this->hasPermission('edit', $viewingUser))
        {
            $editLimit = $this->hasPermission('editOwnTime', $viewingUser);
            if ($editLimit != -1 && (!$editLimit || $rating['feedback_date'] < XenForo_Application::$time - 60 * $editLimit))
            {
                $errorPhraseKey = array('trader_rating_edit_time_limit_expired', 'minutes' => $editLimit);
                return false;
            }

            return true;
        }

        return false;
    }

    public function canDeleteTraderRating(array $rating, $deleteType = 'soft', &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($deleteType == 'hard')
        {
            return $this->hasPermission('hardDeleteAny', $viewingUser);
        }

        if ($rating['feedback_state'] != 'visible')
        {
            return false;
        }

        if ($this->hasPermission('deleteAny', $viewingUser))
        {
            return true;
        }

        if ($rating['user_id'] == $viewingUser['user_id'] && $this->hasPermission('delete', $viewingUser))
        {
            $editLimit = $this->hasPermission('editOwnTime', $viewingUser);
            if ($editLimit != -1 && (!$editLimit || $rating['feedback_date'] < XenForo_Application::$time - 60 * $editLimit))
            {
                $errorPhraseKey = array('trader_rating_delete_time_limit_expired', 'minutes' => $editLimit);
                return false;
            }

            return true;
        }

        return false;
    }

    public function canUndeleteTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($rating['feedback_state'] != 'deleted')
        {
            return false;
        }

        return $this->hasPermission('undelete', $viewingUser);
    }

    public function canWarnTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if (!empty($rating['warning_id']) || empty($rating['user_id']))
        {
            return false;
        }

        if (!empty($rating['is_admin']) || !empty($rating['is_moderator']))
        {
            return false;
        }

        if ($rating['user_id'] == $viewingUser['user_id'])
        {
            return false;
        }

        return $this->hasPermission('warn', $viewingUser);
    }

    public function canApproveTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($rating['feedback_state'] != 'moderated')
        {
            return false;
        }

        return $this->hasPermission('approveUnapprove', $viewingUser);
    }

    public function canUnapproveTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($rating['feedback_state'] != 'visible')
        {
            return false;
        }

        return $this->hasPermission('approveUnapprove', $viewingUser);
    }

    public function canReportTraderRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if ($rating['feedback_state'] != 'visible')
        {
            return false;
        }

        return $this->getModelFromCache('XenForo_Model_User')->canReportContent($errorPhraseKey, $viewingUser);
    }

    public function hasPermission($permission, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        return XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifiedTraderRating', $permission);
    }

    public function getPermissionBasedFetchConditions(array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $viewAllModerated = $this->hasPermission('viewModerated', $viewingUser);
        $viewAllDeleted = $this->hasPermission('viewDeleted', $viewingUser);

        if ($viewAllModerated)
        {
            $viewModerated = true;
        }
        elseif ($viewingUser['user_id'])
        {
            $viewModerated = $viewingUser['user_id'];
        }
        else
        {
            $viewModerated = false;
        }

        $conditions = array(
            'visible' => true,
            'moderated' => $viewModerated,
            'deleted' => $viewAllDeleted
        );

        return $conditions;
    }

    public function batchUpdateLikeUser($oldUserId, $newUserId, $oldUsername, $newUsername)
    {
        $db = $this->_getDb();

        $db->query(
            'UPDATE (
                SELECT content_id FROM kmk_liked_content
                WHERE content_type = \'classified_trader_rating\'
                AND like_user_id = ?
            ) AS temp
            INNER JOIN kmk_classifieds_rating_feedback AS feedback ON (feedback.feedback_id = temp.content_id)
            SET like_users = REPLACE(like_users, ' .
            $db->quote('i:' . $oldUserId . ';s:8:"username";s:' . strlen($oldUsername) . ':"' . $oldUsername . '";') . ', ' .
            $db->quote('i:' . $newUserId . ';s:8:"username";s:' . strlen($newUsername) . ':"' . $newUsername . '";') . ')', $newUserId
        );
    }

    public function deleteTraderRating($feedbackId, $deleteType, array $options = array())
    {
        $options = array_merge(array(
            'reason' => '',
            'authorAlert' => false,
            'authorAlertReason' => ''
        ), $options);

        /** @var KomuKuYJB_DataWriter_TraderRating $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_TraderRating');
        $writer->setExistingData($feedbackId);

        if ($deleteType == 'hard')
        {
            $writer->delete();
        }
        else
        {
            $writer->setExtraData($writer::DATA_DELETE_REASON, $options['reason']);
            $writer->set('feedback_state', 'deleted');
            $writer->save();
        }

        if ($options['authorAlert'])
        {

        }
    }

    public function sendNotificationToReviewee(array $rating)
    {
        if ($rating['feedback_state'] != 'visible')
        {
            return array();
        }

        /** @var XenForo_Model_User $userModel */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        $reviewer = $userModel->getUserById($rating['user_id']);
        $reviewee = $userModel->getUserById($rating['for_user_id']);

        if (!$reviewer || !$reviewee)
        {
            return array();
        }

        $emailed = false;
        $alerted = false;

        /*if ($reviewee['email_subscribe'] && $reviewee['email'] && $reviewee['user_state'] == 'valid')
        {
            $reviewee['email_confirm_key'] = $userModel->getUserEmailConfirmKey($reviewee);

            $mail = XenForo_Mail::create('classified_trader_rating_insert', array(
                'rating' => $rating,
                'receiver' => $reviewee,
                'poster' => $reviewer
            ), $reviewee['language_id']);

            $mail->enableAllLanguagePreCache();
            $mail->queue($reviewee['email'], $reviewee['username']);
            $emailed = true;
        }*/

        if (XenForo_Model_Alert::userReceivesAlert($reviewee, 'classified_trader_rating', 'insert'))
        {
            XenForo_Model_Alert::alert(
                $reviewee['user_id'], $reviewer['user_id'], $reviewer['username'],
                'classified_trader_rating', $rating['feedback_id'], 'insert'
            );

            $alerted = true;
        }

        return array(
            'emailed' => $emailed,
            'alerted' => $alerted
        );
    }

    public function rebuildCount()
    {
        @set_time_limit(0);
        $db = $this->_getDb();

        $feedbacks = $db->fetchAll(
            'SELECT for_user_id, rating
            FROM kmk_classifieds_rating_feedback
            WHERE feedback_state = \'visible\''
        );

        $userIds = array();

        foreach ($feedbacks as $data)
        {
            $userId = $data['for_user_id'];
            $rating = $data['rating'];

            if (!isset($userIds[$userId]))
            {
                $userIds[$userId] = array(
                    'rating_count' => 0,
                    'rating_positive_count' => 0,
                    'rating_neutral_count' => 0,
                    'rating_negative_count' => 0,
                    'rating_avg' => 0,
                    'rating_weighted' => 0
                );
            }

            $userIds[$userId]['rating_count']++;

            if ($rating == 1)
            {
                $userIds[$userId]['rating_positive_count']++;
            }
            elseif ($rating == -1)
            {
                $userIds[$userId]['rating_negative_count']++;
            }
            else
            {
                $userIds[$userId]['rating_neutral_count']++;
            }

            $sum = ($userIds[$userId]['rating_positive_count'] * 5) + ($userIds[$userId]['rating_neutral_count'] * 3) + ($userIds[$userId]['rating_negative_count'] * 1);
            $average = $sum / $userIds[$userId]['rating_count'];

            $userIds[$userId]['rating_avg'] = $average;
            $userIds[$userId]['rating_weighted'] = $this->getWeightedRating($userIds[$userId]['rating_count'], $sum);
        }

        $db->update('kmk_classifieds_trader', array(
            'rating_count' => 0,
            'rating_positive_count' => 0,
            'rating_neutral_count' => 0,
            'rating_negative_count' => 0,
            'rating_avg' => 0,
            'rating_weighted' => 0
        ));

        foreach ($userIds as $userId => $bind)
        {
            $db->update('kmk_classifieds_trader', $bind, 'user_id = ' . $db->quote($userId));
        }
    }

    /**
     * @return KomuKuYJB_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Classified');
    }
}