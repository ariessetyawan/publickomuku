<?php /*7cf0bdb42fe96bda99016af0771debdb0f02a291*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerPublic_Comment extends GFNClassifieds_ControllerPublic_Abstract
{
    public function actionIndex()
    {
        if ($this->_input->filterSingle('comment_id', XenForo_Input::UINT))
        {
            return $this->responseReroute(__CLASS__, 'view');
        }

        return $this->responseReroute('GFNClassifieds_ControllerPublic_Classified', 'comment');
    }

    public function actionShow()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();
        $commentModel = $this->models()->comment();

        if ($comment['reply_parent_comment_id'])
        {
            $viewParams = array(
                'comment' => $comment,
                'classified' => $classified
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Comment_Show', 'classifieds_item_comment_reply', $viewParams);
        }
        else
        {
            $criteria = $commentModel->getPermissionBasedFetchConditions($category);
            $criteria += array(
                'reply_parent_comment_id' => $comment['comment_id']
            );

            $replies = $commentModel->getComments($criteria, array(
                'join' => $commentModel::FETCH_USER,
                'likeUserId' => XenForo_Visitor::getUserId(),
                'limit' => 3,
                'order' => 'post_date',
                'direction' => 'desc'
            ));

            if ($replies)
            {
                $replies = $commentModel->prepareComments($replies, $classified, $category);
                $comment['replies'] = array_reverse($replies);
                $reply = reset($comment['replies']);
                $comment['first_shown_reply_date'] = $reply['post_date'];
            }

            $viewParams = array(
                'comment' => $comment,
                'classified' => $classified
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Comment_Show', 'classifieds_item_comment_single', $viewParams);
        }
    }

    public function actionView()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if ($this->_noRedirect())
        {
            $commentModel = $this->models()->comment();
            $beforeDate = $this->_input->filterSingle('before', XenForo_Input::UINT);
            $criteria = $commentModel->getPermissionBasedFetchConditions($category);

            $criteria += array(
                'reply_parent_comment_id' => $comment['comment_id'],
                'post_date' => array('<', $beforeDate)
            );

            $replies = $commentModel->getComments($criteria, array(
                'join' => $commentModel::FETCH_USER,
                'likeUserId' => XenForo_Visitor::getUserId(),
                'limit' => 50,
                'order' => 'post_date',
                'direction' => 'asc'
            ));

            if (!$replies)
            {
                return $this->responseMessage(new XenForo_Phrase('no_comments_to_display'));
            }

            $replies = $commentModel->prepareComments($replies, $classified, $category);

            $firstReplyShown = reset($replies);
            $lastReplyShown = end($replies);

            $viewParams = array(
                'classified' => $classified,
                'comment' => $comment,
                'replies' => $replies,
                'firstReplyShown' => $firstReplyShown,
                'lastReplyShown' => $lastReplyShown
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Comment_Replies', '', $viewParams);
        }

        XenForo_Application::getSession()->set(
            'classifiedCommentLoadAllReplies',
            $comment['comment_id']
        );

        return $this->getCommentSpecificRedirect(
            $comment, $classified, $category, XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT
        );
    }

    public function actionReply()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if (!$this->models()->comment()->canReplyToComment($comment, $classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->getRequest()->isPost())
        {
            $message = $this->getHelper('Editor')->getMessageText('message', $this->_input);
            $message = XenForo_Helper_String::autoLinkBbCode($message);
            $visitor = XenForo_Visitor::getInstance();

            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment');
            $writer->set('classified_id', $classified['classified_id']);
            $writer->set('user_id', $visitor['user_id']);
            $writer->set('username', $visitor['username']);
            $writer->set('reply_comment_id', $comment['comment_id']);
            $writer->set('message', $message);

            $writer->preSave();

            if (!$writer->hasErrors() && $writer->isInsert())
            {
                $this->assertNotFlooding('post');
            }

            $writer->save();

            if ($this->_noRedirect())
            {
                $reply = $this->models()->comment()->getCommentById($writer->get('comment_id'), array(
                    'join' => GFNClassifieds_Model_Comment::FETCH_USER
                ));

                $viewParams = array(
                    'reply' => $this->models()->comment()->prepareComment($reply, $classified, $category),
                    'comment' => $comment,
                    'classified' => $classified
                );

                return $this->responseView('GFNClassifieds_ViewPublic_Comment_Reply', '', $viewParams);
            }
            else
            {
                return $this->getCommentSpecificRedirect($writer->getMergedData(), $classified, $category);
            }
        }
        else
        {
            $viewParams = array(
                'comment' => $comment,
                'classified' => $classified,

                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Comment_ReplyForm', 'classifieds_comment_reply_form', $viewParams);
        }
    }

    public function actionEdit()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if (!$this->models()->comment()->canEditComment($comment, $classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->_input->inRequest('more_options'))
        {
            $comment['message'] = $this->getHelper('Editor')->getMessageText('message', $this->_input);
        }

        $inline = $this->_input->filterSingle('inline', XenForo_Input::BOOLEAN);

        $viewParams = array(
            'comment' => $comment,
            'classified' => $classified,

            'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
        );

        return $this->responseView(
            'GFNClassifieds_ViewPublic_Comment_Edit',
            $inline ? 'classifieds_comment_edit_inline' : 'classifieds_comment_edit',
            $viewParams
        );
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        if ($this->_input->inRequest('more_options'))
        {
            $this->getRequest()->setParam('inline', false);
            return $this->responseReroute(__CLASS__, 'edit');
        }

        $commentId = $this->_input->filterSingle('comment_id', XenForo_Input::UINT);
        if ($commentId)
        {
            list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable($commentId);
            if (!$this->models()->comment()->canEditComment($comment, $classified, $category, $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }
        }
        else
        {
            $comment = false;
            list ($classified, $category) = $this->getContentHelper()->assertClassifiedValidAndViewable();
            if (!$this->models()->classified()->canAddComment($classified, $category, $errorPhraseKey))
            {
                throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
            }
        }

        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment');

        if ($comment)
        {
            $writer->setExistingData($comment);
        }
        else
        {
            $visitor = XenForo_Visitor::getInstance();
            $writer->set('user_id', $visitor['user_id']);
            $writer->set('username', $visitor['username']);
            $writer->set('classified_id', $classified['classified_id']);
        }

        $message = $this->getHelper('Editor')->getMessageText('message', $this->_input);
        $message = XenForo_Helper_String::autoLinkBbCode($message);
        $writer->set('message', $message);
        $writer->preSave();

        if (!$writer->hasErrors() && $writer->isInsert())
        {
            $this->assertNotFlooding('post');
        }

        $writer->save();

        if ($writer->isInsert())
        {
            $watch = XenForo_Visitor::getInstance()->get('default_classified_watch_state');
            $this->models()->classifiedWatch()->setClassifiedWatchState(
                XenForo_Visitor::getUserId(), $writer->get('classified_id'), $watch
            );
        }

        if ($this->_noRedirect())
        {
            $lastCommentDate = $this->_input->filterSingle('last_date', XenForo_Input::UINT);

            $newComments = $this->models()->comment()->getLatestComments($classified['classified_id'], $lastCommentDate, array(
                'join' => GFNClassifieds_Model_Comment::FETCH_USER
            ));

            $newComments = $this->models()->comment()->prepareComments($newComments, $classified, $category);

            foreach ($newComments as $commentId => $comment)
            {
                if (!$comment['canView'])
                {
                    unset ($newComments[$commentId]);
                }
            }

            $lastComment = end($newComments);
            $lastCommentDate = $lastComment['post_date'];

            $viewParams = array(
                'comments' => $newComments,
                'lastCommentDate' => $lastCommentDate
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Comment_Insert', '', $viewParams);
        }
        else
        {
            return $this->getCommentSpecificRedirect($writer->getMergedData(), $classified, $category);
        }
    }

    public function actionLike()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if (!$this->models()->comment()->canLikeComment($comment, $classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $likeModel = $this->_getLikeModel();
        $existingLike = $likeModel->getContentLikeByLikeUser('classified_comment', $comment['comment_id'], XenForo_Visitor::getUserId());

        if ($this->getRequest()->isPost())
        {
            if ($existingLike)
            {
                $latestUsers = $likeModel->unlikeContent($existingLike);
            }
            else
            {
                $latestUsers = $likeModel->likeContent('classified_comment', $comment['comment_id'], $comment['user_id']);
            }

            $liked = $existingLike ? false : true;

            if ($this->_noRedirect() && $latestUsers !== false)
            {
                $comment['likeUsers'] = $latestUsers;
                $comment['likes'] += $liked ? 1 : -1;
                $comment['like_date'] = $liked ? XenForo_Application::$time : 0;

                $viewParams = array(
                    'comment' => $comment,
                    'liked' => $liked
                );

                return $this->responseView('GFNClassifieds_ViewPublic_Comment_LikeConfirmed', '', $viewParams);
            }
            else
            {
                return $this->getCommentSpecificRedirect($comment, $classified, $category);
            }
        }
        else
        {
            $viewParams = array(
                'comment' => $comment,
                'classified' => $classified,
                'like' => $existingLike,

                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Comment_Like', 'classifieds_comment_like', $viewParams);
        }
    }

    public function actionLikes()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        $likes = $this->_getLikeModel()->getContentLikes('classified_comment', $comment['comment_id']);
        if (!$likes)
        {
            return $this->responseError(new XenForo_Phrase('no_one_has_liked_this_classified_yet'), 404);
        }

        $viewParams = array(
            'comment' => $comment,
            'classified' => $classified,

            'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
        );

        return $this->responseView('GFNClassifieds_ViewPublic_Comment_Likes', 'classifieds_comment_likes', $viewParams);
    }

    public function actionReport()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if (!$this->models()->comment()->canReportComment($comment, $classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            $reportMessage = $this->_input->filterSingle('message', XenForo_Input::STRING);
            if (!$reportMessage)
            {
                return $this->responseError(new XenForo_Phrase('please_enter_reason_for_reporting_this_message'));
            }

            $this->assertNotFlooding('report');

            $comment['classified'] = $classified;
            $comment['category'] = $category;

            /* @var $reportModel XenForo_Model_Report */
            $reportModel = XenForo_Model::create('XenForo_Model_Report');
            $reportModel->reportContent('classified_comment', $comment, $reportMessage);

            $response = $this->getCommentSpecificRedirect($comment, $classified, $category);
            $response->redirectMessage = new XenForo_Phrase('thank_you_for_reporting_this_message');
            return $response;
        }
        else
        {
            $viewParams = array(
                'comment' => $comment,
                'classified' => $classified,

                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Comment_Report', 'classifieds_comment_report', $viewParams);
        }
    }

    public function actionDelete()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        $hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
        $deleteType = ($hardDelete ? 'hard' : 'soft');

        if (!$this->models()->comment()->canDeleteComment($comment, $classified, $category, $deleteType, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        if ($this->isConfirmedPost())
        {
            /** @var GFNClassifieds_DataWriter_Comment $writer */
            $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment');
            $writer->setExistingData($comment);

            if ($hardDelete)
            {
                $writer->delete();

                XenForo_Model_Log::logModeratorAction('classified_comment', $comment, 'delete_hard');

                return $this->responseRedirect(
                    XenForo_ControllerResponse_Redirect::SUCCESS,
                    $this->_buildLink('classifieds/comments', $classified)
                );
            }
            else
            {
                $reason = $this->_input->filterSingle('reason', XenForo_Input::STRING);
                $writer->setExtraData($writer::DATA_DELETE_REASON, $reason);
                $writer->set('message_state', 'deleted');
                $writer->save();

                if (XenForo_Visitor::getUserId() != $comment['user_id'])
                {
                    XenForo_Model_Log::logModeratorAction('classified_comment', $comment, 'delete_soft', array('reason' => $reason));
                }

                return $this->getCommentSpecificRedirect($comment, $classified, $category);
            }
        }
        else
        {
            $viewParams = array(
                'comment' => $comment,
                'classified' => $classified,
                'canHardDelete' => $this->models()->comment()->canDeleteComment($comment, $classified, $category, 'hard'),

                'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
            );

            return $this->responseView('GFNClassifieds_ViewPublic_Comment_Delete', 'classifieds_comment_delete', $viewParams);
        }
    }

    public function actionUndelete()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if (!$this->models()->comment()->canUndeleteComment($comment, $classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment');
        $writer->setExistingData($comment);
        $writer->set('message_state', 'visible');
        $writer->save();

        XenForo_Model_Log::logModeratorAction('classified_comment', $comment, 'undelete');
        return $this->getCommentSpecificRedirect($comment, $classified, $category);
    }

    public function actionApprove()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if (!$this->models()->comment()->canApproveComment($comment, $classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment');
        $writer->setExistingData($comment);
        $writer->set('message_state', 'visible');
        $writer->save();

        XenForo_Model_Log::logModeratorAction('classified_comment', $comment, 'approve');
        return $this->getCommentSpecificRedirect($comment, $classified, $category);
    }

    public function actionUnapprove()
    {
        $this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if (!$this->models()->comment()->canUnapproveComment($comment, $classified, $category, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $writer = XenForo_DataWriter::create('GFNClassifieds_DataWriter_Comment');
        $writer->setExistingData($comment);
        $writer->set('message_state', 'moderated');
        $writer->save();

        XenForo_Model_Log::logModeratorAction('classified_comment', $comment, 'unapprove');
        return $this->getCommentSpecificRedirect($comment, $classified, $category);
    }

    public function actionIp()
    {
        list ($comment, $classified, $category) = $this->getContentHelper()->assertCommentValidAndViewable();

        if (!$this->models()->user()->canViewIps($errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $ipInfo = $this->models()->ip()->getContentIpInfo($comment);
        if (empty($ipInfo['contentIp']))
        {
            return $this->responseError(new XenForo_Phrase('no_ip_information_available'));
        }

        $viewParams = array(
            'comment' => $comment,
            'classified' => $classified,
            'ipInfo' => $ipInfo,

            'categoryBreadcrumbs' => $this->models()->category()->getCategoryBreadcrumb($category)
        );

        return $this->responseView('GFNClassifieds_ViewPublic_Comment_Ip', 'classifieds_comment_ip', $viewParams);
    }

    public function getCommentSpecificRedirect(array $comment, array $classified, array $category, $redirectType = XenForo_ControllerResponse_Redirect::SUCCESS)
    {
        if ($comment['reply_parent_comment_id'])
        {
            $reply = $comment;
            $comment = $this->models()->comment()->getCommentById($reply['reply_parent_comment_id']);
        }
        else
        {
            $reply = false;
        }

        if ($comment['post_date'] < XenForo_Application::$time)
        {
            $criteria = array(
                'classified_id' => $classified['classified_id'],
                'post_date' => array('>', $comment['post_date'])
            );

            $criteria += $this->models()->comment()->getPermissionBasedFetchConditions($category);
            $totalComments = $this->models()->comment()->countComments($criteria);
            $page = floor($totalComments / GFNClassifieds_Options::getInstance()->get('commentsPerPage')) + 1;
        }
        else
        {
            $page = 1;
        }

        return $this->responseRedirect(
            $redirectType, $this->_buildLink('classifieds/comments', $classified, array(
                'page' => $page
            )) . ($reply ? '#comment-reply-' . $reply['comment_id'] : '#comment-' . $comment['comment_id'])
        );
    }
}