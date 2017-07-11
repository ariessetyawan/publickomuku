<?php

class BestAnswer_Model_Post extends XFCP_BestAnswer_Model_Post
{
	public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		$response = parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);
		
		$response['showBestAnswer'] = $this->getModelFromCache('BestAnswer_Model_BestAnswer')->canMarkAsBestAnswer($post, $thread, $forum);
		
		if ($thread['best_answer_id'] == $post['post_id'])
		{
			$response['isBestAnswer'] = true;
		}
		
		return $response;
	}
	
	public function getPostsInThread($threadId, array $fetchOptions = array())
	{
		if (!XenForo_Application::getOptions()->bestAnswerEmbedBestAnswerInFirstPost && !XenForo_Application::getOptions()->bestAnswerDisplayBestAnswerLinkUnderFirstPost)
		{
			return parent::getPostsInThread($threadId, $fetchOptions);
		}
		
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		
		if ($limitOptions['offset'] != 0)
		{
			return parent::getPostsInThread($threadId, $fetchOptions);
		}
		
		$stateLimit = $this->prepareStateLimitFromConditions($fetchOptions, 'post');
		$joinOptions = $this->preparePostJoinOptions($fetchOptions);
		
		$params = array($threadId);
		if ($limitOptions['offset'] == 0)
		{
			if (!empty($fetchOptions['bestAnswerId']) && XenForo_Application::getOptions()->bestAnswerEmbedBestAnswerInFirstPost)
			{
				$params[] = $fetchOptions['bestAnswerId'];
			}
			
			if (!empty($fetchOptions['alternativeBestAnswers']) && XenForo_Application::getOptions()->bestAnswerDisplayBestAnswerLinkUnderFirstPost)
			{
				$params[] = $fetchOptions['alternativeBestAnswers'];
			}
		}
		
		$posts = $this->fetchAllKeyed('
			SELECT post.*
				' . $joinOptions['selectFields'] . '
			FROM kmk_post AS post
			' . $joinOptions['joinTables'] . '
			WHERE (
					(post.thread_id = ? ' . $this->addPositionLimit('post', $limitOptions['limit'], $limitOptions['offset']) . ')
					' . ($limitOptions['offset'] == 0 && XenForo_Application::getOptions()->bestAnswerEmbedBestAnswerInFirstPost && !empty($fetchOptions['bestAnswerId']) ? 'OR post.post_id = ?' : '') . '
					' . ($limitOptions['offset'] == 0 && XenForo_Application::getOptions()->bestAnswerDisplayBestAnswerLinkUnderFirstPost && !empty($fetchOptions['alternativeBestAnswers']) ? 'OR post.post_id IN (?)' : '') . '
				)
				AND (' . $stateLimit . ')
			ORDER BY post.position ASC, post.post_date ASC
		', 'post_id', $params);
		
		
		if (
			$limitOptions['offset'] == 0 &&
			!empty($fetchOptions['bestAnswerId']) &&
			isset($posts[$fetchOptions['bestAnswerId']]) &&
			$posts[$fetchOptions['bestAnswerId']]['position'] >= $limitOptions['limit']
		)
		{
			$posts[$fetchOptions['bestAnswerId']]['isBestAnswerCopy'] = true;
		}
		
		if (
		$limitOptions['offset'] == 0 &&
		!empty($fetchOptions['alternativeBestAnswers'])
		)
		{
			$alternativeBestAnswers = explode(",", $fetchOptions['alternativeBestAnswers']);
			foreach ($alternativeBestAnswers AS $alternativeAnswer)
			{
				if (
					isset($posts[$alternativeAnswer]) &&
					$posts[$alternativeAnswer]['position'] >= $limitOptions['limit']
				)
				{
					$posts[$alternativeAnswer]['isBestAnswerCopy'] = true;
				}
			}
		}
		
		return $posts;
	}
}