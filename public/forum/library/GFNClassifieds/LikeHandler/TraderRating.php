<?php /*9a784d8667bfc855fc4ba41354f59fe5d0d8bb37*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_LikeHandler_TraderRating extends XenForo_LikeHandler_Abstract
{
    public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
    {
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_TraderRating');
        if ($writer->setExistingData($contentId))
        {
            $writer->set('likes', $writer->get('likes') + $adjustAmount);
            $writer->set('like_users', $latestLikes);
            $writer->save();
        }
    }

    public function getContentData(array $contentIds, array $viewingUser)
    {
        /** @var GFNClassifieds_Model_TraderRating $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_TraderRating');


    }

    public function batchUpdateContentUser($oldUserId, $newUserId, $oldUsername, $newUsername)
    {
        /** @var GFNClassifieds_Model_TraderRating $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_TraderRating');
        $model->batchUpdateLikeUser($oldUserId, $newUserId, $oldUsername, $newUsername);
    }

    public function getListTemplateName()
    {
        return 'news_feed_item_trader_rating_like';
    }
}