<?php

class KChaUser_ControllerPublic_Members extends XFCP_KChaUser_ControllerPublic_Members
{
	public function actionUsernameChanges()
	{
		$model = $this->getModelFromCache('KChaUser_Model_Changes');
		
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->assertUserProfileValidAndViewable($userId);
		
		$usernameChanges = $model->getAllChangesForUser($user['user_id']);

		$viewParams = array(
		    'usernameChanges' => $usernameChanges
		);
		
		return $this->responseView('KChaUser_ViewPublic_Members_UsernameChanges', 'KChaUser_change_username_logs', $viewParams);
	}
	public function actionDeleteLogs()
	{
		$model = $this->getModelFromCache('KChaUser_Model_Changes');
		
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->assertUserProfileValidAndViewable($userId);
		
		if ($this->isConfirmedPost())
		{
			$model->deleteLogsForUser($user['user_id']);
			
			return $this->responseRedirect(
			    XenForo_ControllerResponse_Redirect::SUCCESS,
			    XenForo_Link::buildPublicLink('members', $user),
				new XenForo_Phrase('KChaUser_change_username_logs_successfully_deleted')
		    );
		}
		else
		{
			$viewParams = array(
			    'user' => $user,
			);
			
			return $this->responseView('KChaUser_ViewPublic_Members_DeleteChanges', 'KChaUser_change_username_delete_logs', $viewParams);
		}
	}
	public function actionMember()
	{
		$response = parent::actionMember();
		
		if ($response instanceof XenForo_ControllerResponse_View)
		{
			$visitor = XenForo_Visitor::getInstance();
			
			$response->params['canDeleteUsernameChangesLogs'] = $visitor->hasPermission('usernameChanges', 'deleteUsernameChangesLogs');
		}
		
		return $response;
	}
}