<?php /*2193280dad97fb0dc137c6046d177d9ea4b4a653*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 5
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Installer_Data_1000014 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $this->table()->alter('kmk_classifieds_field', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->text('hint_text')->default('');
                $table->boolean('include_in_classified_editor')->default(1)->after('include_in_thread_list');
                $table->boolean('include_in_classified_view')->default(1)->after('include_in_classified_editor');
                $table->boolean('include_in_thread_view')->default(0)->after('include_in_thread_list');
            });
        }

        /** @var GFNClassifieds_Model_AdvertType $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_AdvertType');
        $model->rebuildAdvertTypeCache();
    }

    public function uninstall()
    {
        // Nothing! :D
    }

    public function rebuild()
    {
        /** @var GFNClassifieds_Model_AdvertType $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_AdvertType');
        $model->rebuildAdvertTypeCache();
    }
}