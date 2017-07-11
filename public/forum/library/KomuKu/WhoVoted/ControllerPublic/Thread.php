<?php

class KomuKu_WhoVoted_ControllerPublic_Thread extends XFCP_KomuKu_WhoVoted_ControllerPublic_Thread
{
	public function actionWhoVoted()
	{
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('whoVotedGroupID', 'whoVotedID'))
		{
			throw $this->getNoPermissionResponseException();
		}
		
		// get threadId
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

		// get database
		$db = XenForo_Application::get('db');
		
		// get pollId
		$pollId = $db->fetchOne("
		SELECT poll_id
		FROM kmk_poll
		WHERE content_id = ?
		", $threadId);					

		// get results
		$results = $db->fetchAll("
		SELECT kmk_user.user_id, username
		FROM kmk_poll_vote
		INNER JOIN kmk_user ON kmk_user.user_id = kmk_poll_vote.user_id
		WHERE poll_id = ?
		GROUP BY kmk_user.user_id
		ORDER BY username ASC
		", $pollId);
		
		// prepare viewParams
		$viewParams = array(
			'results' => $results
		);
		
		// send to template
		return $this->responseView('KomuKu_WhoVoted_ViewPublic_WhoVoted', 'KomuKu_whovoted', $viewParams);
	}
}