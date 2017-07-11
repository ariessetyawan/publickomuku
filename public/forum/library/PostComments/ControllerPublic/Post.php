<?php

class PostComments_ControllerPublic_Post extends XFCP_PostComments_ControllerPublic_Post
{
	// Submit the comment
	public function actionComment()
	{
		// Define some variables
		$visitor = XenForo_Visitor::getInstance();
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		$message = $this->_input->filterSingle('message', XenForo_Input::STRING);

		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);

		$model = $this->getModelFromCache('PostComments_Model_Comment');
		$comment = $model->getCommentsForPostId($postId);
		
		// Exclude forum(s) from using the post comments
		$options = XenForo_Application::get('options');

		$excludefids = $options->excludefids;

		if (in_array($forum['node_id'], $excludefids))
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_page_not_found'), 404));
		}

		// Can post comments
		if (!$model->canPostComment())
		{
			throw $this->getErrorOrNoPermissionResponseException('do_not_have_permission');
		}

		// Prevent abuse of the post comments system by setting up a group(s) and forum(s)daily limit. Staff is excluded
		if (!$model->dailyLimit($post, $thread, $forum, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->_request->isPost()) // submit the comment
		{
			// Check time interval for commenting
			if ($commentPeriod = $model->commentFloodCheck() > 0)
			{
				return $this->responseError(new XenForo_Phrase('comment_interval_check', array('seconds' => $commentPeriod)), 200);
			}

			// Comments should contain content
			if (!$message)
			{
				return $this->responseError(new XenForo_Phrase('please_enter_valid_message'));
			}

			$writer = XenForo_DataWriter::create('PostComments_DataWriter_Comment');
			$writer->set('user_id', $visitor['user_id']);
			$writer->set('username', $visitor['username']);
			$writer->set('content_id', $postId);
			$writer->set('comment', $message);

			$writer->save();

			 // Send the alerts for post comments
			$alertModel = $this->getModelFromCache('PostComments_Model_Alert');

			if ($post['user_id'] != $visitor['user_id'])
			{
				$alerts = array($post['username']);
				$alertModel->sendCommentAlert('comments', $post['post_id'], $alerts, $visitor);
			}

			// Sends a comment to all other subscribed users that a new comment was posted
			$subscribers = $model->getUsersForPostId($post['post_id'], $post['user_id']);

			if ($subscribers)
			{
				foreach ($subscribers as $subscriber)
				{
					if ($subscriber['user_id'] != $visitor['user_id'] && $post['user_id'] != $visitor['user_id'])
					{
						$alerts = array($subscriber['username']);
						$alertModel->sendCommentAlert('comments_existing', $post['post_id'], $alerts, $visitor);
					}
				}
			}

			if ($this->_noRedirect())
			{
				$comment = $model->getCommentById($writer->get('comment_id'));

				$viewParams = array(
					'comment' => $model->prepareComment($comment)
				);

				return $this->responseView('PostComments_ViewPublic_Comment', 200, $viewParams);
			}
			else
			{
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('posts', $post)
				);
			}
		}
		else
		{
			$viewParams = array(
				'post' => $post,
				'thread' => $thread,
				'forum' => $forum,
				'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
			);

			return $this->responseView('PostComments_ViewPublic', 'post_comment_post', $viewParams);
		}
	}

	// Edit the comments
	public function actionCommentEdit()
	{
		// Define some variables
		$commentId = $this->_input->filterSingle('comment', XenForo_Input::UINT);
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		$message = $this->_input->filterSingle('message', XenForo_Input::STRING);

		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);

		$model = $this->getModelFromCache('PostComments_Model_Comment');
		$comment = $model->getCommentById($commentId);

		// Can edit comments
		if(!$model->canEditComment($comment))
		{
			throw $this->getErrorOrNoPermissionResponseException('do_not_have_permission');
		}

		if ($this->isConfirmedPost()) //comment is edited
		{
			$dw = XenForo_DataWriter::create('PostComments_DataWriter_Comment');
			$dw->setExistingData($commentId);
			$dw->set('comment', $message);
			
			$dw->save();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('posts', $post)
			);
		}
		else
		{	
			$viewParams = array(
				'comment' => $comment,
				'post' => $post,
				'thread' => $thread,
				'forum' => $forum,
				'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
			);

			return $this->responseView('PostComments_ViewPublic', 'post_comment_edit', $viewParams);
		}
	}

	// Delete comments
	public function actionCommentDelete()
	{
		// Define some variables
		$commentId = $this->_input->filterSingle('comment', XenForo_Input::UINT);
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);

		$model = $this->getModelFromCache('PostComments_Model_Comment');
		$comment = $model->getCommentById($commentId);

		// Can delete comments
		if(!$model->canDeleteComment($comment))
		{
			throw $this->getErrorOrNoPermissionResponseException('do_not_have_permission');
		}

		if ($this->isConfirmedPost()) //comment is deleted
		{
			$dw = XenForo_DataWriter::create('PostComments_DataWriter_Comment');
			$dw->setExistingData($commentId);
			$dw->delete();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('posts', $post)
			);
		}
		else
		{			
			$viewParams = array(
				'comment' => $comment,
				'post' => $post,
				'thread' => $thread,
				'forum' => $forum,
				'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
			);

			return $this->responseView('PostComments_ViewPublic', 'post_comment_delete', $viewParams);
		}
	}

	// Can report comments
	public function actionCommentReport()
	{
		$commentId = $this->_input->filterSingle('comment', XenForo_Input::UINT);
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);

		$model = $this->getModelFromCache('PostComments_Model_Comment');
		$comment = $model->getCommentById($commentId);

		if (!$model->canReportComment($comment))
		{
			throw $this->getErrorOrNoPermissionResponseException('do_not_have_permission');
		}

		if ($this->_request->isPost())
		{
			$message = $this->_input->filterSingle('message', XenForo_Input::STRING);

			if (!$message)
			{
				return $this->responseError(new XenForo_Phrase('please_enter_reason_for_reporting_this_message'));
			}

			$this->assertNotFlooding('report');

			/* @var $reportModel XenForo_Model_Report */
			$reportModel = XenForo_Model::create('XenForo_Model_Report');
			$reportModel->reportContent('post_comment', $comment, $message);

			$controllerResponse = $this->getPostSpecificRedirect($post, $thread);
			$controllerResponse->redirectMessage = new XenForo_Phrase('thank_you_for_reporting_this_message');
			return $controllerResponse;
		}
		else
		{
			$viewParams = array(
				'comment' => $comment,
				'post' => $post,
				'thread' => $thread,
				'forum' => $forum,
				'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
			);

			return $this->responseView('PostComments_ViewPublic', 'post_comment_report', $viewParams);
		}
	}

	// Show all comments
	public function actionComments()
	{
		// Define some variables
		$visitor = XenForo_Visitor::getInstance();
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);

		$model = $this->getModelFromCache('PostComments_Model_Comment');
		$comments = $model->getCommentsForPostId($postId);

		// Nothing to display if there are no comments
		if (!$comments)
		{
			return $this->responseMessage(new XenForo_Phrase('no_comments_to_display'));
		}

		// Get the proper permissions to be displayed
		foreach ($comments AS &$comment)
		{
			$comment = $model->prepareComment($comment);
		}

		$viewParams = array(
			'comments' => $comments,
			'post' => $post,
			'thread' => $thread,
			'forum' => $forum,
			'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
		);

		return $this->responseView('PostComments_ViewPublic', 'post_comments', $viewParams);
	}
}