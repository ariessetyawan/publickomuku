<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Installer_Data_1000014 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        $this->table()->create('gfncore_data_cache', function(GFNCore_Db_Schema_Table_Create $table)
        {
            $table->binary('data_key', 25);
            $table->mediumBlob('data_value');
            $table->integer('expire_date')->unsigned(true);
        });

        GFNCore_Registry::set('gfncore', array());
    }

    public function uninstall()
    {
        $this->table()->drop('gfncore_data_cache');
    }
}