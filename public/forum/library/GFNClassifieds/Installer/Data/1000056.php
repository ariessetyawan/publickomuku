<?php /*1507b4883b0c4f0d782b73efe542ab04a86250a4*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 6
 * @since      1.0.0 RC 6
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Installer_Data_1000056 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $this->db()->update('kmk_user_option', array('all_classified_watch_state' => ''));

            $this->table()->alter('kmk_classifieds_trader', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->index('rating_count');
            });
        }

        $seeder = new GFNCore_Installer_Handler_Table();
        $seeder->seed('kmk_user', 'kmk_classifieds_trader', 'user_id');

        XenForo_Model::create('GFNClassifieds_Model_Classified')->rebuildCount();
        XenForo_Model::create('GFNClassifieds_Model_TraderRating')->rebuildCount();
    }

    public function uninstall()
    {
        // nothing to do! :D
    }
}