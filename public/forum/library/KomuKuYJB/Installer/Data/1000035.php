<?php /*d26e9873664f4b182ddc4883e6c585188debd08c*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 6
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Installer_Data_1000035 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $this->table()->alter('kmk_classifieds_classified', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->modify($table->string('classified_state', 20)->default('visible'));
            });

            $stmt = $this->db()->query(
                'SELECT classified_id, classified_state, classified_open, classified_completed, expire_date
                FROM kmk_classifieds_classified
                ORDER BY classified_id'
            );

            while ($row = $stmt->fetch())
            {
                $state = $row['classified_state'];

                if ($row['expire_date'] != 0 && $row['expire_date'] <= XenForo_Application::$time)
                {
                    $state = 'expired';
                }
                if (!$row['classified_open'])
                {
                    $state = 'closed';
                }
                if ($row['classified_completed'])
                {
                    $state = 'completed';
                }

                $this->db()->update('kmk_classifieds_classified', array('classified_state' => $state), 'classified_id = ' . $this->db()->quote($row['classified_id']));
            }

            $stmt->closeCursor();

            $this->table()->alter('kmk_classifieds_classified', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->drop('classified_open');
                $table->drop('classified_completed');
                $table->integer('last_bump_date')->default(0)->unsigned(true);
            });

            $this->rebuild();
        }
    }

    public function uninstall()
    {
        // Nothing! :D
    }

    public function rebuild()
    {
        XenForo_Model::create('KomuKuYJB_Model_Classified')->rebuildCount();
    }
}