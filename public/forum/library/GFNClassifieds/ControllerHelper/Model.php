<?php /*963f5e52989958920ac4466e9c9138a9d3ca92c3*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerHelper_Model extends XenForo_ControllerHelper_Abstract
{
    protected function _get($class)
    {
        return $this->_controller->getModelFromCache('GFNClassifieds_Model_' . $class);
    }

    /**
     * @return GFNClassifieds_Model_AdvertType
     */
    public function advertType()
    {
        return $this->_get('AdvertType');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation
     */
    public function association()
    {
        return $this->_get('CategoryAssociation');
    }

    /**
     * @return GFNClassifieds_Model_Category
     */
    public function category()
    {
        return $this->_get('Category');
    }

    /**
     * @return GFNClassifieds_Model_Field
     */
    public function field()
    {
        return $this->_get('Field');
    }

    /**
     * @return GFNClassifieds_Model_Package
     */
    public function package()
    {
        return $this->_get('Package');
    }

    /**
     * @return GFNClassifieds_Model_Prefix
     */
    public function prefix()
    {
        return $this->_get('Prefix');
    }

    /**
     * @return GFNClassifieds_Model_Classified
     */
    public function classified()
    {
        return $this->_get('Classified');
    }

    /**
     * @return GFNClassifieds_Model_ClassifiedWatch
     */
    public function classifiedWatch()
    {
        return $this->_get('ClassifiedWatch');
    }

    /**
     * @return GFNClassifieds_Model_CategoryWatch
     */
    public function categoryWatch()
    {
        return $this->_get('CategoryWatch');
    }

    /**
     * @return GFNClassifieds_Model_Payment
     */
    public function payment()
    {
        return $this->_get('Payment');
    }

    /**
     * @return GFNClassifieds_Model_Comment
     */
    public function comment()
    {
        return $this->_get('Comment');
    }

    /**
     * @return XenForo_Model_User
     */
    public function user()
    {
        return $this->_controller->getModelFromCache('XenForo_Model_User');
    }

    /**
     * @return XenForo_Model_Ip
     */
    public function ip()
    {
        return $this->_controller->getModelFromCache('XenForo_Model_Ip');
    }

    /**
     * @return GFNClassifieds_Model_TraderRatingCriteria
     */
    public function ratingCriteria()
    {
        return $this->_get('TraderRatingCriteria');
    }

    /**
     * @return GFNClassifieds_Model_TraderRating
     */
    public function traderRating()
    {
        return $this->_get('TraderRating');
    }

    /**
     * @return GFNClassifieds_Model_Trader
     */
    public function trader()
    {
        return $this->_get('Trader');
    }

    /**
     * @return XenForo_Model_Like
     */
    public function like()
    {
        return $this->_controller->getModelFromCache('XenForo_Model_Like');
    }

    /**
     * @return XenForo_Model_UserProfile
     */
    public function userProfile()
    {
        return $this->_controller->getModelFromCache('XenForo_Model_UserProfile');
    }

    /**
     * @return XenForo_Model_UserIgnore
     */
    public function userIgnore()
    {
        return $this->_controller->getModelFromCache('XenForo_Model_UserIgnore');
    }
}