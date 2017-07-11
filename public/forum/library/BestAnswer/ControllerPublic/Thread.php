<?php

class BestAnswer_ControllerPublic_Thread extends XFCP_BestAnswer_ControllerPublic_Thread
{
	protected function _getPostFetchOptions(array $thread, array $forum)
	{
		$response = parent::_getPostFetchOptions($thread, $forum);
		
		$response['join'] |= XenForo_Model_Post::FETCH_THREAD;
		$response['bestAnswerId'] = $thread['best_answer_id'];
		$response['alternativeBestAnswers'] = $thread['alternative_best_answers'];
		
		return $response;
	}
	
	public function actionIndex()
	{
		$response = parent::actionIndex();
		
		if (($response instanceof XenForo_ControllerResponse_View) AND !empty($response->params['posts']))
		{
			foreach ($response->params['posts'] AS &$post)
			{
				if (!empty($response->params['posts'][$post['post_id']]['attachments']))
				{
					$post['attachments'] = $response->params['posts'][$post['post_id']]['attachments'];
				}
			}
			
			$response->params['thread']['alternative_best_answers'] = array_flip(array_filter(explode(",", $response->params['thread']['alternative_best_answers'])));
		}
		
		return $response;
	}
}