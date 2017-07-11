<?php /*610c7d2a8f04f7d4de483a934e5377d782f16cf3*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Installer_Data_1000055 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $packages = $this->db()->fetchPairs('SELECT package_id, allowed_user_group_ids FROM kmk_classifieds_package');

            $this->table()->alter('kmk_classifieds_package', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->drop('allowed_user_group_ids');
                $table->mediumBlob('user_criteria');
            });

            foreach ($packages as $packageId => $package)
            {
                if ($package == -1)
                {
                    continue;
                }

                $this->db()->update('kmk_classifieds_package', array(
                    'user_criteria' => XenForo_Helper_Php::safeSerialize(array(
                        array(
                            'rule' => 'user_groups',
                            'data' => array(
                                'user_group_ids' => explode(',', $package)
                            )
                        )
                    ))
                ), 'package_id = ' . $this->db()->quote($packageId));
            }

            $this->table()->alter('kmk_user_option', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->enum('all_classified_watch_state', array('', 'watch_no_email', 'watch_email'))->default('');
            });

            $this->db()->update('kmk_user_option', array('all_classified_watch_state' => ''));
        }
    }

    public function uninstall()
    {
        // nothing to do! :D
    }
}