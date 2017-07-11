<?php /*73e1ecf559647ef74a7aa6f8351ca8f098eeb3ef*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 3
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Installer_Data_1000013 extends GFNCore_Installer_Data_Abstract
{
    public function install($isUpgrade = false)
    {
        if ($isUpgrade)
        {
            $this->table()->alter('kmk_classifieds_comment', function(GFNCore_Db_Schema_Table_Alter $table)
            {
                $table->integer('first_reply_date')->unsigned(true)->default(0)->after('reply_count');
            });

            $db = $this->db();

            $pairs = $db->fetchPairs(
                'SELECT reply_parent_comment_id, MIN(post_date)
                FROM kmk_classifieds_comment
                WHERE reply_parent_comment_id <> 0
                GROUP BY reply_parent_comment_id'
            );

            foreach ($pairs as $commentId => $firstReplyDate)
            {
                $db->update('kmk_classifieds_comment', array('first_reply_date' => $firstReplyDate), 'comment_id = ' . $db->quote($commentId));
            }
        }
    }

    public function uninstall()
    {
        // Nothing! :D
    }
}