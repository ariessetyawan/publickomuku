<?php

class KomuKu_Emoticons_XenForo_ControllerPublic_Account extends XFCP_KomuKu_Emoticons_XenForo_ControllerPublic_Account
{
	/**
	 * @return XenForo_ControllerResponse_View
	 */
	public function actionEmoticons()
	{
		$userEmoticonModel = $this->_getEmoticonModel();
		if(!$userEmoticonModel->canUseOwnEmoticons($error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}

		$visitor = XenForo_Visitor::getInstance();

		$emoticons = $userEmoticonModel->getEmoticonsByUserId($visitor['user_id']);
		$emoticons = $userEmoticonModel->prepareEmoticons($emoticons);

		$viewParams = array(
			'emoticons' => $emoticons,
			'canUploadEmoticon' => $userEmoticonModel->canUploadEmoticons(),
		);

		return $this->_getWrapper('account', 'emoticons',
			$this->responseView('KomuKu_Emoticons_ViewPublic_Emoticon_List', 'emoticon_list', $viewParams)
		);
	}

	public function actionEmoticonsEdit()
	{
		$emoticon = $this->_getEmoticonOrError();
		if(!$this->_getEmoticonModel()->canEditEmoticon($emoticon, $error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}

		if($this->_request->isPost())
		{
			$caption = $this->_input->filterSingle('caption', XenForo_Input::STRING);
			if(utf8_strlen($caption) < 1)
			{
				return $this->responseError(new XenForo_Phrase('please_enter_valid_value'));
			}

			$dw = XenForo_DataWriter::create('KomuKu_Emoticons_DataWriter_Emoticon');
			$dw->setExistingData($emoticon);
			$dw->set('caption', $caption);

			$dw->save();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->_buildLink('account/emoticons')
			);
		}
		else
		{
			$viewParams = array(
				'emoticon' => $emoticon
			);
			return $this->_getWrapper('account', 'emoticons',
				$this->responseView('KomuKu_Emoticons_ViewPublic_Emoticon_Edit', 'emoticon_edit', $viewParams)
			);
		}
	}

	public function actionEmoticonsDelete()
	{
		$emoticon = $this->_getEmoticonOrError();
		if(!$this->_getEmoticonModel()->canDeleteEmoticon($emoticon, $error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}

		$this->_checkCsrfFromToken($this->_input->filterSingle('t', XenForo_Input::STRING));
		
		$dw = XenForo_DataWriter::create('KomuKu_Emoticons_DataWriter_Emoticon');
		$dw->setExistingData($emoticon);
		$dw->delete();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$this->_buildLink('account/emoticons')
		);
	}

	/**
	 * Handle add new emoticon
	 *
	 * @return XenForo_ControllerResponse_Redirect
	 */
	public function actionEmoticonsAdd()
	{
		$this->_assertPostOnly();

		$userEmoticonModel = $this->_getEmoticonModel();
		if(!$userEmoticonModel->canUseOwnEmoticons($error) || !$userEmoticonModel->canUploadEmoticons($error))
		{
			throw $this->getErrorOrNoPermissionResponseException($error);
		}

		$input = $this->_input->filter(array(
			'emoticon_url' => XenForo_Input::STRING,
			'caption' => XenForo_Input::STRING,
			'text_replace' => XenForo_Input::STRING,
			'method' => XenForo_Input::STRING,
		));

		if('import' == $input['method'])
		{
			if(!Zend_Uri::check($input['emoticon_url']))
			{
				return $this->responseError(new XenForo_Phrase('please_enter_valid_url'));
			}

			$contents = @file_get_contents($input['emoticon_url']);
			$parts = @parse_url($input['emoticon_url']);

			if(!$contents OR !$parts)
			{
				// Could not get data from URL provide
				return $this->responseError(new XenForo_Phrase('emoticon_we_could_not_get_emoticon_data_from_provided_url'));
			}

			$tempEmoticon = tempnam(XenForo_Helper_File::getTempDir(), 'userEmoticon');
			file_put_contents($tempEmoticon, $contents);

			// Create new upload for import from URL
			$emoticon = new XenForo_Upload(basename($parts['path']), $tempEmoticon);
		}
		else if('upload' == $input['method'])
		{
			$emoticon = XenForo_Upload::getUploadedFile('emoticon');
			if(!$emoticon)
			{
				return $this->responseError(new XenForo_Phrase('uploaded_file_is_not_valid_image'));
			}
		}
		else
		{
			return $this->responseNoPermission();
		}

		$returned = $userEmoticonModel->doUpload($emoticon, $input);
		if(isset($returned['errors']))
		{
			return $this->responseError($returned['errors']);
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			$this->_buildLink('account/emoticons')
		);
	}

	protected function _getEmoticonOrError()
	{
		$id = $this->_input->filterSingle('id', XenForo_Input::UINT);
		$record = $this->_getEmoticonModel()->getEmoticonById($id, array(
			'join' => KomuKu_Emoticons_Model_Emoticon::FETCH_USER
		));

		if(!$record)
		{
			throw $this->responseException($this->responseError('emoticon_requested_record_not_found', 404));
		}

		return $this->_getEmoticonModel()->prepareEmoticon($record);
	}

	/**
	 * @return KomuKu_Emoticons_Model_Emoticon
	 */
	protected function _getEmoticonModel()
	{
		return $this->getModelFromCache('KomuKu_Emoticons_Model_Emoticon');
	}
}