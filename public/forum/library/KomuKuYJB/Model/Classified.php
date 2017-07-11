<?php /*df39f5cde72644ca193caf8173cd2df757d69f91*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_Classified extends XenForo_Model
{
    const FETCH_USER            = 0x01;
    const FETCH_USER_OPTION     = 0x02;
    const FETCH_CATEGORY        = 0x04;
    const FETCH_DELETION_LOG    = 0x08;
    const FETCH_LOCATION        = 0x10;

    public function getClassifiedById($classifiedId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareClassifiedFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            'SELECT classified.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_classified AS classified
            ' . $joinOptions['joinTables'] . '
            WHERE classified.classified_id = ?', $classifiedId
        );
    }

    public function getClassifiedsByIds($classifiedIds, array $fetchOptions = array())
    {
        if (!$classifiedIds)
        {
            return array();
        }

        $joinOptions = $this->prepareClassifiedFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            'SELECT classified.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_classified AS classified
            ' . $joinOptions['joinTables'] . '
            WHERE classified.classified_id IN (' . $this->_getDb()->quote($classifiedIds) . ')', 'classified_id'
        );
    }

    public function getClassifiedByDiscussionId($threadId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareClassifiedFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            'SELECT classified.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_classified AS classified
            ' . $joinOptions['joinTables'] . '
            WHERE classified.discussion_thread_id = ?', $threadId
        );
    }

    public function getClassifiedsByDiscussionIds(array $threadIds, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareClassifiedFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            'SELECT classified.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_classified AS classified
            ' . $joinOptions['joinTables'] . '
            WHERE classified.discussion_thread_id IN (' . $this->_getDb()->quote($threadIds) . ')', 'classified_id'
        );
    }

    public function getClassifiedIdsInRange($start, $limit)
    {
        $db = $this->_getDb();

        return $db->fetchCol($db->limit(
            'SELECT classified_id
            FROM kmk_classifieds_classified
            WHERE classified_id > ?
            ORDER BY classified_id', $limit
        ), $start);
    }

    /**
     * @param array $conditions
     * @param array $fetchOptions
     * @return array
     */
    public function getClassifieds(array $conditions = array(), array $fetchOptions = array())
    {
        $whereClause = $this->prepareClassifiedConditions($conditions, $fetchOptions);
        $orderClause = $this->prepareClassifiedOrderOptions($fetchOptions);
        $joinOptions = $this->prepareClassifiedFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT classified.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_classified AS classified
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause . '
            ' . $orderClause . '
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'classified_id');
    }

    public function getFeaturedClassifiedsInCategories(array $categoryIds, array $fetchOptions = array())
    {
        if (!$categoryIds)
        {
            return array();
        }

        if (!empty($fetchOptions['order']) && $fetchOptions['order'] == 'random')
        {
            $orderClause = 'ORDER BY RAND()';
        }
        else
        {
            $orderClause = $this->prepareClassifiedOrderOptions($fetchOptions, 'classified.feature_date DESC');
        }

        $joinOptions = $this->prepareClassifiedFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            $this->limitQueryResults(
                'SELECT classified.*
                ' . $joinOptions['selectFields'] . '
                FROM kmk_classifieds_classified AS classified
                ' . $joinOptions['joinTables'] . '
                WHERE classified.category_id IN (' . $this->_getDb()->quote($categoryIds) . ')
                AND feature_date > 0
                AND classified_state IN (\'visible\')
                ' . $orderClause, $limitOptions['limit'], $limitOptions['offset']
            ), 'classified_id'
        );
    }

    public function getClassifiedsAboutToExpire(array $fetchOptions = array())
    {
        // todo
    }

    public function getLocationById($classifiedId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_classified_location
            WHERE classified_id = ?', $classifiedId
        );
    }

    public function countClassifieds(array $conditions = array())
    {
        $fetchOptions = array();

        $whereClause = $this->prepareClassifiedConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareClassifiedFetchOptions($fetchOptions);

        return $this->_getDb()->fetchOne(
            'SELECT COUNT(*)
            ' . 'FROM kmk_classifieds_classified AS classified
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause
        );
    }

    public function prepareClassifiedOrderOptions(array $fetchOptions, $defaultOrderSql = '')
    {
        $choices = array(
            'bump_date' => 'classified.last_bump_date',
            'item_date' => 'classified.classified_date',
            'expiring' => 'classified.expire_date',
            'price' => 'classified.price_base_currency',
            'views' => 'classified.view_count',
            'username' => 'classified.username %s, classified.last_update DESC',
            'title' => 'classified.title',
            'random' => 'RAND()'
        );

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    public function prepareClassifiedConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (!empty($conditions['classified_ids']))
        {
            $conditions['classified_id'] = $conditions['classified_ids'];
        }

        if (isset($conditions['classified_id']))
        {
            if (is_array($conditions['classified_id']))
            {
                $sqlConditions[] = 'classified.classified_id IN (' . $db->quote($conditions['classified_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'classified.classified_id = ' . $db->quote($conditions['classified_id']);
            }
        }

        if (isset($conditions['not_classified_id']))
        {
            if (is_array($conditions['not_classified_id']))
            {
                $sqlConditions[] = 'classified.classified_id NOT IN (' . $db->quote($conditions['not_classified_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'classified.classified_id != ' . $db->quote($conditions['not_classified_id']);
            }
        }

        if (!empty($conditions['user_id']))
        {
            if (is_array($conditions['user_id']))
            {
                $sqlConditions[] = 'classified.user_id IN (' . $db->quote($conditions['user_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'classified.user_id = ' . $db->quote($conditions['user_id']);
            }
        }

        if (!empty($conditions['category_id']))
        {
            if (is_array($conditions['category_id']))
            {
                $sqlConditions[] = 'classified.category_id IN (' . $db->quote($conditions['category_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'classified.category_id = ' . $db->quote($conditions['category_id']);
            }
        }

        if (!empty($conditions['prefix_id']))
        {
            if (is_array($conditions['prefix_id']))
            {
                if (in_array(-1, $conditions['prefix_id']))
                {
                    $conditions['prefix_id'][] = 0;
                }

                $sqlConditions[] = 'classified.prefix_id IN (' . $db->quote($conditions['prefix_id']) . ')';
            }
            elseif ($conditions['prefix_id'] == -1)
            {
                $sqlConditions[] = 'classified.prefix_id = 0';
            }
            else
            {
                $sqlConditions[] = 'classified.prefix_id = ' . $db->quote($conditions['prefix_id']);
            }
        }

        if (!empty($conditions['advert_type_id']))
        {
            if (is_array($conditions['advert_type_id']))
            {
                $sqlConditions[] = 'classified.advert_type_id IN (' . $db->quote($conditions['advert_type_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'classified.advert_type_id = ' . $db->quote($conditions['advert_type_id']);
            }
        }

        if (!empty($conditions['classified_id_not']))
        {
            $sqlConditions[] = 'classified.classified_id <> ' . $db->quote($conditions['classified_id_not']);
        }

        if (isset($conditions['featured']))
        {
            if (empty($conditions['featured']))
            {
                $sqlConditions[] = 'classified.feature_date = 0';
            }
            else
            {
                $sqlConditions[] = 'classified.feature_date <> 0';
            }
        }

        if (!empty($conditions['feature_date']))
        {
            $sqlConditions[] = $this->getCutOffCondition('classified.feature_date', $conditions['feature_date']);
        }

        if (isset($conditions['expire_date']))
        {
            $sqlConditions[] = $this->getCutOffCondition('classified.expire_date', $conditions['expire_date']);
        }

        /*if (isset($conditions['expired']) || isset($conditions['closed']))
        {
            $subConditions = array();

            if (isset($conditions['expired']))
            {
                if ($conditions['expired'] === false)
                {
                    $subConditions[] = 'classified.expire_date = 0 OR classified.expire_date > ' . $db->quote(XenForo_Application::$time);
                }
                else
                {
                    $subConditions[] = 'classified.expire_date > 0 AND classified.expire_date <= ' . $db->quote(XenForo_Application::$time);
                }
            }

            if (isset($conditions['closed']))
            {
                if ($conditions['closed'])
                {
                    $subConditions[] = 'classified.classified_open = 0';
                }
                else
                {
                    $subConditions[] = 'classified.classified_open = 1';
                }
            }

            if ($subConditions)
            {
                if (empty($conditions['expired']) && empty($conditions['closed']))
                {
                    $sqlConditions[] = '(' . implode(') AND (', $subConditions) . ')';
                }
                else
                {
                    $sqlConditions[] = '(' . implode(') OR (', $subConditions) . ')';
                }
            }
        }*/

        if (isset($conditions['states']))
        {
            $conditions['state'] = $conditions['states'];
        }

        if (!empty($conditions['state']))
        {
            if (!is_array($conditions['state']))
            {
                $conditions['state'] = array($conditions['state']);
            }

            foreach ($conditions['state'] as $state)
            {
                switch ($state)
                {
                    case 'visible':
                    case 'moderated':
                    case 'deleted':
                    case 'pending':
                    case 'expired':
                    case 'closed':
                    case 'completed':
                    case 'on_hold':
                        $conditions[$state] = true;
                        break;
                }
            }
        }

        if (isset($conditions['visible'])
            || isset($conditions['deleted'])
            || isset($conditions['moderated'])
            || isset($conditions['pending'])
            || isset($conditions['expired'])
            || isset($conditions['closed'])
            || isset($conditions['completed'])
            || isset($conditions['on_hold'])
        )
        {
            $sqlConditions[] = $this->prepareStateLimitFromConditions($conditions, 'classified', 'classified_state');
        }
        else
        {
            $sqlConditions[] = "classified.classified_state = 'visible'";
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareClassifiedFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $db = $this->_getDb();

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_USER)
            {
                $selectFields .= ', user.*, user_profile.*, trader.*, IF(user.username IS NULL, classified.username, user.username) AS username';
                $joinTables .= '
                    LEFT JOIN kmk_user AS user ON (user.user_id = classified.user_id)
                    LEFT JOIN kmk_user_profile AS user_profile ON (user_profile.user_id = classified.user_id)
                    LEFT JOIN kmk_classifieds_trader AS trader ON (trader.user_id = classified.user_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_CATEGORY)
            {
                $selectFields .= ', category.*, category.title AS category_title, classified.title AS title,
                    category.description AS category_description, classified.description AS description,
                    category.last_update AS category_last_update, classified.last_update AS last_update';
                $joinTables .= '
                    LEFT JOIN kmk_classifieds_category AS category ON (category.category_id = classified.category_id)';

                if (!empty($fetchOptions['permissionCombinationId']))
                {
                    $selectFields .= ', permission.cache_value AS category_permission_cache';
                    $joinTables .= '
                        LEFT JOIN kmk_permission_cache_content AS permission
                            ON (permission.permission_combination_id = ' . $db->quote($fetchOptions['permissionCombinationId']) . '
                                AND permission.content_type = \'classified_category\'
                                AND permission.content_id = category.category_id)';
                }
            }

            if ($fetchOptions['join'] & self::FETCH_LOCATION)
            {
                $selectFields .= ', location.*, classified.classified_id AS classified_id';
                $joinTables .= '
                    LEFT JOIN kmk_classifieds_classified_location AS location ON (location.classified_id = classified.classified_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_DELETION_LOG)
            {
                $selectFields .= ', deletion_log.delete_date, deletion_log.delete_reason,
                    deletion_log.delete_user_id, deletion_log.delete_username';
                $joinTables .= '
                    LEFT JOIN kmk_deletion_log AS deletion_log
                        ON (deletion_log.content_type = \'classified\' AND deletion_log.content_id = classified.classified_id)';
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
                        ON (liked_content.content_type = \'classified\'
                            AND liked_content.content_id = classified.classified_id
                            AND liked_content.like_user_id = ' . $db->quote($fetchOptions['likeUserId']) . '
                        )';
            }
        }

        if (isset($fetchOptions['watchUserId']))
        {
            if (empty($fetchOptions['watchUserId']))
            {
                $selectFields .= ', 0 AS is_watched';
            }
            else
            {
                $selectFields .= ', IF(classified_watch.user_id IS NULL, 0, IF(classified_watch.email_subscribe, \'watch_email\', \'watch_no_email\')) AS is_watched';
                $joinTables .= '
                    LEFT JOIN kmk_classifieds_classified_watch AS classified_watch
                        ON (classified_watch.classified_id = classified.classified_id
                            AND classified_watch.user_id = ' . $db->quote($fetchOptions['watchUserId']) . ')';
            }
        }

        if (empty($fetchOptions['contactUserId']))
        {
            $selectFields .= ', 0 AS conversation_id, 0 AS show_location';
        }
        else
        {
            $selectFields .= ', IF(conversation.conversation_id IS NULL, 0, conversation.conversation_id) AS conversation_id,
                                IF(conversation.show_location IS NULL, 0, conversation.show_location) AS show_location';
            $joinTables .= '
                LEFT JOIN kmk_classifieds_conversation AS conversation
                    ON (conversation.classified_id = classified.classified_id
                        AND conversation.user_id = ' . $db->quote($fetchOptions['contactUserId']) . ')';
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    public function prepareStateLimitFromConditions(array $fetchOptions, $table = '', $stateField = 'message_state', $userField = 'user_id')
    {
        $fetchOptions = array_merge(
            array(
                'visible' => true,
                'deleted' => false,
                'moderated' => false,
                'pending' => false,
                'expired' => false,
                'closed' => false,
                'completed' => false,
                'on_hold' => false
            ), $fetchOptions
        );

        $stateRef = ($table ? "$table.$stateField" : $stateField);
        $userRef = ($table ? "$table.$userField" : $userField);

        $states = array();

        if ($fetchOptions['visible'])
        {
            $states[] = "'visible'";
        }

        $moderatedLimit = '';
        $pendingLimit = '';
        $expiredLimit = '';
        $closedLimit = '';
        $completedLimit = '';
        $onHoldLimit = '';

        if ($fetchOptions['deleted'])
        {
            $states[] = "'deleted'";
        }

        if ($fetchOptions['pending'])
        {
            if ($fetchOptions['pending'] === true)
            {
                $states[] = "'pending'";
            }
            else
            {
                $pendingLimit = " OR ($stateRef = 'pending' AND $userRef = " . intval($fetchOptions['pending']) . ')';
            }
        }

        if ($fetchOptions['moderated'])
        {
            if ($fetchOptions['moderated'] === true)
            {
                $states[] = "'moderated'";
            }
            else
            {
                $moderatedLimit = " OR ($stateRef = 'moderated' AND $userRef = " . intval($fetchOptions['moderated']) . ')';
            }
        }

        if ($fetchOptions['expired'])
        {
            if ($fetchOptions['expired'] === true)
            {
                $states[] = "'expired'";
            }
            else
            {
                $moderatedLimit = " OR ($stateRef = 'expired' AND $userRef = " . intval($fetchOptions['expired']) . ')';
            }
        }

        if ($fetchOptions['closed'])
        {
            if ($fetchOptions['closed'] === true)
            {
                $states[] = "'closed'";
            }
            else
            {
                $moderatedLimit = " OR ($stateRef = 'closed' AND $userRef = " . intval($fetchOptions['closed']) . ')';
            }
        }

        if ($fetchOptions['completed'])
        {
            if ($fetchOptions['completed'] === true)
            {
                $states[] = "'completed'";
            }
            else
            {
                $moderatedLimit = " OR ($stateRef = 'completed' AND $userRef = " . intval($fetchOptions['completed']) . ')';
            }
        }

        if ($fetchOptions['on_hold'])
        {
            if ($fetchOptions['on_hold'] === true)
            {
                $states[] = "'on_hold'";
            }
            else
            {
                $moderatedLimit = " OR ($stateRef = 'on_hold' AND $userRef = " . intval($fetchOptions['on_hold']) . ')';
            }
        }

        return "$stateRef IN (" . implode(',', $states) . ")$pendingLimit$moderatedLimit$expiredLimit$closedLimit$completedLimit$onHoldLimit";
    }

    public function getDeletionLog(array &$classified, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForCategory($classified['category_id'], $viewingUser);

        if (!$this->hasPermission('viewDeleted', $classified, $viewingUser))
        {
            return;
        }

        $log = $this->_getDb()->fetchRow(
            'SELECT delete_date, delete_reason, delete_user_id, delete_username
            FROM kmk_deletion_log
            WHERE content_type = ?
            AND content_id = ?', array('classified', $classified['classified_id'])
        );

        if ($log)
        {
            $classified += $log;
        }
    }

    public function getAttachmentParams(array $contentData = array(), array $viewingUser = null, $tempHash = null)
    {
        if ($this->canUploadAndManageAttachment($null, $viewingUser))
        {
            return array(
                'hash' => $tempHash ? $tempHash : md5(uniqid('', true)),
                'content_type' => 'classified',
                'content_data' => $contentData
            );
        }
        else
        {
            return false;
        }
    }

    public function getClassifiedIconParams(array $contentData = array(), array $viewingUser = null, $tempHash = null)
    {
        return array(
            'hash' => $tempHash ? $tempHash : md5(uniqid('', true)),
            'hash_name' => 'classified_icon_hash',
            'content_type' => 'classified_icon',
            'content_data' => $contentData
        );
    }

    public function getGalleryImageParams(array $contentData = array(), array $viewingUser = null, $tempHash = null)
    {
        if ($this->canUploadAndManageGalleryImage())
        {
            return array(
                'hash' => $tempHash ? $tempHash : md5(uniqid('', true)),
                'hash_name' => 'gallery_hash',
                'content_type' => 'classified_gallery',
                'content_data' => $contentData
            );
        }
        else
        {
            return false;
        }
    }

    public function canUploadAndManageAttachment(&$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'uploadAttach'));
    }

    public function canUploadAndManageGalleryImage(&$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'uploadGallery'));
    }

    public function getAttachmentConstraints()
    {
        return $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentConstraints();
    }

    public function getClassifiedIconConstraints()
    {
        $attachmentConstraints = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentConstraints();
        $attachmentConstraints['extensions'] = array('jpg', 'jpeg', 'jpe', 'png', 'gif');
        $attachmentConstraints['count'] = 1;
        return $attachmentConstraints;
    }

    public function getGalleryImageConstraints()
    {
        $options = KomuKuYJB_Options::getInstance();

        return array(
            'extensions' => array('jpg', 'jpeg', 'jpe', 'png', 'gif'),
            'size' => $options->get('galleryImageSize') * 1024,
            'width' => $options->get('galleryImageDimensions', 'width'),
            'height' => $options->get('galleryImageDimensions', 'height'),
            'count' => $options->get('galleryImageCount')
        );
    }

    public function filterUnviewableClassifieds(array $classifieds, array $category = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        foreach ($classifieds AS $key => $classified)
        {
            $cat = $category ?: $classified;

            if (isset($cat['category_permission_cache']))
            {
                $categoryPermissions = XenForo_Permission::unserializePermissions($cat['category_permission_cache']);
            }
            else
            {
                $categoryPermissions = null;
            }

            if (!$this->canViewClassifiedAndContainer($classified, $cat, $null, $viewingUser, $categoryPermissions))
            {
                unset ($classifieds[$key]);
            }
        }

        return $classifieds;
    }

    public function canViewClassifiedAndContainer(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$this->getCategoryModel()->canViewCategory($category, $errorPhraseKey, $viewingUser, $categoryPermissions))
        {
            return false;
        }

        return $this->canViewClassified($classified, $category, $errorPhraseKey, $viewingUser, $categoryPermissions);
    }

    public function canViewDescriptionEditHistory(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$viewingUser)
        {
            return false;
        }

        if (!XenForo_Application::getOptions()->editHistory['enabled'])
        {
            return false;
        }

        if ($classified['user_id'] == $viewingUser['user_id'])
        {
            return true;
        }

        return $this->hasPermission('viewEditHistory', $category, $viewingUser);
    }

    public function canViewClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$this->hasPermission('view', $category, $viewingUser))
        {
            return false;
        }

        /*if ($classified['classified_state'] == 'completed')
        {
            if (!$this->hasPermission('viewClosed', $category))
            {
                if (!$viewingUser['user_id'] || $viewingUser['user_id'] != $classified['user_id'] || $viewingUser['user_id'] != @$classified['complete_user_id'])
                {
                    return false;
                }
            }
        }
        elseif ($classified['classified_state'] == 'expired' || $classified['classified_state'] == 'closed')
        {
            if (!$this->hasPermission('viewClosed', $category))
            {
                if (!$viewingUser['user_id'] || $viewingUser['user_id'] != $classified['user_id'])
                {
                    return false;
                }
            }
        }
        else*/if ($classified['classified_state'] == 'moderated' || $classified['classified_state'] == 'pending' || $classified['classified_state'] == 'on_hold')
        {
            if (!$this->hasPermission('viewModerated', $category))
            {
                if (!$viewingUser['user_id'] || $viewingUser['user_id'] != $classified['user_id'])
                {
                    return false;
                }
            }
        }
        elseif ($classified['classified_state'] == 'deleted')
        {
            if (!$this->hasPermission('viewDeleted', $category))
            {
                return false;
            }
        }

        return true;
    }

    public function canViewClassifieds(&$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        return XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'view');
    }

    public function canLikeClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if (!in_array($classified['classified_state'], array('visible', 'expired', 'closed', 'completed')))
        {
            return false;
        }

        if ($viewingUser['user_id'] == $classified['user_id'])
        {
            $errorPhraseKey = 'liking_own_content_cheating';
            return false;
        }

        return $this->hasPermission('like', $category, $viewingUser);
    }

    public function canWatchClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        return true;
    }

    public function canViewAttachment(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        return $this->hasPermission('viewAttach', $category, $viewingUser);
    }

    public function canViewGalleryImage(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        return $this->hasPermission('viewGallery', $category, $viewingUser);
    }

    public function canContactAdvertiser(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if ($classified['classified_state'] != 'visible')
        {
            $errorPhraseKey = 'this_classified_is_closed_you_cannot_contact_advertiser';
            return false;
        }

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($viewingUser['user_id'] == $classified['user_id'])
        {
            $errorPhraseKey = 'you_may_not_start_conversation_with_yourself';
            return false;
        }

        if ($this->hasPermission('contact', $category, $viewingUser))
        {
            return true;
        }

        $errorPhraseKey = array('you_may_not_start_conversation_with_x_privacy_settings', 'name' => $classified['username']);
        return false;
    }

    public function canOpenClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'closed')
        {
            return false;
        }

        return $this->hasPermission('openCloseAny', $category, $viewingUser);
    }

    public function canCloseClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'visible')
        {
            return false;
        }

        if ($this->hasPermission('openCloseAny', $category, $viewingUser))
        {
            return true;
        }

        if ($classified['user_id'] != $viewingUser['user_id'])
        {
            return false;
        }

        return $this->hasPermission('openCloseSelf', $category, $viewingUser);
    }

    public function canMarkClassifiedAsComplete(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($viewingUser['user_id'] != $classified['user_id'])
        {
            return false;
        }

        return $this->canCloseClassified($classified, $category, $errorPhraseKey, $viewingUser, $categoryPermissions);
    }

    public function canAssociateClassifiedToTraderRating(array $classified, &$errorPhraseKey, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'completed')
        {
            return false;
        }

        if ($classified['user_id'] != $viewingUser['user_id'] && $classified['complete_user_id'] != $viewingUser['user_id'])
        {
            return false;
        }

        return true;
    }

    public function canAddComment(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$category['enable_comment'])
        {
            return false;
        }

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if (!$this->canViewClassified($classified, $category, $errorPhraseKey, $viewingUser, $categoryPermissions))
        {
            return false;
        }

        return $this->hasPermission('addComment', $category, $viewingUser );
    }

    public function canAddReview(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'visible')
        {
            return false;
        }

        if ($classified['user_id'] == $viewingUser['user_id'])
        {
            return false;
        }

        return $this->hasPermission('addReview', $category, $viewingUser );
    }

    public function canEditClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] == 'closed' && !$this->canOpenClassified($classified, $category, $errorPhraseKey, $viewingUser, $categoryPermissions))
        {
            $errorPhraseKey = 'you_may_not_perform_this_action_because_classified_is_closed';
            return false;
        }

        if ($this->hasPermission('editAny', $category, $viewingUser))
        {
            return true;
        }

        if ($classified['classified_state'] == 'completed' && !$this->hasPermission('editAny', $category, $viewingUser))
        {
            return false;
        }

        if ($classified['user_id'] == $viewingUser['user_id'] && $this->hasPermission('editSelf', $category, $viewingUser))
        {
            $editLimit = $this->hasPermission('editOwnClassifiedTime', $category, $viewingUser);
            if ($editLimit != -1 && (!$editLimit || $classified['classified_date'] < XenForo_Application::$time - 60 * $editLimit))
            {
                $errorPhraseKey = array('classified_edit_time_limit_expired', 'minutes' => $editLimit);
                return false;
            }

            return true;
        }

        return false;
    }

    public function canEditTags(array $classified = null, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        if (!XenForo_Application::getOptions()->enableTagging)
        {
            return false;
        }

        $this->standardizeViewingUserReferenceForCategory($category['category_id'], $viewingUser, $categoryPermissions);

        if (!$classified || $classified['user_id'] == $viewingUser['user_id'])
        {
            if ($this->hasPermission('tagOwnClassified', $category, $viewingUser))
            {
                return true;
            }
        }

        if ($this->hasPermission('tagAnyClassified', $category, $viewingUser) || $this->hasPermission('manageAnyTag', $category, $viewingUser))
        {
            return true;
        }

        return false;
    }
 
    private function countBumpsekarang($user_id){
        $hariini = date("Y-m-d");
        return $this->_getDb()->fetchOne("SELECT COUNT(*)
            FROM `kmk_classifieds_logdaydump`
            WHERE user_id = '$user_id' AND inday = '$hariini'");
    }
    public function canBumpClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $totalangkat = $this->countBumpsekarang($viewingUser['user_id']);   
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'visible')
        {
            return false;
        }

        if ($this->hasPermission('bumpAny', $category, $viewingUser))
        {
            return true;
        }

        if ($classified['user_id'] != $viewingUser['user_id'])
        {
            return false;
        }

        $bumpLimit = $this->hasPermission('bumpOwnClassifiedTime', $category, $viewingUser);
        if ($bumpLimit == -1)
        {
            return true;
        }

        if ($bumpLimit == 0)
        {
            $errorPhraseKey = 'you_cannot_bump_this_classified';
            return false;
        }

        $bumpLimit *= 60;
        $angkatdong = 0;
        $diff = XenForo_Application::$time - $classified['last_bump_date'];
        /* lokasi :: limit bump clasified manual komuku*/
        if($viewingUser['secondary_group_ids'] == ""){
            $angkatdong = 3;
        }elseif($viewingUser['secondary_group_ids'] == "4" || $viewingUser['secondary_group_ids'] == "6"){
            $angkatdong = 1;
        }elseif($viewingUser['secondary_group_ids'] == "2" || $viewingUser['secondary_group_ids'] == "8"){
            $angkatdong = 10;
        }
        if ($diff < $bumpLimit)
        {
        
            $errorPhraseKey = array('you_have_to_wait_at_least_x_minutes_before_you_can_bump_this_classified', 'minutes' => ceil(($bumpLimit - $diff) / 60));
            return false;
        }
        if ($totalangkat >= $angkatdong)  
        {
            echo "LIMIT";
            $errorPhraseKey = array('max_up_jualan');
            return false;
        }
        
        return true;
    }

    public function canDeleteClassified(array $classified, array $category, $deleteType = 'soft', &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($deleteType == 'hard')
        {
            return $this->hasPermission('hardDeleteAny', $category, $viewingUser);
        }

        if ($classified['classified_state'] == 'completed' && !$this->hasPermission('deleteAny', $category, $viewingUser))
        {
            return false;
        }

        if ($this->hasPermission('deleteAny', $category, $viewingUser))
        {
            return true;
        }

        if ($classified['user_id'] == $viewingUser['user_id'] && $this->hasPermission('deleteSelf', $category, $viewingUser))
        {
            $editLimit = $this->hasPermission('editOwnClassifiedTime', $category, $viewingUser);
            if ($editLimit != -1 && (!$editLimit || $classified['classified_date'] < XenForo_Application::$time - 60 * $editLimit))
            {
                $errorPhraseKey = array('classified_delete_time_limit_expired', 'minutes' => $editLimit);
                return false;
            }

            return true;
        }

        return false;
    }

    public function canReassignClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        return $this->hasPermission('reassign', $category, $viewingUser);
    }

    public function canFeatureUnfeatureClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
        return ($viewingUser['user_id'] && $this->hasPermission('featureUnfeature', $category, $viewingUser));
    }

    public function canApproveClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'moderated')
        {
            return false;
        }

        return $this->hasPermission('approveUnapprove', $category, $viewingUser);
    }

    public function canUnapproveClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'visible')
        {
            return false;
        }

        return $this->hasPermission('approveUnapprove', $category, $viewingUser);
    }

    public function canUndeleteClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'deleted')
        {
            return false;
        }

        return $this->hasPermission('undelete', $category, $viewingUser);
    }

    public function canWarnClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['warning_id'] || empty($classified['user_id']))
        {
            return false;
        }

        if (!empty($classified['is_admin']) || !empty($classified['is_moderator']))
        {
            return false;
        }

        if ($classified['user_id'] == $viewingUser['user_id'])
        {
            return false;
        }

        return $this->hasPermission('warn', $category, $viewingUser);
    }

    public function canReportClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if ($classified['classified_state'] != 'visible')
        {
            return false;
        }

        return $this->getModelFromCache('XenForo_Model_User')->canReportContent($errorPhraseKey, $viewingUser);
    }

    public function canViewPreview(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$this->canViewClassified($classified, $category, $errorPhraseKey, $viewingUser, $categoryPermissions))
        {
            return false;
        }

        return (XenForo_Application::get('options')->discussionPreviewLength > 0);
    }

    public function canActivateClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if (!$viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'pending')
        {
            return false;
        }

        /** @var KomuKuYJB_Model_Package $packageModel */
        $packageModel = $this->getModelFromCache('KomuKuYJB_Model_Package');
        if (!$packageModel->verifyPackageIsUsable($classified['package_id'], $category['category_id'], $viewingUser, $package))
        {
            $errorPhraseKey = 'package_does_not_exist_any_more';
            return false;
        }

        $packageModel->preparePackage($package);
        $price = $classified['price_base_currency'];
        list ($listing, $renew) = $this->calculatePayment($price, $package);

        if (empty($listing) && empty($renew))
        {
            return false;
        }

        if ($this->hasPermission('activeAny', $category, $viewingUser))
        {
            return true;
        }

        if ($classified['user_id'] != $viewingUser['user_id'])
        {
            return false;
        }

        return true;
    }

    public function canReplyToConversations(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if ($classified['user_id'] != $viewingUser['user_id'])
        {
            return false;
        }

        return true;
    }

    public function canRenewClassified(array $classified, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        if ($classified['user_id'] != $viewingUser['user_id'])
        {
            return false;
        }

        if ($classified['classified_state'] != 'expired')
        {
            return false;
        }

        /** @var KomuKuYJB_Model_Package $packageModel */
        $packageModel = $this->getModelFromCache('KomuKuYJB_Model_Package');
        if (!$packageModel->verifyPackageIsUsable($classified['package_id'], $category['category_id'], $viewingUser, $package))
        {
            $errorPhraseKey = 'package_does_not_exist_any_more';
            return false;
        }

        $packageModel->preparePackage($package);

        if ($package['max_renewal'] == 0)
        {
            return false;
        }

        if ($package['max_renewal'] > 0 && $package['max_renewal'] <= $classified['renewal_count'])
        {
            $errorPhraseKey = array('you_have_renewed_already_classified_x_times_which_is_the_maximum_allowed_for_this_package', 'count' => $classified['renewal_count']);
            return false;
        }

        return true;
    }

    public function prepareClassifiedCustomFields(array $classified, array &$category = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $classified['customFields'] = XenForo_Helper_Php::safeUnserialize($classified['custom_classified_fields']);
        if (!$classified['customFields'])
        {
            $classified['customFields'] = array();
        }

        $classified['showExtraInfoTab'] = false;

        if (!isset($category['fieldCache']))
        {
            $category['fieldCache'] = XenForo_Helper_Php::safeUnserialize($category['field_cache']);
            if (!is_array($category['fieldCache']))
            {
                $category['fieldCache'] = array();
            }
        }

        if (!empty($category['fieldCache']['extra_tab']))
        {
            foreach ($category['fieldCache']['extra_tab'] as $fieldId)
            {
                if (isset($classified['customFields'][$fieldId]) && $classified['customFields'][$fieldId] !== '')
                {
                    $classified['showExtraInfoTab'] = true;
                    break; // no need to go any further...
                }
            }
        }

        $classified['customFieldTabs'] = array();
        if (!empty($category['fieldCache']['new_tab']))
        {
            foreach ($category['fieldCache']['new_tab'] AS $fieldId)
            {
                if (isset($classified['customFields'][$fieldId])
                    && (
                        (is_string($classified['customFields'][$fieldId]) && $classified['customFields'][$fieldId] !== '')
                        || (is_array($classified['customFields'][$fieldId]) && count($classified['customFields'][$fieldId]) > 0)
                    )
                )
                {
                    $classified['customFieldTabs'][] = $fieldId;
                }
            }
        }

        if ($classified['streetAddress'])
        {
            if (empty($category['fieldCache']['location_tab']))
            {
                $category['fieldCache']['location_tab'] = array();
            }

            if (empty($category['fieldCache']['below_title']))
            {
                $category['fieldCache']['below_title'] = array();
            }

            array_push($category['fieldCache']['below_title'], array(
                'field_id' => 'location',
                'field_type' => 'textbox',
                'field_group' => 'below_title',
                'field_value' => $classified['streetAddress']
            ));

            array_unshift($category['fieldCache']['location_tab'], array(
                'field_id' => 'streetAddress',
                'field_type' => 'textbox',
                'field_group' => 'location_tab',
                'field_value' => $classified['streetAddress']
            ));
        }

        return $classified;
    }

    public function prepareClassified(array $classified, array $category = null, array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $classified['title'] = XenForo_Helper_String::censorString($classified['title']);
        $classified['isCensored'] = true;
        $classified['price'] = floatval($classified['price']);
        $classified['isTrusted'] = (!empty($classified['user_id']) && (!empty($classified['is_admin']) || !empty($classified['is_moderator'])));
        $classified['streetAddress'] = '';

        if ($classified['likes'])
        {
            $classified['likeUsers'] = XenForo_Helper_Php::safeUnserialize($classified['like_users']);
        }

        if ($classified['user_id'] == $viewingUser['user_id'] || empty($classified['location_private']))
        {
            $classified['canViewLocation'] = true;
        }
        else
        {
            $classified['canViewLocation'] = $viewingUser['permissions']['general']['bypassUserPrivacy'] || !empty($classified['show_location']);
        }

        $classified['isClosed'] = $classified['classified_state'] == 'closed';
        $classified['isDeleted'] = $classified['classified_state'] == 'deleted';
        $classified['isModerated'] = $classified['classified_state'] == 'moderated';
        $classified['isCompleted'] = $classified['classified_state'] == 'completed';
        $classified['isOnHold'] = $classified['classified_state'] == 'on_hold';
        $classified['isPending'] = $classified['classified_state'] == 'pending';
        $classified['isExpired'] = $classified['classified_state'] == 'expired' || ($classified['expire_date'] > 0 && $classified['expire_date'] < XenForo_Application::$time);
        $classified['isExpiring'] = false; // todo: add support for 'expiring'

        $classified['tagsList'] = empty($classified['tags']) ? array() : XenForo_Helper_Php::safeUnserialize($classified['tags']);
        $classified['tagString'] = '';

        foreach ($classified['tagsList'] as $tag)
        {
            $classified['tagString'] .= $tag['tag'] . ',';
        }

        if ($category)
        {
            $classified['canView'] = $this->canViewClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canAddComment'] = $this->canAddComment($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canAddReview'] = $this->canAddReview($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canApprove'] = $this->canApproveClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canContact'] = $this->canContactAdvertiser($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canDelete'] = $this->canDeleteClassified($classified, $category, 'soft', $null, $viewingUser, $categoryPermissions);
            $classified['canEdit'] = $this->canEditClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canFeatureUnfeature'] = $this->canFeatureUnfeatureClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canLike'] = $this->canLikeClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canOpen'] = $this->canOpenClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canClose'] = $this->canCloseClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canReport'] = $this->canReportClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canUnapprove'] = $this->canUnapproveClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canUndelete'] = $this->canUndeleteClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canViewAttachments'] = $this->canViewAttachment($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canViewGalleryImages'] = $this->canViewGalleryImage($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canViewPreview'] = $this->canViewPreview($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canWarn'] = $this->canWarnClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canWatch'] = $this->canWatchClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canRenew'] = $this->canRenewClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canReassign'] = $this->canReassignClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canViewHistory'] = $this->canViewDescriptionEditHistory($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canActivate'] = $this->canActivateClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canMarkAsComplete'] = $this->canMarkClassifiedAsComplete($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canBump'] = $this->canBumpClassified($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canReplyConversations'] = $this->canReplyToConversations($classified, $category, $null, $viewingUser, $categoryPermissions);
            $classified['canEditTags'] = $this->canEditTags($classified, $category, $null, $viewingUser, $categoryPermissions);

            if (!isset($classified['canInlineMod']))
            {
                $this->addInlineModOptionToClassified($classified, $category, $viewingUser);
            }
        }
        else
        {
            $classified['canView'] = false;
            $classified['canAddComment'] = false;
            $classified['canAddReview'] = false;
            $classified['canApprove'] = false;
            $classified['canContact'] = false;
            $classified['canDelete'] = false;
            $classified['canEdit'] = false;
            $classified['canFeatureUnfeature'] = false;
            $classified['canLike'] = false;
            $classified['canOpen'] = false;
            $classified['canClose'] = false;
            $classified['canReport'] = false;
            $classified['canUnapprove'] = false;
            $classified['canUndelete'] = false;
            $classified['canViewAttachments'] = false;
            $classified['canViewGalleryImages'] = false;
            $classified['canViewPreview'] = false;
            $classified['canWarn'] = false;
            $classified['canWatch'] = false;
            $classified['canInlineMod'] = false;
            $classified['canRenew'] = false;
            $classified['canReassign'] = false;
            $classified['canViewHistory'] = false;
            $classified['canActivate'] = false;
            $classified['canMarkAsComplete'] = false;
            $classified['canBump'] = false;
            $classified['canReplyConversations'] = false;
            $classified['canEditTags'] = false;
        }

        if (!empty($classified['latitude']))
        {
            $classified['streetAddress'] = $this->getLocationStreetAddress($classified);
        }

        return $classified;
    }

    public function getLocationStreetAddress(array $location, $bypassPrivacy = false)
    {
        $pieces = array();
        $return = '';

        if (!empty($location['route']))
        {
            $pieces[] = $location['route'];
        }

        if (!empty($location['neighborhood']))
        {
            $pieces[] = $location['neighborhood'];
        }

        if (!empty($location['sublocality_level_1']))
        {
            $pieces[] = $location['sublocality_level_1'];
        }

        if (!empty($location['locality']))
        {
            $pieces[] = $location['locality'];
        }
        elseif (!empty($location['administrative_area_level_2']))
        {
            $pieces[] = $location['administrative_area_level_2'];
        }
        elseif (!empty($location['administrative_area_level_1']))
        {
            $pieces[] = $location['administrative_area_level_1'];
        }

        $pieces[] = GFNCore_Helper_Country::getCountryByCode($location['country']);
        $i = 0;

        if (!$bypassPrivacy && empty($location['canViewLocation']))
        {
            return (string) new XenForo_Phrase('x_comma_y', array('y' => end($pieces), 'x' => prev($pieces)));
        }

        while ($i < count($pieces))
        {
            $x = $return ?: $pieces[$i++];
            $y = $pieces[$i++];

            $return = (string) new XenForo_Phrase('x_comma_y', array('x' => $x, 'y' => $y));
        }

        return $return;
    }

    public function prepareClassifieds(array $classifieds, array $category = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        foreach ($classifieds as &$classified)
        {
            if ($category === null && isset($classified['category_title']))
            {
                $classified = $this->prepareClassified($classified, $classified, $viewingUser);
            }
            else
            {
                $classified = $this->prepareClassified($classified, $category, $viewingUser);
            }
        }

        return $classifieds;
    }

    public function standardizeViewingUserReferenceForCategory($categoryId, array &$viewingUser = null, array &$categoryPermissions = null)
    {
        $this->getCategoryModel()->standardizeViewingUserReferenceForCategory($categoryId, $viewingUser, $categoryPermissions);
    }

    public function getFeaturedImagePath($classifiedId, $externalDataPath = null)
    {
        if ($externalDataPath === null)
        {
            $externalDataPath = XenForo_Helper_File::getExternalDataPath();
        }

        return sprintf('%s/classifieds/icons/%d/%d.jpg',
            $externalDataPath,
            floor($classifiedId / 1000),
            $classifiedId
        );
    }

    public function batchUpdateLikeUser($oldUserId, $newUserId, $oldUsername, $newUsername)
    {
        $db = $this->_getDb();

        $db->query(
            'UPDATE (
				SELECT content_id FROM kmk_liked_content
				WHERE content_type = \'classified\'
				AND like_user_id = ?
			) AS temp
			INNER JOIN kmk_classifieds_classified AS classified ON (classified.classified_id = temp.content_id)
			SET like_users = REPLACE(like_users, ' .
            $db->quote('i:' . $oldUserId . ';s:8:"username";s:' . strlen($oldUsername) . ':"' . $oldUsername . '";') . ', ' .
            $db->quote('i:' . $newUserId . ';s:8:"username";s:' . strlen($newUsername) . ':"' . $newUsername . '";') . ')', $newUserId
        );
    }

    public function insertUploadedClassifiedIconData(XenForo_Upload $file, $userId, array $extra = array())
    {
        if (!$file->isImage() || !XenForo_Image_Abstract::canResize($file->getImageInfoField('width'), $file->getImageInfoField('height')))
        {
            throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image_or_image_too_big_to_process'), true);
        }

        $dimensions = array(
            'width' => $file->getImageInfoField('width'),
            'height' => $file->getImageInfoField('height'),
        );

        $image = XenForo_Image_Abstract::createFromFile($file->getTempFile(), $file->getImageInfoField('type'));
        if ($image)
        {
            $image->thumbnailFixedShorterSide(KomuKuYJB_Options::getInstance()->get('iconDimensions'));
            $dimensions['width'] = $image->getWidth();
            $dimensions['height'] = $image->getHeight();

            switch ($image->getOrientation())
            {
                case XenForo_Image_Abstract::ORIENTATION_LANDSCAPE:
                    $diff = floor(($dimensions['width'] - $dimensions['height']) / 2);
                    $image->crop($diff, 0, $dimensions['height'], $dimensions['height']);
                    break;

                case XenForo_Image_Abstract::ORIENTATION_PORTRAIT:
                    $diff = floor(($dimensions['height'] - $dimensions['width']) / 2);
                    $image->crop(0, $diff, $dimensions['width'], $dimensions['width']);
                    break;
            }

            $dimensions['thumbnail_width'] = $dimensions['width'] = $image->getWidth();
            $dimensions['thumbnail_height'] = $dimensions['height'] = $image->getHeight();

            $image->output($file->getImageInfoField('type'), $file->getTempFile(), 100);
            $tempThumbFile = tempnam(XenForo_Helper_File::getInternalDataPath(), 'gfnc');
            @copy($file->getTempFile(), $tempThumbFile);
            unset ($image);
        }

        try
        {
            /** @var XenForo_DataWriter_AttachmentData $writer */
            $writer = XenForo_DataWriter::create('XenForo_DataWriter_AttachmentData');
            $writer->bulkSet($extra);
            $writer->set('user_id', $userId);
            $writer->set('filename', $file->getFileName());
            $writer->bulkSet($dimensions);
            $writer->setExtraData($writer::DATA_TEMP_FILE, $file->getTempFile());
            if (isset($tempThumbFile))
            {
                $writer->setExtraData($writer::DATA_TEMP_THUMB_FILE, $tempThumbFile);
            }
            $writer->save();
        }
        catch (Exception $e)
        {
            if (isset($tempThumbFile))
            {
                @unlink($tempThumbFile);
            }

            throw $e;
        }

        if (isset($tempThumbFile))
        {
            @unlink($tempThumbFile);
        }

        return $writer->get('data_id');
    }

    public function insertUploadedGalleryImageData(XenForo_Upload $file, $userId, array $extra = array())
    {
        if (!$file->isImage() || !XenForo_Image_Abstract::canResize($file->getImageInfoField('width'), $file->getImageInfoField('height')))
        {
            throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image_or_image_too_big_to_process'), true);
        }

        $options = KomuKuYJB_Options::getInstance();

        $dimensions = array(
            'width' => $file->getImageInfoField('width'),
            'height' => $file->getImageInfoField('height'),
        );

        $tempThumbFile = tempnam(XenForo_Helper_File::getTempDir(), 'gfnc');
        if ($tempThumbFile)
        {
            $image = XenForo_Image_Abstract::createFromFile($file->getTempFile(), $file->getImageInfoField('type'));
            if ($image)
            {
                if ($image->thumbnail($options->get('galleryThumbnailDimensions')))
                {
                    $image->output($file->getImageInfoField('type'), $tempThumbFile);
                }
                else
                {
                    copy($file->getTempFile(), $tempThumbFile);
                }

                $dimensions['thumbnail_width'] = $image->getWidth();
                $dimensions['thumbnail_height'] = $image->getHeight();

                unset ($image);
            }
        }

        $tempSlideFile = tempnam(XenForo_Helper_File::getTempDir(), 'gfnc');
        if ($tempSlideFile)
        {
            $image = XenForo_Image_Abstract::createFromFile($file->getTempFile(), $file->getImageInfoField('type'));
            if ($image)
            {
                if ($image->thumbnail($options->get('gallerySlideDimensions')))
                {
                    $image->output($file->getImageInfoField('type'), $tempSlideFile);
                }
                else
                {
                    copy($file->getTempFile(), $tempSlideFile);
                }

                $dimensions['slide_width'] = $image->getWidth();
                $dimensions['slide_height'] = $image->getHeight();

                unset ($image);
            }
        }

        if ($options->get('galleryImageDimensions', 'width') || ($options->get('galleryImageDimensions', 'width') && $options->get('galleryImageDimensions', 'height')))
        {
            $image = XenForo_Image_Abstract::createFromFile($file->getTempFile(), $file->getImageInfoField('type'));
            if ($image)
            {
                if ($image->thumbnail($options->get('galleryImageDimensions', 'width'), $options->get('galleryImageDimensions', 'height')))
                {
                    $image->output($file->getImageInfoField('type'), $file->getTempFile());
                }

                $dimensions['width'] = $image->getWidth();
                $dimensions['height'] = $image->getHeight();

                unset ($image);
            }
        }

        try
        {
            /** @var KomuKuYJB_Extend_XenForo_DataWriter_AttachmentData $writer */
            $writer = XenForo_DataWriter::create('XenForo_DataWriter_AttachmentData');
            $writer->bulkSet($extra);
            $writer->set('user_id', $userId);
            $writer->set('filename', $file->getFileName());
            $writer->bulkSet($dimensions);
            $writer->setExtraData($writer::DATA_TEMP_FILE, $file->getTempFile());
            $writer->setExtraData($writer::DATA_TEMP_SLIDE_FILE, $tempSlideFile);
            $writer->setExtraData($writer::DATA_TEMP_THUMB_FILE, $tempThumbFile);
            $writer->save();
        }
        catch (Exception $e)
        {
            @unlink($tempThumbFile);
            @unlink($tempSlideFile);

            throw $e;
        }

        @unlink($tempThumbFile);
        @unlink($tempSlideFile);

        return $writer->get('data_id');
    }

    public function getGallerySlideImagePath(array $data, $externalDataPath = null)
    {
        if ($externalDataPath === null)
        {
            $externalDataPath = XenForo_Helper_File::getExternalDataPath();
        }

        return sprintf('%s/classifieds/galleries/%d/%d-%s.jpg',
            $externalDataPath,
            floor($data['data_id'] / 1000),
            $data['data_id'],
            $data['file_hash']
        );
    }

    public function getGallerySlideImageUrl(array $data)
    {
        return sprintf('%s/classifieds/galleries/%d/%d-%s.jpg',
            XenForo_Application::$externalDataUrl,
            floor($data['data_id'] / 1000),
            $data['data_id'],
            $data['file_hash']
        );
    }

    public function getAttachments($classifiedId)
    {
        return $this->fetchAllKeyed(
            'SELECT attachment.*,
            ' . XenForo_Model_Attachment::$dataColumns . '
            FROM kmk_attachment AS attachment
            INNER JOIN kmk_attachment_data AS data
                ON (data.data_id = attachment.data_id)
            WHERE content_id = ?
            AND content_type IN (' . $this->_getDb()->quote(array('classified', 'classified_icon','classified_gallery')) . ')
            ORDER BY attachment.attach_date', 'attachment_id', $classifiedId
        );
    }

    public function logClassifiedView($classifiedId)
    {
        $this->_getDb()->query(
            'INSERT ' . (XenForo_Application::get('options')->enableInsertDelayed ? 'DELAYED ' : '') . 'INTO kmk_classifieds_classified_view
                (classified_id)
            VALUES
                (?)', $classifiedId
        );
    }

    public function updateClassifiedView()
    {
        $db = $this->_getDb();

        $db->query('
			UPDATE kmk_classifieds_classified
			INNER JOIN (
				SELECT classified_id, COUNT(*) AS total
				FROM kmk_classifieds_classified_view
				GROUP BY classified_id
			) AS kmk_cv ON (kmk_cv.classified_id = kmk_classifieds_classified.classified_id)
			SET kmk_classifieds_classified.view_count = kmk_classifieds_classified.view_count + kmk_cv.total
		');

        $db->query('TRUNCATE TABLE kmk_classifieds_classified_view');
    }

    public function getInlineModOptionsForClassifieds(array $classifieds, array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);
        $inlineModOptions = array();

        foreach ($classifieds as $classified)
        {
            $inlineModOptions += $this->addInlineModOptionToClassified($classified, $classified, $viewingUser);
        }

        return $inlineModOptions;
    }

    public function addInlineModOptionToClassified(array &$classified, array $category, array $viewingUser = null, array $categoryPermissions = null)
    {
        $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

        $modOptions = array();
        $canInlineMod = ($viewingUser['user_id'] && (
                XenForo_Permission::hasContentPermission($categoryPermissions, 'openCloseAny')
                || XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteAny')
                || XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')
                || XenForo_Permission::hasContentPermission($categoryPermissions, 'undelete')
                || XenForo_Permission::hasContentPermission($categoryPermissions, 'approveUnapprove')
                || XenForo_Permission::hasContentPermission($categoryPermissions, 'reassign')
                || XenForo_Permission::hasContentPermission($categoryPermissions, 'featureUnfeature')
            ));

        if ($canInlineMod)
        {
            if ($this->canDeleteClassified($classified, $category, 'soft', $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['delete'] = true;
            }

            if ($this->canUndeleteClassified($classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['undelete'] = true;
            }

            if ($this->canOpenClassified($classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['open'] = true;
            }

            if ($this->canCloseClassified($classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['close'] = true;
            }

            if ($this->canApproveClassified($classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['approve'] = true;
            }

            if ($this->canUnapproveClassified($classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['unapprove'] = true;
            }

            if ($this->canReassignClassified($classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['reassign'] = true;
            }

            if ($this->canFeatureUnfeatureClassified($classified, $category, $null, $viewingUser, $categoryPermissions))
            {
                $modOptions['feature'] = true;
                $modOptions['unfeature'] = true;
            }

            if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny'))
            {
                $modOptions['move'] = true;
                $modOptions['edit'] = true;
            }
        }

        $classified['canInlineMod'] = count($modOptions) > 0;
        return $modOptions;
    }

    public function hasPermission($permission, $category, $viewingUser = null)
    {
        return $this->getCategoryModel()->hasPermission($permission, $category, $viewingUser);
    }

    public function deleteClassified($classifiedId, $deleteType, array $options = array())
    {
        $options = array_merge(array(
            'reason' => '',
            'authorAlert' => false,
            'authorAlertReason' => ''
        ), $options);

        /** @var KomuKuYJB_DataWriter_Classified $writer */
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        $writer->setExistingData($classifiedId);

        if ($deleteType == 'hard')
        {
            $writer->delete();
        }
        else
        {
            $writer->setExtraData($writer::DATA_DELETE_REASON, $options['reason']);
            $writer->set('classified_state', 'deleted');
            $writer->save();
        }

        if ($options['authorAlert'])
        {

        }
    }

    public function publishLocation($conversationId)
    {
        return $this->_getDb()->query(
            'UPDATE kmk_classifieds_conversation
            SET show_location = 1
            WHERE conversation_id = ?
            AND show_location = 0', $conversationId
        )->rowCount();
    }

    public function featureClassified(array $classified, $featureDate = null)
    {
        $db = $this->_getDb();

        if ($featureDate === null)
        {
            $featureDate = XenForo_Application::$time;
        }

        XenForo_Db::beginTransaction($db);

        $stmt = $db->query(
            'UPDATE kmk_classifieds_classified
            SET feature_date = ?
            WHERE classified_id = ?
            AND feature_date = 0', array($featureDate, $classified['classified_id'])
        );

        if ($stmt->rowCount() == 1 && $classified['classified_state'] == 'visible')
        {
            $category = new KomuKuYJB_Eloquent_Category($classified['category_id']);
            if (!$category->writer()->isUpdate())
            {
                return false;
            }

            $category->writer()->updateFeaturedCount(1);
            $category->save();

            XenForo_Model_Log::logModeratorAction('classified', $classified, 'feature');
        }

        XenForo_Db::commit($db);

        return true;
    }

    public function unfeatureClassified(array $classified)
    {
        $db = $this->_getDb();

        XenForo_Db::beginTransaction($db);

        $stmt = $db->query(
            'UPDATE kmk_classifieds_classified
            SET feature_date = 0
            WHERE classified_id = ?
            AND feature_date > 0', $classified['classified_id']
        );

        if ($stmt->rowCount() == 1 && $classified['classified_state'] == 'visible')
        {
            $category = new KomuKuYJB_Eloquent_Category($classified['category_id']);
            if (!$category->writer()->isUpdate())
            {
                return false;
            }

            $category->writer()->updateFeaturedCount(-1);
            $category->save();

            XenForo_Model_Log::logModeratorAction('classified', $classified, 'unfeature');
        }

        XenForo_Db::commit($db);

        return true;
    }

    public function bumpClassified(array $classified,$user_id)
    {
        $db = $this->_getDb();
        if ($classified['classified_state'] != 'visible')
        {
            return;
        }

        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        $writer->setExistingData($classified);
        $writer->set('last_bump_date', XenForo_Application::$time);
        $writer->save();
        $hariini = date('Y-m-d');
        $db->query("INSERT INTO kmk_classifieds_logdaydump(user_id,inday) VALUES ('$user_id','$hariini')");
    }

    public function getAvailableCountryCodesForSearch(array $classifiedIds)
    {
        return $this->_getDb()->fetchCol(
            'SELECT DISTINCT country
            FROM kmk_classifieds_classified_location AS location
            INNER JOIN kmk_classifieds_classified AS classified
              ON (classified.classified_id = location.classified_id AND classified.category_id IN (' . $this->_getDb()->quote($classifiedIds) . '))'
        );
    }

    public function calculatePayment($price, array $package)
    {
        switch ($package['price_format'])
        {
            case 'flat':
                $listing = floatval($package['price_rate']['listing']);
                $renewal = floatval($package['price_rate']['renewal']);
                break;

            case 'percentile':
                $listing = floatval($price * $package['price_rate']['listing'] / 100);
                $renewal = floatval($price * $package['price_rate']['renewal'] / 100);
                break;

            case 'flexible':
                $listing = 0;
                $renewal = 0;

                $rates = array_reverse($package['price_rate']);
                foreach ($rates as $rate)
                {
                    if ($price >= $rate['item_price'])
                    {
                        $listing = floatval($rate['listing']);
                        $renewal = floatval($rate['renewal']);
                        break;
                    }
                }
                break;

            default:
                throw new XenForo_Exception(new XenForo_Phrase('invalid_price_format_for_pacakge'));
        }

        return array($listing, $renewal);
    }

    public function getCategoryIdsFromClassifieds(array $classifieds)
    {
        $return = array();

        foreach ($classifieds as $classified)
        {
            $return[] = $classified['category_id'];
        }

        return array_unique($return);
    }

    public function getPermissionBasedFetchConditions(array $category = null, array $viewingUser = null, array $categoryPermissions = null)
    {
        if ($category)
        {
            $this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
            $viewAllModerated = XenForo_Permission::hasContentPermission($categoryPermissions, 'viewModerated');
            $viewAllPending = XenForo_Permission::hasContentPermission($categoryPermissions, 'viewPending');
            $viewAllDeleted = XenForo_Permission::hasContentPermission($categoryPermissions, 'viewDeleted');
        }
        else
        {
            $this->standardizeViewingUserReference($viewingUser);
            $viewAllModerated = XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'viewModerated');
            $viewAllPending = XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'viewPending');
            $viewAllDeleted = XenForo_Permission::hasPermission($viewingUser['permissions'], 'classifieds', 'viewDeleted');
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

        if ($viewAllPending)
        {
            $viewPending = true;
        }
        elseif ($viewingUser['user_id'])
        {
            $viewPending = $viewingUser['user_id'];
        }
        else
        {
            $viewPending = false;
        }

        $conditions = array(
            'visible' => true,
            'deleted' => $viewAllDeleted,
            'moderated' => $viewModerated,
            'pending' => $viewPending,
            'on_hold' => $viewModerated,
            'expired' => true,
            'completed' => true,
            'closed' => true
        );

        return $conditions;
    }

    public function getClassifiedTotalItemCounts()
    {
        $return = $this->_getDb()->fetchRow(
            "SELECT SUM(comment_count) AS comments, SUM(view_count) AS views
            FROM kmk_classifieds_classified
            WHERE classified_state NOT IN ('moderated', 'deleted', 'pending')"
        );

        $return += $this->_getDb()->fetchRow(
            "SELECT SUM(price_base_currency) AS revenue, COUNT(*) AS completed
            FROM kmk_classifieds_classified
            WHERE classified_state = 'completed'"
        );

        $return['adverts'] = $this->_getDb()->fetchOne(
            "SELECT COUNT(*)
            FROM kmk_classifieds_classified
            WHERE classified_state = 'visible'"
        );

        return $return;
    }

    public function getOrderPhrases()
    {
        return array(
            'bump_date' => new XenForo_Phrase('recently_bumped'),
            'item_date' => new XenForo_Phrase('submission_date'),
            'expiring' => new XenForo_Phrase('expire_date'),
            'price' => new XenForo_Phrase('price'),
            'views' => new XenForo_Phrase('classifieds_views'),
            'username' => new XenForo_Phrase('advertiser_name'),
            'title' => new XenForo_Phrase('title'),
        );
    }

    public function getOrderOptions($selectedOrder, $defaultOrder)
    {
        $options = array();

        foreach ($this->getOrderPhrases() as $order => $title)
        {
            $options[] = array(
                'title' => $title,
                'value' => $order,
                'selected' => $selectedOrder == $order,
                'paramOverwrite' => $order == $defaultOrder ? null : $order
            );
        }

        return $options;
    }

    public function markClassifiedAsCompleted($classifiedId, $userId, $username)
    {
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Classified');
        $writer->setExistingData($classifiedId);

        $writer->set('classified_state', 'completed');
        $writer->set('complete_user_id', $userId);
        $writer->set('complete_username', $username);
        $writer->set('complete_date', XenForo_Application::$time);

        $writer->save();

        KomuKuYJB_Model_NewsFeed::publish(
            'classified_complete', $writer->getMergedData()
        );
    }

    public function rebuildCount()
    {
        @set_time_limit(0);

        foreach ($this->getCategoryModel()->getAllCategories() as $category)
        {
            $category = new KomuKuYJB_Eloquent_Category($category);
            $category->writer()->updateClassifiedCount();
            $category->writer()->updateFeaturedCount();
        }

        $db = $this->_getDb();

        $userIds = $db->fetchPairs(
            'SELECT user_id, COUNT(*) AS count
            FROM kmk_classifieds_classified
            WHERE classified_state = ?
            GROUP BY user_id', array('visible')
        );

        $db->update('kmk_classifieds_trader', array('classified_count' => 0));

        foreach ($userIds as $userId => $count)
        {
            $db->update('kmk_classifieds_trader', array('classified_count' => $count), 'user_id = ' . $db->quote($userId));
        }
    }

    /**
     * @return KomuKuYJB_Model_Category
     */
    public function getCategoryModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Category');
    }
}