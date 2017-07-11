<?php /*db11a3350c9c7456f0c0beecade77876511bbbf0*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Extend_XenForo_Model_Thread extends XFCP_GFNClassifieds_Extend_XenForo_Model_Thread
{
    /*public function prepareThreadFetchOptions(array $fetchOptions)
    {
        $return = parent::prepareThreadFetchOptions($fetchOptions);

        $return['selectFields'] .= ', classified.classified_id, classified.title AS classified_title, classified.price AS classified_price,
                                    classified.currency AS classified_currency, classified.featured_image_date AS classified_featured_image_date,
                                    classified.advert_type_id AS classified_advert_type_id, classified.prefix_id AS classified_prefix_id,
                                    classified.classified_state';

        $return['joinTables'] .= ' LEFT JOIN kmk_classifieds_classified AS classified
                                    ON (thread.discussion_type = \'classified\' AND thread.thread_id = classified.discussion_thread_id) ';

        return $return;
    }*/

    public function updateThreadViews()
    {
        parent::updateThreadViews();
        $this->getModelFromCache('GFNClassifieds_Model_Classified')->updateClassifiedView();
    }
}