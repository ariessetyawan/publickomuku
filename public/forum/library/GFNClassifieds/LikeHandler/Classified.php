<?php /*19d07d197e16d897e783aac23d69ad4879ad5711*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_LikeHandler_Classified extends XenForo_LikeHandler_Abstract
{
    public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
    {
        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Classified');
        if ($writer->setExistingData($contentId))
        {
            $writer->set('likes', $writer->get('likes') + $adjustAmount);
            $writer->set('like_users', $latestLikes);
            $writer->save();
        }
    }

    public function getContentData(array $contentIds, array $viewingUser)
    {
        /** @var GFNClassifieds_Model_Classified $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_Classified');

        $classifieds = $model->getClassifiedsByIds($contentIds, array(
            'join' => $model::FETCH_CATEGORY,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        $classifieds = $model->unserializePermissionsInList($classifieds, 'category_permission_cache');
        $return = array();

        foreach ($classifieds as $classifiedId => $classified)
        {
            if ($model->canViewClassifiedAndContainer($classified, $classified, $null, $viewingUser, $classified['permission']))
            {
                $return[$classifiedId] = $classified;
            }
        }

        return $return;
    }

    public function batchUpdateContentUser($oldUserId, $newUserId, $oldUsername, $newUsername)
    {
        /** @var GFNClassifieds_Model_Classified $model */
        $model = XenForo_Model::create('GFNClassifieds_Model_Classified');
        $model->batchUpdateLikeUser($oldUserId, $newUserId, $oldUsername, $newUsername);
    }

    /**
     * Gets the name of the template that will be used when listing likes of this type.
     *
     * @return string news_feed_item_{$contentType}_like
     */
    public function getListTemplateName()
    {
        return 'news_feed_item_classified_like';
    }
}