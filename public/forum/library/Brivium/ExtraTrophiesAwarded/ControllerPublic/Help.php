<?php

class Brivium_ExtraTrophiesAwarded_ControllerPublic_Help extends XFCP_Brivium_ExtraTrophiesAwarded_ControllerPublic_Help
{

	public function actionAwards()
	{
		$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');

		$viewParams = array(
			'trophies' => $trophyModel->prepareTrophies($trophyModel->getAllAwards())
		);

		return $this->_getWrapper('awards',
			$this->responseView('XenForo_ViewPublic_Help_Awards', 'BRETA_awards', $viewParams)
		);
	}
	
	public function actionLeaderboards()
	{
		$userModel = $this->getModelFromCache('XenForo_Model_User');
		$notableCriteria = array(
			'is_banned' => 0,
			'trophy_points' => array('>', 0)
		);
		
		$users = $userModel->getUsers($notableCriteria, array(
			'join' => XenForo_Model_User::FETCH_USER_FULL,
			'limit' => XenForo_Application::get('options')->BRETA_maximumLeaderBoard,
			'order' => 'trophy_points',
			'direction' => 'desc'
		));
		
		$viewParams = array(
			'users' => $users
		);

		return $this->_getWrapper('leaderboards',
			$this->responseView('XenForo_ViewPublic_Help_Leaderboards', 'BRETA_leaderboards', $viewParams)
		);
	}
}