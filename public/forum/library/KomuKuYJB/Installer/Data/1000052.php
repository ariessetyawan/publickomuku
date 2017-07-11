<?php /*e45ff074b0b4f6cc749ec9d131f8ab11f10c614a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Installer_Data_1000052 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $this->table()->alter('kmk_classifieds_classified', function (GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->modify($table->double('price', 17, 2));
                $table->modify($table->double('price_base_currency', 22, 7));
            });
        }

        $this->table()->truncate('kmk_classifieds_trader');
        $db = $this->db();
        $position = 0;

        do
        {
            $userIds = $db->fetchCol('SELECT user_id FROM kmk_user WHERE user_id > ? ORDER BY user_id ASC LIMIT 10000', $position);

            foreach ($userIds as $userId)
            {
                $db->query('INSERT IGNORE INTO kmk_classifieds_trader (user_id) VALUES (?)', $userId);
                $position = $userId;
            }
        }
        while (!empty($userIds));

        XenForo_Model::create('KomuKuYJB_Model_Classified')->rebuildCount();
        XenForo_Model::create('KomuKuYJB_Model_TraderRating')->rebuildCount();
    }

    public function uninstall()
    {
        // Nothing to do! :D
    }
}