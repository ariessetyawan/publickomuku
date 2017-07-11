<?php /*fb1a2bc7767f47f6c1ae8113ff933495403fc07d*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 7
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Installer_Data_1000037 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $this->db()->query(
                'UPDATE kmk_classifieds_classified
                SET last_bump_date = classified_date'
            );
        }
    }

    public function uninstall()
    {
        // Nothing! :D
    }
}