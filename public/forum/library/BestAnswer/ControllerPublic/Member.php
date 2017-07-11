<?php

class BestAnswer_ControllerPublic_Member extends XFCP_BestAnswer_ControllerPublic_Member
{
	public function actionBestAnswers()
	{
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->assertUserProfileValidAndViewable($userId);
		
		/* @var $bestAnswerModel BestAnswer_Model_BestAnswer */
		$bestAnswerModel = $this->getModelFromCache('BestAnswer_Model_BestAnswer');
		
		$perPage = 5;
		
		$view = '';
		
		$lastPostId = $this->_input->filterSingle('last_post_id', XenForo_Input::UINT);
		if ($lastPostId)
		{
			$view = 'BestAnswer_ViewPublic_Member_BestAnswers';
		}
			
		$posts = $bestAnswerModel->getBestAnswersByUserId($userId, $perPage, $lastPostId);
		
		end($posts);
		$lastPostId = key($posts);
		reset($posts);
		
		$viewParams = array(
			'user' => $user,
			'posts' => $posts,
			'lastPostId' => $lastPostId
		);
		
		return $this->responseView($view, 'member_best_answers', $viewParams);
	}
	
	protected function _getNotableMembers($type, $limit)
	{
		if ($type == 'best_answers')
		{
			$userModel = $this->_getUserModel();
				
			$notableCriteria = array(
				'is_banned' => 0,
				'best_answer_count' => array('>', 0)
			);
			
			return array($userModel->getUsers($notableCriteria, array(
				'join' => XenForo_Model_User::FETCH_USER_FULL,
				'limit' => $limit,
				'order' => 'best_answer_count',
				'direction' => 'desc'
			)), 'best_answer_count');
		}
		
		return parent::_getNotableMembers($type, $limit);
	}
	
	/**
	 * @return XenForo_Model_Post
	 */
	protected function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}
}