<?php /*fe0dcc4f998ae1ecef562822962b91e824acd734*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ModerationQueueHandler_Comment extends XenForo_ModerationQueueHandler_Abstract
{
    public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
    {
        /** @var KomuKuYJB_Model_Comment $commentModel */
        $commentModel = XenForo_Model::create('KomuKuYJB_Model_Comment');
        $comments = $commentModel->getCommentsByIds($contentIds, array(
            'join' => $commentModel::FETCH_CATEGORY | $commentModel::FETCH_CLASSIFIED | $commentModel::FETCH_USER,
            'permissionCombinationId' => $viewingUser['permission_combination_id']
        ));

        $return  = array();

        foreach ($comments as $comment)
        {
            $comment['permissions'] = XenForo_Permission::unserializePermissions($comment['category_permission_cache']);
            $canManage = true;

            if (!$commentModel->canViewCommentAndContainer($comment, $comment, $comment, $null, $viewingUser, $comment['permissions']))
            {
                $canManage = false;
            }
            elseif ( !XenForo_Permission::hasContentPermission($comment['permissions'], 'editCommentAny')
                || !XenForo_Permission::hasContentPermission($comment['permissions'], 'deleteCommentAny')
            )
            {
                $canManage = false;
            }

            if ($canManage)
            {
                $return[$comment['comment_id']] = array(
                    'message' => $comment['message'],
                    'user' => array(
                        'user_id' => $comment['user_id'],
                        'username' => $comment['username']
                    ),
                    'title' => new XenForo_Phrase('comment_by_x', array('user' => $comment['username'])),
                    'link' => XenForo_Link::buildPublicLink('classifieds/comments', $comment),
                    'contentTypeTitle' => new XenForo_Phrase('classified_comment'),
                    'titleEdit' => false
                );
            }
        }

        return $return;
    }

    public function approveModerationQueueEntry($contentId, $message, $title)
    {
        $message = XenForo_Helper_String::autoLinkBbCode($message);

        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
        $writer->setExistingData($contentId);
        $writer->set('message_state', 'visible');
        $writer->set('message', $message);

        return $writer->save();
    }

    public function deleteModerationQueueEntry($contentId)
    {
        $writer = XenForo_DataWriter::create('KomuKuYJB_DataWriter_Comment', XenForo_DataWriter::ERROR_SILENT);
        $writer->setExistingData($contentId);
        $writer->set('message_state', 'deleted');

        return $writer->save();
    }
}