<?php /*3db7438f11ef264eb0431ad699f14f6f3eb1f0f3*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_NewsFeedHandler_TraderRating extends XenForo_NewsFeedHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, array $viewingUser)
    {
        return $this->_getTraderRatingModel()->getTraderRatingsByIds($contentIds);
    }

    public function canViewNewsFeedItem(array $item, $content, array $viewingUser)
    {
        return $this->_getTraderRatingModel()->canViewTraderRating($content, $null, $viewingUser);
    }

    /**
     * @return KomuKuYJB_Model_TraderRating
     */
    protected function _getTraderRatingModel()
    {
        static $model = null;

        if ($model === null)
        {
            $model = XenForo_Model::create('KomuKuYJB_Model_TraderRating');
        }

        return $model;
    }
}