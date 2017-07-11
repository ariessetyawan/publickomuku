<?php


class KomuKu_ProfileCover_XenForo_ControllerPublic_Member extends XFCP_KomuKu_ProfileCover_XenForo_ControllerPublic_Member
{
	public function actionMember()
	{
		$response = parent::actionMember();
		if ($response instanceof XenForo_ControllerResponse_View)
		{
			$viewParams =& $response->params;

			$visitor = XenForo_Visitor::getInstance();
			if ($viewParams['user']['user_id'] == XenForo_Visitor::getUserId())
			{
				$viewParams['cover_canUpload'] = ($visitor->hasPermission('general', 'cover_upload') 
					&& $visitor->hasPermission('general', 'cover_maxFilesize'));
			}

			$viewParams['cover_canDelete'] = ($visitor->hasPermission('general', 'cover_deleteAny') 
					&& !empty($viewParams['user']['cover_date']));

			$permissions = XenForo_Permission::unserializePermissions($viewParams['user']['global_permission_cache']);
			$enabled = !empty($viewParams['user']['cover_date']) ? true : false;

			if (! XenForo_Permission::hasPermission($permissions, 'general', 'cover_upload')
				OR ! XenForo_Permission::hasPermission($permissions, 'general', 'cover_maxFilesize'))
			{
				$enabled = false;
			}

			$viewParams['cover_enabled'] = $enabled;
		}

		return $response;
	}

	public function actionCoverDelete()
	{
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->assertUserProfileValidAndViewable($userId);

		if (! XenForo_Visitor::getInstance()->hasPermission('general', 'cover_deleteAny'))
		{
			throw $this->getNoPermissionResponseException();
		}

		if (empty($user['cover_date']))
		{
			return $this->responseError(new XenForo_Phrase('profile_cover_the_user_x_did_not_have_an_custom_cover', array(
				'name' => $user['username']
			)));
		}

		KomuKu_ProfileCover_Cover::deleteCover($user['user_id']);
		$dw = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($user);

		$dw->set('cover_date', 0);
		$dw->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$this->_buildLink('members', $user)
		);
	}

}