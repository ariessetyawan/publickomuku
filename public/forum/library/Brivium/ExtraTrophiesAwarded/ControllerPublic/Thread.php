<?php

class Brivium_ExtraTrophiesAwarded_ControllerPublic_Thread extends XFCP_Brivium_ExtraTrophiesAwarded_ControllerPublic_Thread
{
	protected function _getDefaultViewParams(array $forum, array $thread, array $posts, $page = 1, array $viewParams = array())
	{
		$response = parent::_getDefaultViewParams($forum, $thread, $posts, $page, $viewParams);
		
		$positionAward = XenForo_Application::get('options')->BRETA_positionAwardOfUser;
		$response['brViewUerInfo'] = $positionAward['bottom_of_user_infomation'];
		$response['brShowTrophyIcon'] = XenForo_Application::get('options')->BRETA_showTrophyIcon;
		$response['brShowLevel'] = XenForo_Application::get('options')->BETA_showLevel;
		
		if(!$response['brViewUerInfo'] || (!$response['brShowTrophyIcon'] && !$response['brShowLevel'])){
			return $response;
		}
		
		$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
		
		$userIds = array();
		
		foreach($response['posts'] AS $postId => &$post){
				
				if($post['trophy_points'] && $post['breta_next_level']){
					if($post['breta_next_level'] !== $post['breta_curent_level']){
						$percentTrophy = ($post['trophy_points'] - $post['breta_curent_level']) / ($post['breta_next_level'] - $post['breta_curent_level']) * 100;
					}else{
						$percentTrophy = 100;
					}
				}else{
					$percentTrophy = 0;
				}
				
				$post['percentTrophy'] = round($percentTrophy);
				
				if (!in_array($post['user_id'], $userIds)) {
					$userIds[] = $post['user_id'];
				}
		}
		
		$usersAwards = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy')
			->getUserAwardsByUserIds($userIds);
		
		foreach($response['posts'] AS $postId => &$post){
			if (!empty($usersAwards[$post['user_id']])) {
				$post['awards'] = $trophyModel->prepareTrophies($usersAwards[$post['user_id']]);
			} else {
				$post['awards'] = array();
			}
		}
		
		return $response;
	}
}