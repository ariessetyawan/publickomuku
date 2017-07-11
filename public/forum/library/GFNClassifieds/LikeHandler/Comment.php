<?php /*359d15b6827c9b2047e81df12eab64e1a7ae85a1*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_LikeHandler_Comment extends XenForo_LikeHandler_Abstract
{
    public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
    {
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment');
        if ($writer->setExistingData($contentId))
        {
            $writer->set('likes', $writer->get('likes') + $adjustAmount);
            $writer->set('like_users', $latestLikes);
            $writer->save();
        }
    }

    public function getContentData(array $contentIds, array $viewingUser)
    {
        /** @var GFNClassifieds_Model_Comment $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_Comment');

        $comments = $model->getCommentsByIds($contentIds, array(
            'join' => $model::FETCH_CATEGORY,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        $comments = $model->unserializePermissionsInList($comments, 'category_permission_cache');
        $return = array();

        foreach ($comments as $commentId => $comment)
        {
            if ($model->canViewCommentAndContainer($comment, $comment, $comment, $null, $viewingUser))
            {
                $return[$commentId] = $comment;
            }
        }

        return $return;
    }

    public function batchUpdateContentUser($oldUserId, $newUserId, $oldUsername, $newUsername)
    {
        /** @var GFNClassifieds_Model_Comment $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_Comment');
        $model->batchUpdateLikeUser($oldUserId, $newUserId, $oldUsername, $newUsername);
    }

    public function getListTemplateName()
    {
        return 'news_feed_item_classified_comment_like';
    }
}