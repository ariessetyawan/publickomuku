<?php

class KChaUser_ControllerPublic_Account extends XFCP_KChaUser_ControllerPublic_Account
{
	public function actionChangeUsername()
	{
		$visitor = XenForo_Visitor::getInstance();
		$model = $this->_getUsernameChangesModel();
		
		if (!$visitor->hasPermission('usernameChanges', 'changeUsername')) 
		{
			return $this->responseNoPermission();
		}
		
		$viewParams = array(
		    'lastChange' => $model->getUserLastChange($visitor['user_id']),
			'daysBetweenChanges' => $visitor->hasPermission('usernameChanges', 'changeUsername'),
			'canUsePrivateChange' => $visitor->hasPermission('usernameChanges', 'usePrivateChange'),
		);
		
		return $this->_getWrapper(
			'account', 'change-username',
			$this->responseView('KChaUser_ViewPublic_Account_ChangeUsername', 'KChaUser_change_username', $viewParams)
		);
	}
	public function actionChangeUsernameSave()
	{
		$this->_assertPostOnly();
		$visitor = XenForo_Visitor::getInstance();
		
		$newUsername = $this->_input->filterSingle('new_username', XenForo_Input::STRING);
		$isPrivate = $this->_input->filterSingle('is_private', XenForo_Input::UINT);
		
		$dw = XenForo_DataWriter::create('KChaUser_DataWriter_User');
		$dw->set('user_id', $visitor['user_id']);
		$dw->set('old_username', $visitor['username']);
		$dw->set('new_username', $newUsername);
		$dw->set('is_private', $isPrivate);
		$dw->save();
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('account/change-username')
		);
	}
	protected function _getUsernameChangesModel()
	{
		return $this->getModelFromCache('KChaUser_Model_Changes');
	}
}