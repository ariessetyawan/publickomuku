<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_Model_Ratings extends XenForo_Model
{
    const FETCH_FROM_USER = 0x01;
    const FETCH_TO_USER = 0x02;
    const FETCH_FULL_FROM_USER = 0x04;
    const FETCH_FULL_TO_USER = 0x08;
    const FETCH_BOTH_FULL_USERS = 0x0c;

    /**
     * Gets the specified user rating.
     *
     * @param int $id
     *
     * @return array|false
     */
    public function getRatingById($ratingId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareRatingFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow('
			SELECT rating.*
				'.$joinOptions['selectFields'].'
			FROM kmk_user_ratings AS rating
				'.$joinOptions['joinTables'].'
			WHERE rating_id = ?
		', $ratingId);
    }

    /**
     * Gets the specified user ratings.
     *
     * @param int $id
     *
     * @return array|false
     */
    public function getRatingByIds(array $ratingIds)
    {
        if (!$ratingIds) {
            return array();
        }

        $joinOptions = $this->prepareRatingFetchOptions(array('join' => self::FETCH_BOTH_FULL_USERS));

        return $this->fetchAllKeyed('
			SELECT rating.*
				'.$joinOptions['selectFields'].'
			FROM kmk_user_ratings AS rating
				'.$joinOptions['joinTables'].'
			WHERE rating.rating_id IN ('.$this->_getDb()->quote($ratingIds).')
		', 'rating_id');
    }

    /**
     * Fetch ratings based on the conditions and options specified.
     *
     * @param array $conditions
     * @param array $fetchOptions
     *
     * @return array
     */
    public function getRatings(array $conditions = array(), array $fetchOptions = array(), $prepare = true)
    {
        if (isset($fetchOptions['limit']) && $fetchOptions['limit'] == 0) {
            return array();
        }

        $whereClause = $this->prepareRatingConditions($conditions);
        $orderClause = $this->prepareUserOrderOptions($fetchOptions, 'rating.rating_date');
        $joinOptions = $this->prepareRatingFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        $return = $this->fetchAllKeyed($this->limitQueryResults(
            '
                SELECT rating.*
                    '.$joinOptions['selectFields'].'
                FROM kmk_user_ratings AS rating
                    '.$joinOptions['joinTables'].'
                WHERE '.$whereClause.'
                '.$orderClause.'
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'rating_id');

        return $prepare ? $this->prepareRating($return) : $return;
    }

    /**
     * Gets the count of ratings that match the specified conditions.
     *
     * @param array $conditions
     *
     * @return int
     */
    public function countRatings(array $conditions = array())
    {
        $fetchOptions = array();

        $whereClause = $this->prepareRatingConditions($conditions, $fetchOptions);

        return $this->_getDb()->fetchOne('
            SELECT COUNT(*)
            FROM kmk_user_ratings AS rating
            WHERE '.$whereClause
        );
    }

    /**
     * Prepares join-related fetch options.
     *
     * @param array $fetchOptions
     *
     * @return array Containing 'selectFields' and 'joinTables' keys.
     */
    public function prepareRatingFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';

        if (!empty($fetchOptions['join'])) {
            if ($fetchOptions['join'] & self::FETCH_FULL_FROM_USER) {
                $selectFields .= ',
                    IFNULL(user.username, rating.from_username) AS fromUserUsername,
                    fromUser.positive AS fromUserPositive, fromUser.neutral AS fromUserNeutral,
                    fromUser.negative AS fromUserNegative, fromUser.total AS fromUserTotal, fromUser.rating AS fromUserRating,
                    user.gravatar AS fromUserGravatar, user.avatar_date AS fromUserAvatarDate';

                $joinTables .= '
                    LEFT JOIN kmk_user_ratings_stats AS fromUser ON (fromUser.user_id = rating.from_user_id)
                    LEFT JOIN kmk_user AS user ON (user.user_id = rating.from_user_id)';
            } elseif ($fetchOptions['join'] & self::FETCH_FROM_USER) {
                $selectFields .= ',
                    fromUser.positive AS fromUserPositive, fromUser.neutral AS fromUserNeutral,
                    fromUser.negative AS fromUserNegative, fromUser.total AS fromUserTotal, fromUser.rating AS fromUserRating';

                $joinTables .= '
                    LEFT JOIN kmk_user_ratings_stats AS fromUser ON (fromUser.user_id = rating.from_user_id)';
            }

            if ($fetchOptions['join'] & self::FETCH_FULL_TO_USER) {
                $selectFields .= ',
                    IFNULL(toXfUser.username, rating.to_username) AS toUserUsername,
                    toUser.positive AS toUserPositive, toUser.neutral AS toUserNeutral,
                    toUser.negative AS toUserNegative, toUser.total AS toUserTotal, toUser.rating AS toUserRating,
                    toXfUser.gravatar AS toUserGravatar, toXfUser.avatar_date AS toUserAvatarDate';

                $joinTables .= '
                    LEFT JOIN kmk_user_ratings_stats AS toUser ON (toUser.user_id = rating.to_user_id)
                    LEFT JOIN kmk_user AS toXfUser ON (toXfUser.user_id = rating.to_user_id)';
            } elseif ($fetchOptions['join'] & self::FETCH_TO_USER) {
                $selectFields .= ',
                    toUser.positive AS toUserPositive, toUser.neutral AS toUserNeutral,
                    toUser.negative AS toUserNegative, toUser.total AS toUserTotal, toUser.rating AS toUserRating';

                $joinTables .= '
                    LEFT JOIN kmk_user_ratings_stats AS toUser ON (toUser.user_id = rating.to_user_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables,
        );
    }

    /**
     * Prepares a set of conditions to select ratings against.
     *
     * @param array $conditions   List of conditions. (TODO: make list)
     * @param array $fetchOptions The fetch options that have been provided. May be edited if criteria requires.
     *
     * @return string Criteria as SQL for where clause
     */
    public function prepareRatingConditions(array $conditions)
    {
        $db = $this->_getDb();
        $sqlConditions = array();

        if (!empty($conditions['from_user'])) {
            $sqlConditions[] = 'rating.from_user_id = '.$db->quote($conditions['from_user']);
        }

        if (!empty($conditions['to_user'])) {
            $sqlConditions[] = 'rating.to_user_id = '.$db->quote($conditions['to_user']);
        }

        if (!empty($conditions['rating']) && in_array($conditions['rating'], array(-1, 0, 1))) {
            $sqlConditions[] = 'rating.rating = '.$db->quote($conditions['from_user']);
        }

        if (isset($conditions['active'])) {
            if (isset($conditions['active']) && $conditions['active'] == 0) {
                $sqlConditions[] = 'rating.active = 0';
            } elseif (isset($conditions['active']) && $conditions['active'] == 1) {
                $sqlConditions[] = 'rating.active = 1';
            } else {
                $sqlConditions[] = $this->prepareStateLimitFromConditions($conditions, 'rating', 'active');
            }
        } else {
            $sqlConditions[] = 'rating.active = 1';
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    /**
     * Construct 'ORDER BY' clause.
     *
     * @param array  $fetchOptions    (uses 'order' key)
     * @param string $defaultOrderSql Default order SQL
     *
     * @return string
     */
    public function prepareUserOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
    {
        $choices = array(
            'rating_date' => 'rating.rating_date',
        );

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    /**
     * Prepares a user rating record for display. 
     *
     * @param array $rating info
     *
     * @return array Prepared rating info
     */
    public function prepareRating(array $rating)
    {
        $return = array();
        foreach ($rating as $k => $r) {
            switch ($r['rating']) {
                case -1: $rating = 'negative'; break;
                case 0: $rating = 'neutral'; break;
                case 1: $rating = 'positive'; break;
                default: $rating = 'error';
            }

            $return[$k] = array(
                'rating_id' => $r['rating_id'],
                'message' => $r['message'],
                'rating' => $rating,
                'ratingRaw' => $r['rating'],
                'date' => $r['rating_date'],
            );

            if (isset($r['toUserUsername'])) {
                $return[$k] += array(
                    'toUser' => array(
                        'user_id' => $r['to_user_id'],
                        'username' => $r['toUserUsername'],
                        'positive' => $r['toUserPositive'],
                        'neutral' => $r['toUserNeutral'],
                        'negative' => $r['toUserNegative'],
                        'total' => $r['toUserTotal'],
                        'rating' => $r['toUserRating'],
                        'gravatar' => $r['toUserGravatar'],
                        'avatar_date' => $r['toUserAvatarDate'],
                    ),
                );
            }

            if (isset($r['fromUserUsername'])) {
                $return[$k] += array(
                    'fromUser' => array(
                        'user_id' => $r['from_user_id'],
                        'username' => $r['fromUserUsername'],
                        'positive' => $r['fromUserPositive'],
                        'neutral' => $r['fromUserNeutral'],
                        'negative' => $r['fromUserNegative'],
                        'total' => $r['fromUserTotal'],
                        'rating' => $r['fromUserRating'],
                        'gravatar' => $r['fromUserGravatar'],
                        'avatar_date' => $r['fromUserAvatarDate'],
                    ),
                );
            }
        }

        return $return;
    }

    /**
     * Processes the rating moderation.
     */
    public function processRatingModeration(array $message, $action)
    {
        if ($message['active'] != '0') {
            return false;
        }

        if ($action == 'approve') {
            $dw = XenForo_DataWriter::create('ThemeHouse_UserRatings_DataWriter_Ratings');
            $dw->setExistingData($message);
            $dw->set('active', '1');
            $dw->save();

            return true;
        } elseif ($action == 'reject') {
            $dw = XenForo_DataWriter::create('ThemeHouse_UserRatings_DataWriter_Ratings');
            $dw->setExistingData($message);
            $dw->delete();

            return true;
        }

        return false;
    }

    /**
     * Prepares the rating record for display at the stats center.
     */
    public function getRatingStats()
    {
        $db = $this->_getDb();

        $getAll = $db->fetchAll('SELECT rating, rating_date FROM kmk_user_ratings WHERE active = 1');

        $return['totalRating'] = count($getAll);
        $return['ratings'] = array('negative' => 0, 'neutral' => 0, 'positive' => 0);
        $return['lastMonth'] = $return['last6Months'] = $return['last12Months'] = $return['ratings'];
        $return['last24Hours'] = 0;
        foreach ($getAll as $r) {
            switch ($r['rating']) {
                case -1: $rating = 'negative'; break;
                case 0: $rating = 'neutral'; break;
                case 1: $rating = 'positive'; break;
            }

            if ($r['rating_date'] > XenForo_Application::$time - 86400) {
                $return['last24Hours']++;
            }

            if ($r['rating_date'] > XenForo_Application::$time - 31449600) {
                $return['last12Months'][$rating]++;
            }

            if ($r['rating_date'] > XenForo_Application::$time - 15724800) {
                $return['last6Months'][$rating]++;
            }

            if ($r['rating_date'] > XenForo_Application::$time - 2592000) {
                $return['lastMonth'][$rating]++;
            }

            ++$return['ratings'][$rating];
        }

        $return['ratings']['total'] = $return['ratings']['negative'] + $return['ratings']['neutral'] + $return['ratings']['positive'];

        $ratings = $db->fetchCol('SELECT rating FROM kmk_user_ratings_stats');
        $return['totalUsers'] = count($ratings);
        $return['averageRating'] = 0;
        foreach ($ratings as $rating) {
            $return['averageRating'] += $rating;
        }
        if ($return['totalUsers']) {
            $return['averageRating'] = round($return['averageRating'] / $return['totalUsers'], 1);
        }

        return $return;
    }

    /**
     * Checks to see if the user can view ratings.
     *
     * @param array|null $user
     */
    public function canViewRatings(array $user = null)
    {
        $this->standardizeViewingUserReference($user);

        return XenForo_Permission::hasPermission($user['permissions'], 'ratings', 'viewRatings');
    }

    /**
     * Can add ratings permissions.
     *
     * @param array|null $user
     */
    public function canRate(array $user = null)
    {
        $this->standardizeViewingUserReference($user);

        return XenForo_Permission::hasPermission($user['permissions'], 'ratings', 'addRating');
    }

    /**
     * Prevent abuse of the rating system by setting up a daily limit and an user spred limit.
     *
     * @param array|null $viewingUser
     */
    public function dailyLimit(array $user, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $dailyGids = XenForo_Permission::hasPermission($viewingUser['permissions'], 'ratings', 'dailyLimit');

        //Daily limit.
        if ($dailyGids > 0) {
            $query = $this->countRatings(array('from_user_id' => $viewingUser['user_id'], 'rating_date' => array('>', XenForo_Application::$time - 86400)));

            if ($query >= $dailyGids) {
                $errorPhraseKey = array('th_daily_ratings_reached', 'username' => $viewingUser['username'], 'dailyGids' => $dailyGids);

                return false;
            }
        }

        return true;
    }

    /**
     * Can edit rating permissions.
     */
    public function canEditRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'ratings', 'editAll')) {
            return true;
        }

        if ($rating['fromUser']['user_id'] == $viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'ratings', 'editOwn')) {
            $editLimit = XenForo_Permission::hasPermission($viewingUser['permissions'], 'ratings', 'editDeleteOwnTimeLimit');

            if ($editLimit !== 0) {
                if ($editLimit != -1 && (!$editLimit || $rating['date'] < XenForo_Application::$time - 60 * $editLimit)) {
                    $errorPhraseKey = array('message_edit_time_limit_expired', 'minutes' => $editLimit);

                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Can delete rating permissions.
     */
    public function canDeleteRating(array $rating, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!$viewingUser['user_id']) {
            return false;
        }

        if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'ratings', 'deleteAll')) {
            return true;
        }

        if ($rating['fromUser']['user_id'] == $viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'ratings', 'deleteOwn')) {
            $deleteLimit = XenForo_Permission::hasPermission($viewingUser['permissions'], 'ratings', 'editDeleteOwnTimeLimit');

            if ($deleteLimit !== 0) {
                if ($deleteLimit != -1 && (!$deleteLimit || $rating['date'] < XenForo_Application::$time - 60 * $deleteLimit)) {
                    $errorPhraseKey = array('message_edit_time_limit_expired', 'minutes' => $deleteLimit);

                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Can report ratings permissions.
     *
     * @param array|null $user
     */
    public function canReport(array $user = null)
    {
        $this->standardizeViewingUserReference($user);

        return XenForo_Permission::hasPermission($user['permissions'], 'ratings', 'canReport');
    }
}
