<?php
class KomuKu_FollowingAlerts_XenForo_ControllerPublic_Account extends XFCP_KomuKu_FollowingAlerts_XenForo_ControllerPublic_Account
{
	public function actionFollowSettings()
	{
		$user = XenForo_Visitor::getInstance()->toArray();
		
		if ($this->_request->isPost())
		{
			$input = $this->_input->filter(array(
				'alerts' => XenForo_Input::ARRAY_SIMPLE,
				'send_alert' => XenForo_Input::UINT,
				'send_email' => XenForo_Input::UINT
			));

			$writer = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$writer->setExistingData(XenForo_Visitor::getUserId());
			$writer->set('follow_settings', $input);
			$writer->save();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect()
			);
		}
		else
		{
			$setting = @unserialize($user['follow_settings']);
			return $this->_getWrapper(
				'account', 'FollowSetting',
				$this->responseView('', 'follow_setting_save', array(
					'user' => $user,
					'followRecord' => $setting
				))
			);
		}
		
	}
}