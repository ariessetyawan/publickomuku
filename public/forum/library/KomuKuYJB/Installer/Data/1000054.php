<?php /*7b0e5d1556bbe664b0041c70facfbea87b61f31b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 5
 * @since      1.0.0 RC 5
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Installer_Data_1000054 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        $handler = new GFNCore_Installer_Handler_ContentType('KomuKuYJB');
        $handler->add('classified', $handler::TAG_HANDLER, 'KomuKuYJB_TagHandler_Classified');

        if ($isUpgrade)
        {
            $this->table()->alter('kmk_classifieds_classified', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->mediumBlob('tags');
            });

            $this->table()->alter('kmk_classifieds_category', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->tinyInteger('min_tags')->default(0)->unsigned(true);
            });

            $this->table()->alter('kmk_classifieds_trader', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->integer('response_time')->default(0)->unsigned(true);
                $table->decimal('response_percentage', 5)->default(0);
            });

            $this->table()->alter('kmk_user_option', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->enum('all_classified_watch_state', array('', 'watch_no_email', 'watch_email'))->default('')->after('default_watch_state');
            });
        }

        $handler = new GFNCore_Installer_Handler_Permission();
        $handler->applyGlobalPermission('classifieds', 'manageOthersTagsOwnClass', 'forum', 'manageOthersTagsOwnThread');
        $handler->applyGlobalPermission('classifieds', 'tagOwnClassified', 'forum', 'tagOwnThread');
        $handler->applyGlobalPermission('classifieds', 'tagAnyClassified', 'forum', 'tagAnyThread');
        $handler->applyGlobalPermission('classifieds', 'manageAnyTag', 'forum', 'manageAnyTag', true);
    }

    public function uninstall()
    {
        // nothing to do! :D
    }
}