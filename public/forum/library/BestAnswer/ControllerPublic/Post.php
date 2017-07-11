<?php

class BestAnswer_ControllerPublic_Post extends XFCP_BestAnswer_ControllerPublic_Post
{
	public function actionBestAnswer()
	{
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);
		
		$bestAnswerModel = $this->_getBestAnswerModel();

		if (!$bestAnswerModel->canMarkAsBestAnswer($post, $thread, $forum))
		{
			return $this->responseNoPermission();
		}
		
		if (!$bestAnswerModel->postMarkedAsBestAnswer($post, $thread))
		{
			if ($this->_request->isPost())
			{
				$bestAnswerModel->markAsBestAnswer($thread, $post);
				
				$responseParams = array(
					'community' => XenForo_Application::get('options')->bestAnswerWhoChooses == BestAnswer_Model_BestAnswer::CHOSEN_BY_COMMUNITY,
					'marked' => true,
					'markBestAnswerPhrase' => new XenForo_Phrase('mark_as_best_answer_button'),
					'unmarkBestAnswerPhrase' => new XenForo_Phrase('unmark_best_answer')
				);
				return $this->baRedirect(
					$post,
					$thread,
					new XenForo_Phrase('post_has_been_marked_as_best_answer'),
					$responseParams
				);
			}
			else
			{
				$viewParams = array(
					'post' => $post,
					'thread' => $thread,
					'forum' => $forum,
					'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum)
				);
			
				return $this->responseView('XenForo_ViewPublic_Post_BestAnswer', 'mark_as_best_answer', $viewParams);
			}
		}
		else
		{
			if ($this->_request->isPost())
			{
				$bestAnswerModel->unMarkAsBestAnswer($thread, $post);
			
				$responseParams = array(
					'community' => XenForo_Application::get('options')->bestAnswerWhoChooses == BestAnswer_Model_BestAnswer::CHOSEN_BY_COMMUNITY,
					'marked' => false,
					'markBestAnswerPhrase' => new XenForo_Phrase('mark_as_best_answer_button'),
					'unmarkBestAnswerPhrase' => new XenForo_Phrase('unmark_best_answer')
				);
				return $this->baRedirect(
					$post,
					$thread,
					new XenForo_Phrase('post_has_been_unmarked_as_best_answer'),
					$responseParams
				);
			}
			else
			{
				$viewParams = array(
					'post' => $post,
					'thread' => $thread,
					'forum' => $forum,
					'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
					'marked' => $bestAnswerModel->postMarkedAsBestAnswer($post, $thread)
				);
					
				return $this->responseView('XenForo_ViewPublic_Post_BestAnswer', 'mark_as_best_answer', $viewParams);
			}
		}
	}
	
	public function actionPreview()
	{
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

		$visitor = XenForo_Visitor::getInstance();

		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId, array('join' => XenForo_Model_Post::FETCH_USER));
		
		$postModel = $this->_getPostModel();
		
		if (!$postModel->canViewPost($post, $thread, $forum))
		{
			return $this->responseView('XenForo_ViewPublic_Post_Preview', '', array('post' => false));
		}

		$viewParams = array(
			'post' => $post,
			'thread' => $thread,
			'forum' => $forum
		);

		return $this->responseView('BestAnswer_ViewPublic_Post_Preview', 'best_answer_post_preview', $viewParams);
	}
	
	/**
	 * @return BestAnswer_Model_BestAnswer
	 */
	protected function _getBestAnswerModel()
	{
		return $this->getModelFromCache('BestAnswer_Model_BestAnswer');
	}
	
	/**
	 * @return XenForo_Model_Post
	 */
	protected function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}
	
	public function baRedirect(array $post, array $thread, $message, $params)
	{
		$page = floor($post['position'] / XenForo_Application::get('options')->messagesPerPage) + 1;
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('threads', $thread, array('page' => $page)) . '#post-' . $post['post_id'],
			$message, $params
		);
	}
}