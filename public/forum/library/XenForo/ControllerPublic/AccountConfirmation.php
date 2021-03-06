<?php

/**
 * Controller for account/user confirmation.
 *
 * @package XenForo_UserConfirmation
 */
class XenForo_ControllerPublic_AccountConfirmation extends XenForo_ControllerPublic_Abstract
{
	/**
	 * Handles email confirmation.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionEmail()
	{
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		if (!$userId)
		{
			return $this->responseError(new XenForo_Phrase('no_account_specified'));
		}

		$confirmationModel = $this->_getUserConfirmationModel();

		$confirmation = $confirmationModel->getUserConfirmationRecord($userId, 'email');
		if (!$confirmation)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('index')
			);
		}

		$confirmationKey = $this->_input->filterSingle('c', XenForo_Input::STRING);
		if ($confirmationKey)
		{
			$accountConfirmed = $confirmationModel->validateUserConfirmationRecord($confirmationKey, $confirmation);
		}
		else
		{
			$accountConfirmed = false;
		}

		if ($accountConfirmed)
		{
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$dw->setExistingData($userId);
			if ($dw->get('user_state') == 'email_confirm')
			{
				// don't log when changing from initial confirm state as it creates a lot of noise
				$dw->setOption(XenForo_DataWriter_User::OPTION_LOG_CHANGES, false);
			}
			$dw->advanceRegistrationUserState();
			$dw->save();

			$confirmationModel->deleteUserConfirmationRecord($userId, 'email');

			$user = $dw->getMergedData();

			/** @var XenForo_Model_User $userModel */
			$userModel = $this->getModelFromCache('XenForo_Model_User');
			$userModel->sendWelcomeContact($user, $dw->getExisting('user_state'));

			// log the IP of the user
			XenForo_Model_Ip::log($user['user_id'], 'user', $user['user_id'], 'account-confirmation');

			$visitor = XenForo_Visitor::getInstance();
			if ($visitor['user_id'] == $user['user_id'])
			{
				$visitor['user_state'] = $user['user_state'];
			}

			$viewParams = array(
				'user' => $user,
				'oldUserState' => $dw->getExisting('user_state'),
				'canEditProfile' => $visitor->canEditProfile()
			);

			return $this->responseView('XenForo_ViewPublic_Register_Confirm', 'register_confirm', $viewParams);
		}
		else
		{
			return $this->responseError(new XenForo_Phrase('your_account_could_not_be_confirmed')); // TODO: users need to be able to do something
		}
	}

	/**
	 * Resends the account confirmation if needed.
	 *
	 * @package XenForo_ControllerPublic_AccountConfirmation
	 */
	public function actionResend()
	{
		$visitor = XenForo_Visitor::getInstance();

		if (!$visitor['user_id'])
		{
			return $this->responseNoPermission();
		}

		if ($visitor['user_state'] != 'email_confirm' && $visitor['user_state'] != 'email_confirm_edit')
		{
			return $this->responseError(new XenForo_Phrase('your_account_does_not_require_confirmation'));
		}

		$confirmationModel = $this->_getUserConfirmationModel();
		$needsCaptcha = $confirmationModel->confirmationResendRequiresCaptcha($visitor['user_id'], 'email');

		if ($this->isConfirmedPost())
		{
			if ($needsCaptcha && !XenForo_Captcha_Abstract::validateDefault($this->_input, true))
			{
				return $this->responseError(
					new XenForo_Phrase('did_not_complete_the_captcha_verification_properly')
				);
			}

			$this->_getUserConfirmationModel()->sendEmailConfirmation($visitor->toArray());

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('index'),
				new XenForo_Phrase('confirmation_email_has_been_resent')
			);
		}
		else
		{
			$viewParams = array();

			if ($needsCaptcha)
			{
				$viewParams['captcha'] = XenForo_Captcha_Abstract::createDefault(true);
			}

			return $this->responseView('XenForo_ViewPublic_AccountConfirmation_Resend', 'account_confirm_resend', $viewParams);
		}
	}

	protected function _assertViewingPermissions($action) {}
	protected function _assertCorrectVersion($action) {}
	protected function _assertBoardActive($action) {}
	protected function _assertTfaRequirement($action) {}
	public function updateSessionActivity($controllerResponse, $controllerName, $action) {}

	/**
	 * @return XenForo_Model_UserConfirmation
	 */
	protected function _getUserConfirmationModel()
	{
		return $this->getModelFromCache('XenForo_Model_UserConfirmation');
	}
}