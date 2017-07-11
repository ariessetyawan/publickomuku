<?php
class Brivium_ExtraTrophiesAwarded_ControllerPublic_Member extends XFCP_Brivium_ExtraTrophiesAwarded_ControllerPublic_Member
{
	public function actionMember()
	{
		$response = parent::actionMember();
		
		$response->params['brShowLevel'] = XenForo_Application::get('options')->BETA_showLevel;
		
		if (!$this->_input->filterSingle('card', XenForo_Input::UINT) && $response->params['brShowLevel'])
		{
			$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
			$sumTrophyPoints = $trophyModel->getSumTrophyPoints();
			$user = $response->params['user'];
			if($user['trophy_points'] && $user['breta_next_level']){
				if($user['breta_next_level'] !== $user['breta_curent_level']){
					$percentTrophy = ($user['trophy_points'] - $user['breta_curent_level']) / ($user['breta_next_level'] - $user['breta_curent_level']) * 100;
				}else{
					$percentTrophy = 100;
				}
			}else{
				$percentTrophy = 0;
			}

			$response->params['user']['percentTrophy'] = round($percentTrophy);
			$response->params['user']['sumTrophyPoints'] = $sumTrophyPoints;
		}
		
		return $response;
	}
	public function actionCard()
	{
		$response = parent::actionCard();
		
		$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
		$user = $response->params['user'];

		$response->params['user']['awards'] = $trophyModel->prepareTrophies(
			$trophyModel->getAwardsForUserId($user,
			array('limit' => XenForo_Application::get('options')->BRETA_defaultTrophyIcons)));
				
		$positionAward = XenForo_Application::get('options')->BRETA_positionAwardOfUser;
		$response->params['brViewCard'] = $positionAward['member_card'] && XenForo_Application::get('options')->BRETA_showTrophyIcon;
		
		return $response;
	}
	
	public function actionAwards()
	{
		$visitor = XenForo_Visitor::getInstance();
		
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->getUserOrError($userId);
		
		$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
		
		$awards = $trophyModel->prepareTrophies($trophyModel->getAwardsForUserById($user['user_id']));
		
		if($visitor['user_id'] === $userId && $visitor->hasPermission('general', 'BRETA_canSelectTrophies')){
			$canShowIcons = true;
		}else{
			$canShowIcons = false;
		}
		
		$viewParams = array(
			'user' => $user,
			'awards' => $awards,
			'canShowIcons' => $canShowIcons
		);

		return $this->responseView('XenForo_ViewPublic_Member_BRETAAwards', 'BRETA_award_list', $viewParams);
	}
	
	public function actionShowIcon()
	{
		$trophyId = $this->_input->filterSingle('trophyId', XenForo_Input::UINT);
		
		if($trophyId){
			$value = $this->_input->filterSingle('value', XenForo_Input::UINT);
			
			$trophyModel = $this->getModelFromCache('Brivium_ExtraTrophiesAwarded_Model_exTrophy');
			
			if($value){
				$trophyModel->_showIcon($trophyId);
			}else{
				$trophyModel->_hideIcon($trophyId);
			}
			$viewParams = array(
				'trophy' => $trophyId
			);
		}else{
			$viewParams = array();
		}
		return $this->responseView('Brivium_ResourceWishlist_ViewPublic_Member_TrophyIcon', '', $viewParams);
	}
}