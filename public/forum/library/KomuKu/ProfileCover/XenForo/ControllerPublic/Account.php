<?php

class KomuKu_ProfileCover_XenForo_ControllerPublic_Account extends XFCP_KomuKu_ProfileCover_XenForo_ControllerPublic_Account
{
	public function actionCoverUpload()
	{
		$this->_assertCanUploadCover();

		return $this->_getWrapper('account', 'coverUpload',
			$this->responseView('KomuKu_ProfileCover_ViewPublic_Account_CoverUpload', 'profile_cover_upload', array())
		);
	}

	public function actionCoverDoUpload()
	{
		$this->_assertPostOnly();
		$this->_assertCanUploadCover();

		$user = XenForo_Visitor::getInstance();
		$maxFilesize = $user->hasPermission('general', 'cover_maxFilesize');
		$maxFilesize = $maxFilesize * 1024;

		$cover = XenForo_Upload::getUploadedFile('cover');
		if (! $cover)
		{
			return $this->responseError(new XenForo_Phrase('profile_cover_the_cover_uploaded_is_not_an_image'));
		}

		if (filesize($cover->getTempFile()) > $maxFilesize AND $maxFilesize != -1024)
		{
			return $this->responseError(new XenForo_Phrase('profile_cover_allow_x_of_filesize', array(
				'max' => XenForo_Locale::numberFormat($maxFilesize, 'size')
			)));
		}

		if (! $cover)
		{
			return $this->responseError(new XenForo_Phrase('profile_cover_the_cover_uploaded_is_not_an_image'));
		}

		if (! $cover->isImage())
		{
			// Uploaded is not image file
			return $this->responseError(new XenForo_Phrase('profile_cover_the_cover_uploaded_is_not_an_image'));
		}

		if ($cover->getImageInfoField('width') < 850)
		{
			return $this->responseError(new XenForo_Phrase('profile_cover_please_upload_an_cover_least_x_pixels_of_width'));
		}

		$helper = new KomuKu_ProfileCover_Cover($cover, $this, array('cropX' => 0, 'cropY' => 0));
		$helper->doCrop();

		$result = $helper->getResult();
		if (! empty($result))
		{
			$error = array();
			$this->_saveVisitorSettings(array('cover_date' => XenForo_Application::$time), $error);

			if ($error)
			{
				KomuKu_ProfileCover_Cover::deleteCover($user['user_id']);
				return $this->responseError($error);
			}

		}
		else
		{
			return $this->responseError(new XenForo_Phrase('profile_cover_oops_something_went_wrong'));
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$this->_buildLink('members', $user)
		);
	}

	public function actionCoverDoCrop()
	{
		$this->_assertPostOnly();
		$this->_assertCanUploadCover();

		$crops = $this->_input->filter(array(
			'cropX' => XenForo_Input::UINT,
			'cropY' => XenForo_Input::UINT,
			'cropW' => XenForo_Input::UINT,
			'cropH' => XenForo_Input::UINT,
			'containerW' => XenForo_Input::UINT,
		));

		$cover = new KomuKu_ProfileCover_Cover(null, $this, $crops);
		$cover->doCrop();

		$result = $cover->getResult();
		if (! empty($result))
		{
			$error = array();
			$this->_saveVisitorSettings(array('cover_date' => XenForo_Application::$time), $error);
		}
		else
		{
			return $this->responseError(new XenForo_Phrase('profile_cover_oops_something_went_wrong'));
		}
		$result = array_merge($result, $crops);

		return $this->responseView('KomuKu_ProfileCover_ViewPublic_Account_DoCrop', '', array(
			'result' => $result
		));
	}

	protected function _assertCanUploadCover()
	{
		if (! KomuKu_ProfileCover_Cover::assertCanUploadCover())
		{
			throw $this->getNoPermissionResponseException();
		}
	}

}

