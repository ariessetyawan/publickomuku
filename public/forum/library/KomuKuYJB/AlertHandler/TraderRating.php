<?php /*9fc432b6c2b7f5eb2132bc4f17b1d01b546a282f*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_AlertHandler_TraderRating extends XenForo_AlertHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
    {
        return $this->_getTraderRatingModel()->getTraderRatingsByIds($contentIds, array());
    }

    public function canViewAlert(array $alert, $content, array $viewingUser)
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