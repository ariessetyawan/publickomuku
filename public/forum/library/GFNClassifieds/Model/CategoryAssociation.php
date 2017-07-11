<?php /*dd5e4a88de86a469bca7c0d5fef462cd01187549*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Model_CategoryAssociation extends XenForo_Model
{
    /**
     * @return GFNClassifieds_Model_CategoryAssociation_AdvertType
     */
    public function advertType()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation_AdvertType');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation_Field
     */
    public function field()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation_Field');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation_Package
     */
    public function package()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation_Package');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation_Prefix
     */
    public function prefix()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation_Prefix');
    }

    /**
     * @return GFNClassifieds_Model_CategoryAssociation_RatingCriteria
     */
    public function ratingCriteria()
    {
        return $this->getModelFromCache('GFNClassifieds_Model_CategoryAssociation_RatingCriteria');
    }
}