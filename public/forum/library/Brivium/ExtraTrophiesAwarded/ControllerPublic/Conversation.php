<?php

class Brivium_ExtraTrophiesAwarded_ControllerPublic_Conversation extends XFCP_Brivium_ExtraTrophiesAwarded_ControllerPublic_Conversation
{
	public function actionView()
	{
		$response = parent::actionView();
		
		$positionAward = XenForo_Application::get('options')->BRETA_positionAwardOfUser;
		$response->params['brViewUerInfo'] = $positionAward['bottom_of_user_infomation'];
		$response->params['brShowTrophyIcon'] = XenForo_Application::get('options')->BRETA_showTrophyIcon;
		$response->params['brShowLevel'] = XenForo_Application::get('options')->BETA_showLevel;
		
		if(!$response->params['brViewUerInfo'] || (!$response->params['brShowTrophyIcon'] && !$response->params['brShowLevel'])){
			return $response;
		}
		
		$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
		
		$userIds = array();
			
		foreach($response->params['messages'] AS $messageId => &$message){
			if($message['trophy_points'] && $message['breta_next_level']){
				if($message['breta_next_level'] !== $message['breta_curent_level']){
					$percentTrophy = ($message['trophy_points'] - $message['breta_curent_level']) / ($message['breta_next_level'] - $message['breta_curent_level']) * 100;
				}else{
					$percentTrophy = 100;
				}
			}else{
				$percentTrophy = 0;
			}
			$message['percentTrophy'] = round($percentTrophy);
			
			if (!in_array($message['user_id'], $userIds)) {
				$userIds[] = $message['user_id'];
			}
		}
		
		$usersAwards = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy')
			->getUserAwardsByUserIds($userIds);
	
		foreach($response->params['messages'] AS $messageId => &$message){
			if (!empty($usersAwards[$message['user_id']])) {
				$message['awards'] = $trophyModel->prepareTrophies($usersAwards[$message['user_id']]);
			} else {
				$message['awards'] = array();
			}
		}
		
		return $response;
	}
	
	public function actionSaveMessage()
	{
		$response = parent::actionSaveMessage();
		
		if ($this->_noRedirect())
		{
			$positionAward = XenForo_Application::get('options')->BRETA_positionAwardOfUser;
			$response->params['brViewUerInfo'] = $positionAward['bottom_of_user_infomation'];
			$response->params['brShowTrophyIcon'] = XenForo_Application::get('options')->BRETA_showTrophyIcon;
			$response->params['brShowLevel'] = XenForo_Application::get('options')->BETA_showLevel;
			
			if(!$response->params['brViewUerInfo'] || (!$response->params['brShowTrophyIcon'] && !$response->params['brShowLevel'])){
				return $response;
			}
			
			$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
			
			$message = $response->params['message'];

			$response->params['message']['awards'] = $trophyModel->prepareTrophies(
				$trophyModel->getAwardsForUserId($message,
				array('limit' => XenForo_Application::get('options')->BRETA_defaultTrophyIcons)));
					
			if($message['trophy_points'] && $message['breta_next_level']){
				if($message['breta_next_level'] !== $message['breta_curent_level']){
					$percentTrophy = ($message['trophy_points'] - $message['breta_curent_level']) / ($message['breta_next_level'] - $message['breta_curent_level']) * 100;
				}else{
					$percentTrophy = 100;
				}
			}else{
				$percentTrophy = 0;
			}
			$response->params['message']['percentTrophy'] = round($percentTrophy);
		}
		
		return $response;
	}
	
	public function actionInsertReply()
	{
		$response = parent::actionInsertReply();
		if ($this->_noRedirect() && $this->_input->inRequest('last_date'))
		{
			$positionAward = XenForo_Application::get('options')->BRETA_positionAwardOfUser;
			$response->params['brViewUerInfo'] = $positionAward['bottom_of_user_infomation'];
			$response->params['brShowTrophyIcon'] = XenForo_Application::get('options')->BRETA_showTrophyIcon;
			$response->params['brShowLevel'] = XenForo_Application::get('options')->BETA_showLevel;
			
			if(!$response->params['brViewUerInfo'] || (!$response->params['brShowTrophyIcon'] && !$response->params['brShowLevel'])){
				return $response;
			}
			
			$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
			
			$userIds = array();
			
			foreach($response->params['messages'] AS $messageId => &$message){
				if($message['trophy_points'] && $message['breta_next_level']){
					if($message['breta_next_level'] !== $message['breta_curent_level']){
						$percentTrophy = ($message['trophy_points'] - $message['breta_curent_level']) / ($message['breta_next_level'] - $message['breta_curent_level']) * 100;
					}else{
						$percentTrophy = 100;
					}
				}else{
					$percentTrophy = 0;
				}
				$message['percentTrophy'] = round($percentTrophy);
				
				if (!in_array($message['user_id'], $userIds)) {
					$userIds[] = $message['user_id'];
				}
			}
			
			$usersAwards = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy')
				->getUserAwardsByUserIds($userIds);
		
			foreach($response->params['messages'] AS $messageId => &$message){
				if (!empty($usersAwards[$message['user_id']])) {
					$message['awards'] = $trophyModel->prepareTrophies($usersAwards[$message['user_id']]);
				} else {
					$message['awards'] = array();
				}
			}
		}
		
		return $response;
	}
}