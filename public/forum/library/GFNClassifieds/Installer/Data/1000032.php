<?php /*9cfa5997c531c318f18c24aa61ff688e008dc6ba*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 3
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Installer_Data_1000032 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        $this->rebuild();
    }

    public function uninstall()
    {
        // Nothing! :D
    }

    public function rebuild()
    {
        XenForo_Model::create('GFNClassifieds_Model_CategoryAssociation_AdvertType')->rebuildAssociationCache();
        XenForo_Model::create('GFNClassifieds_Model_CategoryAssociation_Field')->rebuildAssociationCache();
        XenForo_Model::create('GFNClassifieds_Model_CategoryAssociation_Package')->rebuildAssociationCache();
        XenForo_Model::create('GFNClassifieds_Model_CategoryAssociation_Prefix')->rebuildAssociationCache();
    }
}