<?php /*87aec9714f311caf2630c39d8f69245defd49b8b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_WarningHandler_Comment extends XenForo_WarningHandler_Abstract
{
    protected function _canView(array $content, array $viewingUser)
    {
        return $this->_getCommentModel()->canViewCommentAndContainer($content, $content, $content, $null, $viewingUser, $content['permissions']);
    }

    protected function _canWarn($userId, array $content, array $viewingUser)
    {
        return $this->_getCommentModel()->canWarnComment($content, $content, $content, $null, $viewingUser, $content['permissions']);
    }

    protected function _canDeleteContent(array $content, array $viewingUser)
    {
        return $this->_getCommentModel()->canDeleteComment($content, $content, $content, 'soft', $null, $viewingUser, $content['permission']);
    }

    protected function _getContent(array $contentIds, array $viewingUser)
    {
        $model = $this->_getCommentModel();

        $comments = $model->getCommentsByIds($contentIds, array(
            'join' => $model::FETCH_CLASSIFIED | $model::FETCH_CATEGORY,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        return $model->unserializePermissionsInList($comments, 'category_permission_cache');
    }

    public function getContentTitle(array $content)
    {
        return new XenForo_Phrase('comment_in_classified_x', array('classified' => $content['classified_title']));
    }

    public function getContentDetails(array $content)
    {
        return $content['message'];
    }

    public function getContentUrl(array $content, $canonical = false)
    {
        return XenForo_Link::buildPublicLink(($canonical ? 'canonical:' : '') . 'classifieds/comments', $content);
    }

    protected function _warn(array $warning, array $content, $publicMessage, array $viewingUser)
    {
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
        if ($writer->setExistingData($content))
        {
            $writer->set('warning_id', $warning['warning_id']);
            $writer->set('warning_message', $publicMessage);
            $writer->save();
        }
    }

    protected function _reverseWarning(array $warning, array $content)
    {
        if ($content)
        {
            $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
            if ($writer->setExistingData($content))
            {
                $writer->set('warning_id', 0);
                $writer->set('warning_message', '');
                $writer->save();
            }
        }
    }

    protected function _deleteContent(array $content, $reason, array $viewingUser)
    {
        $this->_getCommentModel()->deleteComment($content['comment_id'], 'soft', array('reason' => $reason));
        XenForo_Model_Log::logModeratorAction('classified_comment', $content, 'delete_soft', array('reason' => $reason));
        XenForo_Helper_Cookie::clearIdFromCookie($content['comment_id'], 'inlinemod_classified_comments');
    }

    /**
     * @return KomuKuYJB_Model_Comment
     */
    protected function _getCommentModel()
    {
        return XenForo_Model::create('KomuKuYJB_Model_Comment');
    }
}