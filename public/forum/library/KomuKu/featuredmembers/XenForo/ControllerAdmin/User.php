<?php
class KomuKu_featuredmembers_XenForo_ControllerAdmin_User extends XFCP_KomuKu_featuredmembers_XenForo_ControllerAdmin_User
{
	public function actionSave()
	{
		$return = parent::actionSave();

		if (is_object($return) && $return instanceof XenForo_ControllerResponse_Redirect && $return->redirectType == XenForo_ControllerResponse_Redirect::SUCCESS)
		{
			$db = XenForo_Application::getDb();
			$inputData = $this->_input->filter([
				'user_id' => XenForo_Input::UINT,
				'dad_fm_is_featured' => XenForo_Input::UINT
			]);
			$db->update('kmk_user', ['dad_fm_is_featured' => $inputData['dad_fm_is_featured']], 'user_id = ' . $db->quote($inputData['user_id']));

            $db = XenForo_Application::getDb();
            $inputData = $this->_input->filter([
                'user_id' => XenForo_Input::UINT,
                'dad_fm_is_verified' => XenForo_Input::UINT
            ]);
            $db->update('kmk_user', ['dad_fm_is_verified' => $inputData['dad_fm_is_verified']], 'user_id = ' . $db->quote($inputData['user_id']));
		}

		return $return;
	}
}

if (false)
{
	class XFCP_KomuKu_featuredmembers_XenForo_ControllerAdmin_User extends XenForo_ControllerAdmin_User {}
}