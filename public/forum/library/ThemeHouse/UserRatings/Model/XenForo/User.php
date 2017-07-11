<?php

//######################## User Ratings By ThemeHouse ###########################
class ThemeHouse_UserRatings_Model_XenForo_User extends XFCP_ThemeHouse_UserRatings_Model_XenForo_User
{
    /**
     * Prepares join-related fetch options.
     *
     * @param array $fetchOptions
     *
     * @return array Containing 'selectFields' and 'joinTables' keys.
     */
    public function prepareUserFetchOptions(array $fetchOptions)
    {
        $parent = parent::prepareUserFetchOptions($fetchOptions);
        $selectFields = $parent['selectFields'];
        $joinTables = $parent['joinTables'];

        if (!empty($fetchOptions['join'])) {
            if ($fetchOptions['join'] & self::FETCH_USER_FULL) {
                $selectFields .= ',
					kmk_user_ratings_stats.user_id AS has_rating, kmk_user_ratings_stats.positive, kmk_user_ratings_stats.neutral, kmk_user_ratings_stats.negative, kmk_user_ratings_stats.total, kmk_user_ratings_stats.rating';
                $joinTables .= '
					LEFT JOIN kmk_user_ratings_stats ON (user.user_id = kmk_user_ratings_stats.user_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables,
        );
    }
}
