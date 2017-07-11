<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_Model_User extends XenForo_Model
{
    const FETCH_USER = 0x01;

    /**
     * Gets the specified user rating stats.
     *
     * @param int $id
     *
     * @return array|false
     */
    public function getUserById($userId, array $fetchOptions = array())
    {
        $joinOptions = $this->prepareUserFetchOptions($fetchOptions);

        return $this->_getDb()->fetchRow('
			SELECT rating_stats.*
				'.$joinOptions['selectFields'].'
			FROM kmk_user_ratings_stats AS rating_stats
				'.$joinOptions['joinTables'].'
			WHERE rating_stats.user_id = ?',
            $userId
        );
    }

    /**
     * Fetch rating stats based on the conditions and options specified.
     *
     * @param array $conditions
     * @param array $fetchOptions
     *
     * @return array
     */
    public function getUsers(array $fetchOptions = array())
    {
        $joinOptions = $this->prepareUserFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
        $orderClause = $this->prepareUserOrderOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults(
            '
				SELECT rating_stats.*
					'.$joinOptions['selectFields'].'
				FROM kmk_user_ratings_stats AS rating_stats
					'.$joinOptions['joinTables'].'
				'.$orderClause.'
			', $limitOptions['limit'], $limitOptions['offset']
        ), 'user_id');
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
            'positive' => 'rating_stats.positive',
            'neutral' => 'rating_stats.neutral',
            'negative' => 'rating_stats.negative',
            'total' => 'rating_stats.total',
            'rating' => 'rating_stats.rating',
        );

        return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
    }

    /**
     * Prepares join-related fetch options.
     *
     * @param array $fetchOptions
     *
     * @return array Containing 'selectFields' and 'joinTables' keys.
     */
    public function prepareUserFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';

        if (!empty($fetchOptions['join'])) {
            if ($fetchOptions['join'] & self::FETCH_USER) {
                $selectFields .= ',
                    user.*';

                $joinTables .= '
                    LEFT JOIN kmk_user AS user ON (rating_stats.user_id = user.user_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables,
        );
    }

    /**
     * Prepares the user rating record for display at the users profiles.
     */
    public function getUserRatingMonthlyStats($userId)
    {
        $ratings = $this->_getDb()->fetchAll('
            SELECT rating, rating_date FROM kmk_user_ratings WHERE rating_date > ? AND to_user_id = ? AND active = 1',
            array(XenForo_Application::$time - 31449600, $userId)
        );

        $return['lastMonth'] = $return['last6Months'] = $return['last12Months'] = array('negative' => 0, 'neutral' => 0, 'positive' => 0);
        foreach ($ratings as $r) {
            switch ($r['rating']) {
                case -1: $rating = 'negative'; break;
                case 0: $rating = 'neutral'; break;
                case 1: $rating = 'positive'; break;
            }

            if ($r['rating_date'] > XenForo_Application::$time - 2592000) {
                $return['lastMonth'][$rating]++;
            }

            if ($r['rating_date'] > XenForo_Application::$time - 15724800) {
                $return['last6Months'][$rating]++;
            }

            ++$return['last12Months'][$rating];
        }

        return $return;
    }
}
