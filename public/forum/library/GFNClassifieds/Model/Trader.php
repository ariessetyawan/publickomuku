<?php /*247ed9d505d14efe3b680e7a1d9b7f9b76a0b23e*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 4
 * @since      1.0.0 RC 4
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_Trader extends XenForo_Model
{
    const FETCH_USER_PROFILE        = 0x01;
    const FETCH_LAST_ACTIVITY       = 0x02;

    public function getTraderById($userId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareTraderFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow(
            'SELECT trader.*, user.*, user_profile.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_trader AS trader
            INNER JOIN kmk_user AS user
              ON (user.user_id = trader.user_id)
            INNER JOIN kmk_user_profile AS user_profile
              ON (user_profile.user_id = trader.user_id)
            ' . $joinOptions['joinTables'] . '
            WHERE user_id = ?', $userId
        );
    }

    public function getTraders(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->prepareTraderConditions($conditions, $fetchOptions);
        $orderClause = $this->prepareTraderOrderOptions($fetchOptions);
        $joinOptions = $this->prepareTraderFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults(
            'SELECT trader.*, user.*, user_profile.*
            ' . $joinOptions['selectFields'] . '
            FROM kmk_classifieds_trader AS trader
            INNER JOIN kmk_user AS user
              ON (user.user_id = trader.user_id)
            INNER JOIN kmk_user_profile AS user_profile
              ON (user_profile.user_id = trader.user_id)
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause . '
            ' . $orderClause . '
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'user_id');
    }

    public function prepareTraderConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (isset($conditions['rating_count']))
        {
            $sqlConditions[] = $this->getCutOffCondition('trader.rating_count', $conditions['rating_count']);
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareTraderFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $db = $this->_getDb();

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_USER_PROFILE)
            {
                $selectFields .= ', user_profile.*';
                $joinTables .= '
                    LEFT JOIN kmk_user_profile AS user_profile
                        ON (user_profile.* = trader.user_id';
            }

            if ($fetchOptions['join'] & self::FETCH_LAST_ACTIVITY)
            {
                $selectFields .= ', IF (session_activity.view_date IS NULL, user.last_activity, session_activity.view_date) AS effective_last_activity,
					session_activity.view_date, session_activity.controller_name, session_activity.controller_action, session_activity.params, session_activity.ip';
                $joinTables .= '
					LEFT JOIN kmk_session_activity AS session_activity ON
						(session_activity.user_id = advertiser.user_id AND session_activity.unique_key = advertiser.user_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables
        );
    }

    public function getTotalClassifiedCounts(array $traderIds)
    {
        return $this->_getDb()->fetchPairs(
            'SELECT user_id, COUNT(*)
            FROM kmk_classifieds_classified
            WHERE classified_state NOT IN (\'moderated\', \'deleted\')
            AND user_id IN (' . $this->_getDb()->quote($traderIds) . ')
            GROUP BY user_id'
        );
    }

    public function prepareTraderOrderOptions(array $fetchOptions, $defaultOrderSql = '')
    {
        $choices = array(
            'weighted' => 'trader.rating_weighted',
            'username' => 'user.username'
        );

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    public function recalculateConversationStates($userId)
    {
        $this->_getDb()->update('kmk_classifieds_trader', array(
            'response_time' => $this->getAverageResponseTime($userId),
            'response_percentage' => $this->getResponsePercentage($userId)
        ), 'user_id = ' . $this->_getDb()->quote($userId));
    }

    public function getResponsePercentage($userId)
    {
        if (is_array($userId))
        {
            $userId = $userId['user_id'];
        }

        $conversations = $this->_getDb()->fetchAll(
            'SELECT conversation.conversation_id, IF(message.message_id IS NULL, 0, 1) AS has_reply
            FROM kmk_classifieds_conversation AS conversation
            INNER JOIN kmk_classifieds_classified AS classified
              ON (classified.classified_id = conversation.classified_id)
            INNER JOIN kmk_conversation_master AS master
              ON (master.conversation_id = conversation.conversation_id)
            LEFT JOIN kmk_conversation_message AS message
              ON (message.conversation_id = conversation.conversation_id AND message.user_id = classified.user_id)
            WHERE classified.user_id = ?
            GROUP BY master.conversation_id', $userId
        );

        if (!$conversations)
        {
            return 0;
        }

        $total = $count = 0;

        foreach ($conversations as $conversation)
        {
            $total++;

            if ($conversation['has_reply'])
            {
                $count++;
            }
        }

        if (!$count)
        {
            return 0;
        }

        return ($count / $total) * 100;
    }

    public function getAverageResponseTime($userId)
    {
        if (is_array($userId))
        {
            $userId = $userId['user_id'];
        }

        $conversations = $this->_getDb()->fetchAll(
            'SELECT conversation.conversation_id, master.start_date, master.first_message_id, classified.user_id AS advertiser_id
            FROM kmk_classifieds_conversation AS conversation
            INNER JOIN kmk_classifieds_classified AS classified
            ON (classified.classified_id = conversation.classified_id)
            INNER JOIN kmk_conversation_master AS master
            ON (master.conversation_id = conversation.conversation_id)
            WHERE classified.user_id = ?
            AND master.reply_count > 0
            ORDER BY conversation.conversation_id DESC
            LIMIT 15', $userId
        );

        if (!$conversations)
        {
            return 0;
        }

        $total = $count = 0;

        foreach ($conversations as $i => $conversation)
        {
            $next = $this->_getDb()->fetchOne(
                'SELECT message_date
                FROM kmk_conversation_message
                WHERE conversation_id = ?
                AND user_id = ?
                AND message_id > ?
                ORDER BY message_id ASC
                LIMIT 1', array($conversation['conversation_id'], $conversation['advertiser_id'], $conversation['first_message_id'])
            );

            if (!$next)
            {
                continue;
            }

            $count++;
            $total += (intval($next) - intval($conversation['start_date']));
        }

        if (!$count)
        {
            return 0;
        }

        return $total / $count;
    }

    public function optedInTradersWatchClassified(array $classified)
    {
        $userStates = $this->_getDb()->fetchPairs(
            'SELECT user_id, all_classified_watch_state
            FROM kmk_user_option
            WHERE all_classified_watch_state != \'\'
            AND user_id != ?', $classified['user_id']
        );

        if (!$userStates)
        {
            return;
        }

        /** @var GFNClassifieds_Model_ClassifiedWatch $model */
        $model = $this->getModelFromCache('GFNClassifieds_Model_ClassifiedWatch');

        foreach ($userStates as $userId => $state)
        {
            $model->setClassifiedWatchState($userId, $classified['classified_id'], $state);
        }
    }
}