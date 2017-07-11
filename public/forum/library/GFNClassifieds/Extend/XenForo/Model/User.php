<?php /*9a8c88b0add5a3c98ed1d371696c63be8a37533a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_Model_User extends XFCP_GFNClassifieds_Extend_XenForo_Model_User
{
    public function prepareUserFetchOptions(array $fetchOptions)
    {
        $return = parent::prepareUserFetchOptions($fetchOptions);

        if (GFNClassifieds_Application::getInstalledVersionId() > 1000050)
        {
            $return['selectFields'] .= ', trader.*, user.user_id';
            $return['joinTables'] .= ' LEFT JOIN kmk_classifieds_trader AS trader ON (trader.user_id = user.user_id) ';
        }

        return $return;
    }

    public function getVisitingGuestUser()
    {
        return parent::getVisitingGuestUser() + array(
            'classified_count' => 0,
            'rating_count' => 0,
            'rating_positive_count' => 0,
            'rating_neutral_count' => 0,
            'rating_negative_count' => 0,
            'rating_avg' => 0.0,
            'rating_weighted' => 0.0,
            'response_time' => 0,
            'response_percentage' => 0.0,
            'default_classified_watch_state' => ''
        );
    }
}