<?php
class KomuKu_FollowingAlerts_XenForo_ControllerPublic_Member extends XFCP_KomuKu_FollowingAlerts_XenForo_ControllerPublic_Member
{
	public function actionFollow()
	{
		$GLOBALS['KomuKu_FollowingAlerts_ControllerPublic_Member::actionFollow'] = $this;
		
		return parent::actionFollow();
	}

	public function followingAlerts_actionFollow(XenForo_DataWriter_Follower $dw)
	{
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);

		$alerts = $this->_input->filter(array(
			'thread_create' => XenForo_Input::UINT,
			'post_insert' => XenForo_Input::UINT,
			'profile_post' => XenForo_Input::UINT,
			'resource_create' => XenForo_Input::UINT
		));

		$alerts = $this->getModelFromCache('KomuKu_FollowingAlerts_Model_Follow')->verifyActions($alerts);
		if (!$alerts) {
			$dw->error(new XenForo_Phrase('please_enter_valid_value'));
		}

		$dw->set('alert_preferences', $alerts);

		unset($GLOBALS['KomuKu_FollowingAlerts_ControllerPublic_Member::actionFollow']);
	}
	
	public function actionAlertPreferences()
	{
		$this->_assertRegistrationRequired();
		
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->assertUserProfileValidAndViewable($userId);
		
		$visitor = XenForo_Visitor::getInstance();
		$followModel = $this->getModelFromCache('KomuKu_FollowingAlerts_Model_Follow');
		
		if(!$this->_getUserModel()->isFollowing($user['user_id']))
		{
			return $this->responseNoPermission();
		}

		$followRecord = $this->_getUserModel()->getFollowRecord($visitor['user_id'], $user['user_id']);
		$followRecord['alert_preferences'] = @unserialize($followRecord['alert_preferences']);

		$viewParams = array(
			'user' => $user,

			'followRecord' => !empty($followRecord['alert_preferences']) ? $followRecord['alert_preferences'] : array(),

			'followthread' => $followModel->canFollowThread(),
			'followpost' => $followModel->canFollowPost(),
			'followstatus' => $followModel->canFollowStatus(),
			'followresource' => $followModel->canFollowResource()
		);

		return $this->responseView('', 'member_following_alerts_preferences', $viewParams);
	}

	public function actionFollowSave()
	{
		$this->_assertPostOnly();
		
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->assertUserProfileValidAndViewable($userId);

		$alerts = $this->_input->filter(array(
			'thread_create' => XenForo_Input::UINT,
			'post_insert' => XenForo_Input::UINT,
			'profile_post' => XenForo_Input::UINT,
			'resource_create' => XenForo_Input::UINT
		));
		$alerts = $this->getModelFromCache('KomuKu_FollowingAlerts_Model_Follow')->verifyActions($alerts);
		if (!$alerts) {
			return $this->responseNoPermission(); // hack?
		}

		$visitor = XenForo_Visitor::getInstance();
		if(!XenForo_Visitor::getUserId())
		{
			return $this->responseNoPermission();
		}

		if (!$this->_getUserModel()->isFollowing($user['user_id']))
		{
			return $this->responseNoPermission();
		}

		$followRecord = $this->_getUserModel()->getFollowRecord($visitor['user_id'], $userId);

		$dw = XenForo_DataWriter::create('XenForo_DataWriter_Follower', XenForo_DataWriter::ERROR_SILENT);		
		if($dw->setExistingData($followRecord))
		{
			$dw->set('alert_preferences', $alerts);
			$dw->save();
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$this->getDynamicRedirect()
		);
	}

	public function actionFollowing()
	{
		$response = parent::actionFollowing();
		if($response instanceof XenForo_ControllerResponse_View
			AND $response->templateName == 'member_following')
		{
			$check = false;
			if(XenForo_Visitor::getUserId() == $response->params['user']['user_id'])
			{
				$check = true;
			}
		
			$response->params['check'] = $check;
			$response->templateName = 'member_following_alerts'; // switched template.
		}
		
		return $response;
	}
	
}