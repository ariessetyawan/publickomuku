<?php /*80f04d7b946e56e3e42af77317d471528363fdb9*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_StatsHandler_Classified extends XenForo_StatsHandler_Abstract
{
    public function getStatsTypes()
    {
        return array(
            'classified' => new XenForo_Phrase('classifieds'),
            'classified_like' => new XenForo_Phrase('classified_likes'),
            'classified_renewal' => new XenForo_Phrase('classified_renewals'),
            'classified_income_listing' => new XenForo_Phrase('income_from_listing_classifieds'),
            'classified_income_renewal' => new XenForo_Phrase('income_from_renewing_classifieds')
        );
    }

    public function getData($startDate, $endDate)
    {
        $db = $this->_getDb();

        $classifieds = $db->fetchPairs($this->_getBasicDataQuery(
            'kmk_classifieds_classified', 'classified_date', 'classified_state = ?'
        ), array($startDate, $endDate, 'visible'));

        $classifiedLikes = $db->fetchPairs($this->_getBasicDataQuery(
            'kmk_liked_content', 'like_date', 'content_type = ?'
        ), array($startDate, $endDate, 'classified'));

        $classifiedRenewals = $db->fetchPairs($this->_getBasicDataQuery(
            'kmk_classifieds_classified', 'classified_date', 'classified_state = ?', 'SUM(renewal_count)'
        ), array($startDate, $endDate, 'visible'));

        $listingIncome = $db->fetchPairs($this->_getBasicDataQuery(
            'kmk_classifieds_payment', 'payment_date', 'is_renewal = 0 AND payment_complete = 1', 'SUM(amount)'
        ), array($startDate, $endDate));

        $renewalIncome = $db->fetchPairs($this->_getBasicDataQuery(
            'kmk_classifieds_payment', 'payment_date', 'is_renewal = 1 AND payment_complete = 1', 'SUM(amount)'
        ), array($startDate, $endDate));

        return array(
            'classified' => $classifieds,
            'classified_like' => $classifiedLikes,
            'classified_renewal' => $classifiedRenewals,
            'classified_income_listing' => $listingIncome,
            'classified_income_renewal' => $renewalIncome
        );
    }
}