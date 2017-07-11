<?php /*8106c79e09ac3102da7e7a65f6b093657f8a1376*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 9
 * @since      1.0.0 Beta 9
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Installer_Data_1000038 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $handler = new GFNCore_Installer_Handler_Permission();
            $handler->applyGlobalPermission('classifieds', 'bumpAny', 'classifieds', 'editAny', true);
            $handler->applyGlobalPermission('classifieds', 'max', -1, null, false);

            $this->table()->alter('kmk_classifieds_advert_type', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->string('complete_badge_color', 30)->after('badge_color');
            });

            $db = $this->db();
            foreach ($db->fetchPairs('SELECT advert_type_id, badge_color FROM kmk_classifieds_advert_type') as $advertTypeId => $badgeColor)
            {
                $db->update('kmk_classifieds_advert_type', array('complete_badge_color' => $badgeColor), 'advert_type_id = ' . $db->quote($advertTypeId));
            }

            /** @var GFNClassifieds_Model_AdvertType $model */
            $model = XenForo_Model::create('GFNClassifieds_Model_AdvertType');
            $model->rebuildAdvertTypeCache();
        }
    }

    public function uninstall()
    {
        // Nothing! :D
    }
}