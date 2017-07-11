<?php

class BestAnswer_Model_Thread extends XFCP_BestAnswer_Model_Thread
{
	public function prepareThreadConditions(array $conditions, array &$fetchOptions)
	{
		$response = parent::prepareThreadConditions($conditions, $fetchOptions);
		
		if (isset($conditions['answered']))
		{
			if ($conditions['answered'] === 1)
			{
				$response .= " AND thread.best_answer_id != 0";
			}
			else if ($conditions['answered'] === 0)
			{
				$response .= " AND thread.best_answer_id = 0";
			}
		}
		
		return $response;
	}
	
	public function prepareThreadFetchOptions(array $fetchOptions)
	{
		$response = parent::prepareThreadFetchOptions($fetchOptions);
		
		if (XenForo_Application::get('options')->bestAnswerWhoChooses == BestAnswer_Model_BestAnswer::CHOSEN_BY_COMMUNITY)
		{
			$response['selectFields'] .= ',
				vote.post_id AS user_best_answer_id';
			$response['joinTables'] .= '
				LEFT JOIN kmk_best_answer_vote AS vote
					ON (vote.thread_id = thread.thread_id AND vote.user_id = ' . XenForo_Visitor::getUserId() . ')';
		}
		
		return $response;
	}
}