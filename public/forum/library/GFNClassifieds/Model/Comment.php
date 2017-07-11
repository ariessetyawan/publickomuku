<?php /*adf4d5aafc3d5a40acbe83c4f4f6a584255d54c3*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_Comment extends GFNClassifieds_Model
{
    const FETCH_USER            = 0x01;
    const FETCH_USER_OPTION     = 0x02;
    const FETCH_CLASSIFIED      = 0x04;
    const FETCH_CATEGORY        = 0x0C;
    const FETCH_PARENT_COMMENT  = 0x10;
    const FETCH_DELETION_LOG    = 0x20;

    public function getCommentById($commentId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareCommentFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            'SELECT comment.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_comment AS comment
            ' . $joinOptions['joinTables'] . '
            WHERE comment_id = ?', $commentId
        );
    }

    public function getComments(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->prepareCommentConditions($conditions, $fetchOptions);
        $orderClause = $this->prepareCommentOrderOptions($fetchOptions, 'comment.post_date DESC');
        $joinOptions = $this->prepareCommentFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT comment.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_comment AS comment
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause . '
            ' . $orderClause, $limitOptions['limit'], $limitOptions['offset']
        ), 'comment_id');
    }

    public function getCommentsByIds(array $commentIds, array $fetchOptions = array())
    {
        if (!$commentIds)
        {
            return array();
        }

        $joinOptions = $this->prepareCommentFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            'SELECT comment.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_comment AS comment
            ' . $joinOptions['joinTables'] . '
            WHERE comment.comment_id IN (' . $this->_getDb()->quote($commentIds) . ')', 'comment_id'
        );
    }

    public function countComments(array $conditions)
    {
        $fetchOptions = array();

        $whereClause = $this->prepareCommentConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareCommentFetchOptions($fetchOptions);

        return $this->_getDb()->fetchOne(
            'SELECT COUNT(*)
            FROM kmk_classifieds_comment AS comment
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause
        );
    }

    public function getRepliesByParentCommentIds(array $parentCommentIds, array $conditions = array(), array $fetchOptions = array())
    {
        if (!$parentCommentIds)
        {
            return array();
        }

        $conditions['reply_parent_comment_id'] = $parentCommentIds;
        $whereClause = $this->prepareCommentConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareCommentFetchOptions($fetchOptions);
        $db = $this->_getDb();

        $firstList = $this->fetchAllKeyed(
            'SELECT comment.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_comment AS comment
            ' . $joinOptions['joinTables'] . '
            INNER JOIN (
                SELECT MAX(post_date) AS post_date
                FROM kmk_classifieds_comment
                WHERE reply_parent_comment_id IN (' . $db->quote($parentCommentIds) . ')
                GROUP BY reply_parent_comment_id
            ) AS temp
            WHERE ' . $whereClause . '
            AND temp.post_date = comment.post_date', 'comment_id'
        );

        if (empty($firstList))
        {
            return array();
        }

        $alreadyIncluded = array_keys($firstList);
        $secondList = $this->fetchAllKeyed(
            'SELECT comment.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_comment AS comment
            ' . $joinOptions['joinTables'] . '
            INNER JOIN (
                SELECT MAX(post_date) AS post_date
                FROM kmk_classifieds_comment
                WHERE reply_parent_comment_id IN (' . $db->quote($parentCommentIds) . ')
                AND comment_id NOT IN (' . $db->quote($alreadyIncluded) . ')
                GROUP BY reply_parent_comment_id
            ) AS temp
            WHERE ' . $whereClause . '
            AND temp.post_date = comment.post_date', 'comment_id'
        );

        if (empty($secondList))
        {
            return $firstList;
        }

        $alreadyIncluded = array_merge($alreadyIncluded, array_keys($secondList));
        $thirdList  = $this->fetchAllKeyed(
            'SELECT comment.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_comment AS comment
            ' . $joinOptions['joinTables'] . '
            INNER JOIN (
                SELECT MAX(post_date) AS post_date
                FROM kmk_classifieds_comment
                WHERE reply_parent_comment_id IN (' . $db->quote($parentCommentIds) . ')
                AND comment_id NOT IN (' . $db->quote($alreadyIncluded) . ')
                GROUP BY reply_parent_comment_id
            ) AS temp
            WHERE ' . $whereClause . '
            AND temp.post_date = comment.post_date', 'comment_id'
        );

        return array_merge($thirdList, $secondList, $firstList);
    }

    public function getLatestComments($classifiedId, $lastCommentDate, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareCommentFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            'SELECT comment.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_comment AS comment
            ' . $joinOptions['joinTables'] . '
            WHERE comment.post_date > ?
            AND comment.classified_id = ?
            AND comment.reply_parent_comment_id = 0
            ORDER BY comment.post_date ASC', 'comment_id', array($lastCommentDate, $classifiedId)
        );
    }

    public function prepareCommentConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (isset($conditions['classified']))
        {
            $conditions['classified_id'] = $conditions['classified'];
        }

        if (isset($conditions['classified_ids']))
        {
            $conditions['classified_id'] = $conditions['classified_ids'];
        }

        if (!empty($conditions['classified_id']))
        {
            if (is_array($conditions['classified_id']))
            {
                $sqlConditions[] = 'comment.classified_id IN (' . $db->quote($conditions['classified_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'comment.classified_id = ' . $db->quote($conditions['classified_id']);
            }

            $conditions['reply_parent_comment_id'] = 0;
        }

        if (isset($conditions['super_id']))
        {
            $conditions['reply_parent_comment_id'] = $conditions['super_id'];
        }

        if (isset($conditions['reply_parent_comment_id']))
        {
            if (is_array($conditions['reply_parent_comment_id']))
            {
                $sqlConditions[] = 'comment.reply_parent_comment_id IN (' . $db->quote($conditions['reply_parent_comment_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'comment.reply_parent_comment_id = ' . $db->quote($conditions['reply_parent_comment_id']);
            }
        }

        if (isset($conditions['parent_id']))
        {
            $conditions['reply_comment_id'] = $conditions['parent_id'];
        }

        if (isset($conditions['reply_comment_id']))
        {
            if (is_array($conditions['reply_comment_id']))
            {
                $sqlConditions[] = 'comment.reply_comment_id IN (' . $db->quote($conditions['reply_comment_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'comment.reply_comment_id = ' . $db->quote($conditions['reply_comment_id']);
            }
        }

        if (isset($conditions['user_ids']))
        {
            $conditions['user_id'] = $conditions['user_ids'];
        }

        if (!empty($conditions['user_id']))
        {
            if (is_array($conditions['user_id']))
            {
                $sqlConditions[] = 'comment.user_id IN (' . $db->quote($conditions['user_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'comment.user_id = ' . $db->quote($conditions['user_id']);
            }
        }

        if (!empty($conditions['post_date']))
        {
            $sqlConditions[] = $this->getCutOffCondition('comment.post_date', $conditions['post_date']);
        }

        if (!empty($conditions['state']))
        {
            switch ($conditions['state'])
            {
                case 'moderated':
                case 'deleted':
                    $conditions[$conditions['state']] = true;
                    break;
            }
        }

        if (isset($conditions['deleted']) || isset($conditions['moderated']))
        {
            $sqlConditions[] = $this->prepareStateLimitFromConditions($conditions, 'comment', 'message_state');
        }
        else
        {
            $sqlConditions[] = "comment.message_state = 'visible'";
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareCommentFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $db = $this->_getDb();

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_USER)
            {
                $selectFields .= ', user.*, user_profile.*, trader.*, IF(user.username IS NULL, comment.username, user.username) AS username';
                $joinTables .= '
                    LEFT JOIN kmk_user AS user ON (user.user_id = comment.user_id)
                    LEFT JOIN kmk_user_profile AS user_profile ON (user_profile.user_id = comment.user_id)
                    LEFT JOIN kmk_classifieds_trader AS trader ON (trader.user_id = comment.user_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_CLASSIFIED)
            {
                $selectFields .= ', classified.classified_id, classified.title AS classified_title,
                    classified.tag_line AS classified_tag_line, classified.user_id AS classified_user_id,
                    classified.username AS classified_username, classified.classified_state, classified.expire_date AS classified_expire_date,
                    classified.category_id, classified.advert_type_id, classified.package_id, classified.discussion_thread_id,
                    classified.prefix_id AS classified_prefix_id, classified.price AS classified_price, classified.currency AS classified_currency,
                    classified.price_base_currency AS classified_price_base_currency, classified.comment_count AS classified_comment_count';

                $joinTables .= '
                    LEFT JOIN kmk_classifieds_classified AS classified ON (classified.classified_id = comment.classified_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_CATEGORY)
            {
                $selectFields .= ', category.category_id, category.title AS category_title';

                $joinTables .= '
                    LEFT JOIN kmk_classifieds_category AS category ON (classified.category_id = category.category_id)';

                if (!empty($fetchOptions['permissionCombinationId']))
                {
                    $selectFields .= ', permission.cache_value AS category_permission_cache';
                    $joinTables .= '
                        LEFT JOIN kmk_permission_cache_content AS permission
                            ON (permission.permission_combination_id = ' . $db->quote($fetchOptions['permissionCombinationId']) . '
                                AND permission.content_type = \'classified_category\'
                                AND permission.content_id = category.category_id
                            )';
                }
            }

            if ($fetchOptions['join'] & self::FETCH_PARENT_COMMENT)
            {
                $selectFields .= ', parent_comment.comment_id AS parent_comment_id, parent_comment.message AS parent_comment_message,
                    parent_comment.user_id AS parent_comment_user_id, parent_comment.username AS parent_comment_username,
                    parent_comment.post_date AS parent_comment_post_date';

                $joinTables .= '
                    LEFT JOIN kmk_classifieds_comment AS parent_comment ON (comment.reply_parent_comment_id = parent_comment.comment_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_DELETION_LOG)
            {
                $selectFields .= ', deletion_log.delete_date, deletion_log.delete_reason,
                    deletion_log.delete_user_id, deletion_log.delete_username';
                $joinTables .= '
                    LEFT JOIN kmk_deletion_log AS deletion_log
                        ON (deletion_log.content_type = \'classified_comment\' AND deletion_log.content_id = comment.comment_id)';
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
                        ON (liked_content.content_type = \'classified_comment\'
                            AND liked_content.content_id = comment.comment_id
                            AND liked_content.like_user_id = ' . $db->quote($fetchOptions['likeUserId']) . '
                        )';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    public function prepareCommentOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
    {
        $choices = array(
            'post_date' => 'comment.post_date',
            'likes' => 'comment.likes %s, comment.post_date DESC',
        );

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    public function alertTaggedMembers(array $comment, array $tagged, array $alreadyAlerted = array(), $isReply = false)
    {
        $classified = $this->_getClassifiedModel()->getClassifiedById($comment['classified_id'], array(
            'join' => GFNClassifieds_Model_Classified::FETCH_CATEGORY
        ));

        $userIds = XenForo_Application::arrayColumn($tagged, 'user_id');
        $userIds = array_diff($userIds, $alreadyAlerted);
        $alertedUserIds = array();

        if ($userIds)
        {
            /** @var XenForo_Model_User $userModel */
            $userModel = $this->getModelFromCache('XenForo_Model_User');

            $users = $userModel->getUsersByIds($userIds, array(
                'join' => XenForo_Model_User::FETCH_USER_OPTION
                    | XenForo_Model_User::FETCH_USER_PROFILE
                    | XenForo_Model_User::FETCH_USER_PERMISSIONS
            ));

            foreach ($users as $user)
            {
                if (!isset($alertedUserIds[$user['user_id']]) && $user['user_id'] != $comment['user_id'])
                {
                    $user['permissions'] = XenForo_Permission::unserializePermissions($user['global_permission_cache']);

                    if (!$userModel->isUserIgnored($user, $comment['user_id'])
                        && XenForo_Model_Alert::userReceivesAlert($user, 'classified_comment', 'tag')
                        && $this->canViewComment($comment, $classified, $classified, $null, $user)
                    )
                    {
                        $alertedUserIds[$user['user_id']] = true;

                        XenForo_Model_Alert::alert(
                            $user['user_id'], $comment['user_id'], $comment['username'],
                            'classified_comment', $comment['comment_id'], 'tag'
                        );
                    }
                }
            }
        }

        return array_keys($alertedUserIds);
    }

    public function prepareComment(array $comment, array $classified = null, array $category = null, array $viewingUser = null)
    {
        if ($classified && !isset($comment['classified_title']))
        {
            $comment['classified_title'] = $classified['title'];
        }

        $comment['isDeleted'] = $comment['message_state'] == 'deleted';
        $comment['isModerated'] = $comment['message_state'] == 'moderated';
        $comment['isTrusted'] = !empty($comment['user_id']) && (!empty($comment['is_admin']) || !empty($comment['is_moderator']));
        $comment['likeUsers'] = XenForo_Helper_Php::safeUnserialize($comment['like_users']);

        if ($category)
        {
            $comment['canApprove'] = $this->canApproveComment($comment, $classified, $category, $null, $viewingUser);
            $comment['canDelete'] = $this->canDeleteComment($comment, $classified, $category, 'soft', $null, $viewingUser);
            $comment['canEdit'] = $this->canEditComment($comment, $classified, $category, $null, $viewingUser);
            $comment['canLike'] = $this->canLikeComment($comment, $classified, $category, $null, $viewingUser);
            $comment['canReply'] = $this->canReplyToComment($comment, $classified, $category, $null, $viewingUser);
            $comment['canReport'] = $this->canReportComment($comment, $classified, $category, $null, $viewingUser);
            $comment['canUnapprove'] = $this->canUnapproveComment($comment, $classified, $category, $null, $viewingUser);
            $comment['canUndelete'] = $this->canUndeleteComment($comment, $classified, $category, $null, $viewingUser);
            $comment['canView'] = $this->canViewComment($comment, $classified, $category, $null, $viewingUser);
            $comment['canWarn'] = $this->canWarnComment($comment, $classified, $category, $null, $viewingUser);

            if (!isset($comment['canInlineMod']))
            {
                $this->addInlineModOptionToComment($comment, $classified, $category, $viewingUser);
            }
        }
        else
        {
            $comment['canApprove'] = false;
            $comment['canDelete'] = false;
            $comment['canEdit'] = false;
            $comment['canLike'] = false;
            $comment['canReply'] = false;
            $comment['canReport'] = false;
            $comment['canUnapprove'] = false;
            $comment['canUndelete'] = false;
            $comment['canView'] = false;
            $comment['canWarn'] = false;
        }

        if (!empty($comment['delete_date']))
        {
            $comment['deleteInfo'] = array(
                'user_id' => $comment['delete_user_id'],
                'username' => $comment['delete_username'],
                'date' => $comment['delete_date'],
                'reason' => $comment['delete_reason'],
            );
        }

        return $comment;
    }

    public function addInlineModOptionToComment(array &$comment, array $classified, array $category, array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        $modOptions = array();
        $canInlineMod = ($viewingUser['user_id'] && (
                XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteCommentAny')
                || XenForo_Permission::hasContentPermission($categoryPermissions, 'undeleteComment')
                || XenForo_Permission::hasContentPermission($categoryPermissions, 'approveUnapproveComment')
            ));

        if ($canInlineMod)
        {
            if ($this->canDeleteComment($comment, $classified, $category, 'soft', $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['delete'] = true;
            }

            if ($this->canUndeleteComment($comment, $classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['undelete'] = true;
            }

            if ($this->canApproveComment($comment, $classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['approve'] = true;
            }

            if ($this->canUnapproveComment($comment, $classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['unapprove'] = true;
            }
        }

        $comment['canInlineMod'] = count($modOptions) > 1;
        return $modOptions;
    }

    public function getInlineModOptionsForComments(array $comments, array $classified, array $category, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
        $inlineModOptions = array();

        foreach ($comments as $comment)
        {
            $inlineModOptions += $this->addInlineModOptionToComment($comment, $classified, $category, $viewingUser);
        }

        return $inlineModOptions;
    }

    public function prepareComments(array $comments, array $classified = null, array $category = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        foreach ($comments as &$comment)
        {
            $comment = $this->prepareComment($comment, $classified, $category, $viewingUser);
        }

        return $comments;
    }

    public function canViewCommentAndContainer(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$this->_getClassifiedModel()->canViewClassifiedAndContainer($classified, $category, $errorPhraseKey, $viewingUser, $categoryPermissions))
        {
            return false;
        }

        return $this->canViewComment($comment, $classified, $category, $errorPhraseKey, $viewingUser, $categoryPermissions);
    }

    public function canViewComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$this->hasPermission('viewComment', $category, $viewingUser))
        {
            return false;
        }

        if ($comment['message_state'] == 'moderated')
        {
            if (!$this->hasPermission('viewCommentModerated', $category, $viewingUser))
            {
                if (!$viewingUser['user_id'] || $viewingUser['user_id'] != $comment['user_id'])
                {
                    return false;
                }
            }
        }
        elseif ($comment['message_state'] == 'deleted')
        {
            if (!$this->hasPermission('viewCommentDeleted', $category, $viewingUser))
            {
                return false;
            }
        }

        return true;
    }

    public function canReplyToComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$this->canViewComment($comment, $classified, $category, $errorPhraseKey, $viewingUser, $categoryPermissions))
        {
            return false;
        }

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($comment['message_state'] != 'visible')
        {
            return false;
        }

        return $this->hasPermission('addComment', $category, $viewingUser);
    }

    public function canApproveComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($comment['message_state'] != 'moderated')
        {
            return false;
        }

        return $this->hasPermission('approveUnapproveComment', $category, $viewingUser);
    }

    public function canUnapproveComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($comment['message_state'] != 'visible')
        {
            return false;
        }

        return $this->hasPermission('approveUnapproveComment', $category, $viewingUser);
    }

    public function canEditComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($this->hasPermission('editCommentAny', $category, $viewingUser))
        {
            return true;
        }

        if ($comment['user_id'] == $viewingUser['user_id'] && $this->hasPermission('editCommentSelf', $category, $viewingUser))
        {
            $editLimit = $this->hasPermission('editOwnClassifiedTime', $category, $viewingUser);
            if ($editLimit != -1 && (!$editLimit || $comment['post_date'] < XenForo_Application::$time - 60 * $editLimit))
            {
                $errorPhraseKey = array('comment_edit_time_limit_expired', 'minutes' => $editLimit);
                return false;
            }

            return true;
        }

        return false;
    }

    public function canDeleteComment(array $comment, array $classified, array $category, $deleteType, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($comment['message_state'] == 'deleted')
        {
            return false;
        }

        if ($deleteType == 'hard')
        {
            return $this->hasPermission('hardDeleteCommentAny', $category, $viewingUser);
        }

        if ($comment['user_id'] == $viewingUser['user_id'])
        {
            return $this->hasPermission('deleteCommentSelf', $category, $viewingUser);
        }

        return $this->hasPermission('deleteCommentAny', $category, $viewingUser);
    }

    public function canUndeleteComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($comment['message_state'] != 'deleted')
        {
            return false;
        }

        return $this->hasPermission('undeleteComment', $category, $viewingUser);
    }

    public function canWarnComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($comment['warning_id'] || empty($comment['user_id']))
        {
            return false;
        }

        if (!empty($comment['is_admin']) || !empty($comment['is_moderator']))
        {
            return false;
        }

        if ($comment['user_id'] == $viewingUser['user_id'])
        {
            return false;
        }

        return $this->hasPermission('warnComment', $category, $viewingUser);
    }

    public function canReportComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if ($comment['message_state'] != 'visible')
        {
            return false;
        }

        return $this->getModelFromCache('XenForo_Model_User')->canReportContent($errorPhraseKey, $viewingUser);
    }

    public function canLikeComment(array $comment, array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($comment['message_state'] != 'visible')
        {
            return false;
        }

        if ($comment['user_id'] == $viewingUser['user_id'])
        {
            $errorPhraseKey = 'liking_own_content_cheating';
            return false;
        }

        return $this->hasPermission('likeComment', $category, $viewingUser);
    }

    public function standardizeViewingUserReferenceForCategory($categoryId, array &$viewingUser = null, array &$categoryPermissions = null)
    {
        $this->_getClassifiedModel()->standardizeViewingUserReferenceForCategory($categoryId, $viewingUser, $categoryPermissions);
    }

    public function hasPermission($permission, $category, $viewingUser = null)
    {
        return $this->_getClassifiedModel()->hasPermission($permission, $category, $viewingUser);
    }

    public function getPermissionBasedFetchConditions(array $category = null, array $viewingUser = null, array $categoryPermissions = null)
    {
        if ($category)
        {
            $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
            $viewAllModerated = XenForo_Permission::hasContentPermission($categoryPermissions, 'viewCommentModerated');
            $viewAllDeleted = XenForo_Permission::hasContentPermission($categoryPermissions, 'viewCommentDeleted');
        }
        else
        {
            $this->standardizeViewingUserReference($viewingUser);
            $viewAllModerated = XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'viewCommentModerated');
            $viewAllDeleted = XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'viewCommentDeleted');
        }

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
            'deleted' => $viewAllDeleted,
            'moderated' => $viewModerated
        );

        return $conditions;
    }

    public function mergeRepliesToComments(array $replies, array &$comments)
    {
        foreach ($replies as $reply)
        {
            if (isset($comments[$reply['reply_parent_comment_id']]))
            {
                $comment = &$comments[$reply['reply_parent_comment_id']];

                if (!isset($comment['first_shown_reply_date']) || $comment['first_shown_reply_date'] > $reply['post_date'])
                {
                    $comment['first_shown_reply_date'] = $reply['post_date'];
                }

                $comment['replies'][$reply['comment_id']] = $reply;
            }
        }
    }

    public function batchUpdateLikeUser($oldUserId, $newUserId, $oldUsername, $newUsername)
    {
        $db = $this->_getDb();

        $db->query(
            'UPDATE (
				SELECT content_id FROM kmk_liked_content
				WHERE content_type = \'classified_comment\'
				AND like_user_id = ?
			) AS temp
			INNER JOIN kmk_classifieds_comment AS comment ON (comment.comment_id = temp.content_id)
			SET like_users = REPLACE(like_users, ' .
            $db->quote('i:' . $oldUserId . ';s:8:"username";s:' . strlen($oldUsername) . ':"' . $oldUsername . '";') . ', ' .
            $db->quote('i:' . $newUserId . ';s:8:"username";s:' . strlen($newUsername) . ':"' . $newUsername . '";') . ')', $newUserId
        );
    }

    public function removeChildComments($parentCommentId)
    {
        return;

        $childCommentIds = $this->_getDb()->fetchCol(
            'SELECT comment_id
            FROM kmk_classifieds_comment
            WHERE reply_comment_id = ?
            OR reply_parent_comment_id = ?', array($parentCommentId, $parentCommentId)
        );

        if (!$childCommentIds)
        {
            return;
        }

        XenForo_Db::beginTransaction();

        foreach ($childCommentIds as $commentId)
        {
            /** @var GFNClassifieds_DataWriter_Comment $writer */
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
            $writer->setOption($writer::OPTION_DELETE_CHILDREN, false);
            $writer->setExistingData($commentId);
            $writer->delete();
        }

        XenForo_Db::commit();
    }

    public function deleteComment($commentId, $deleteType, array $options = array())
    {
        $options = array_merge(array(
            'reason' => '',
            'authorAlert' => false,
            'authorAlertReason' => ''
        ), $options);

        /** @var GFNClassifieds_DataWriter_Comment $writer */
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment');
        $writer->setExistingData($commentId);

        if ($deleteType == 'hard')
        {
            $writer->delete();
        }
        else
        {
            $writer->setExtraData($writer::DATA_DELETE_REASON, $options['reason']);
            $writer->set('message_state', 'deleted');
            $writer->save();
        }

        if ($options['authorAlert'])
        {

        }
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    protected function _getClassifiedModel()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_Classified');
    }
}