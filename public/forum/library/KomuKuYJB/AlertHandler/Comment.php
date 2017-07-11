<?php /*99c4b0d8f5af3fbad17f41d702426bbc3955b777*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_AlertHandler_Comment extends XenForo_AlertHandler_Abstract
{
    public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
    {
        $model = $this->_getCommentModel();

        $comments = $model->getCommentsByIds($contentIds, array(
            'join' => $model::FETCH_CATEGORY | $model::FETCH_PARENT_COMMENT,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        foreach ($comments as &$comment)
        {
            $comment['classified_title'] = XenForo_Helper_String::censorString($comment['classified_title']);
        }

        return $comments;
    }

    public function canViewAlert(array $alert, $content, array $viewingUser)
    {
        $model = $this->_getCommentModel();
        $categoryPermissions = XenForo_Permission::unserializePermissions($content['category_permission_cache']);
        return $model->canViewCommentAndContainer($content, $content, $content, $null, $viewingUser, $categoryPermissions);
    }

    /**
     * @return KomuKuYJB_Model_Comment
     */
    protected function _getCommentModel()
    {
        static $model = null;

        if ($model === null)
        {
            $model = XenForo_Model::create('KomuKuYJB_Model_Comment');
        }

        return $model;
    }
}