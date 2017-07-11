<?php

class KomuKu_Emoticons_XenForo_ControllerAdmin_Log extends XFCP_KomuKu_Emoticons_XenForo_ControllerAdmin_Log
{
	public function actionEmoticons()
	{
		$userModel = $this->getModelFromCache('XenForo_Model_User');

		if($this->_request->isPost())
		{
			$names = $this->_input->filterSingle('names', XenForo_Input::STRING);
			$names = array_map('trim', explode(',', $names));

			$users = array();
			if(!empty($names))
			{
				$users = $userModel->getUsersByNames($names);
			}

			$conditions = array();
			if($users)
			{
				$conditions['user_id'] = array_keys($users);
			}

			$conditions['names'] = $names;
			return $this->_getDefaultEmoticonsResponse($conditions);
		}

		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$perPage = 20;

		return $this->_getDefaultEmoticonsResponse(array(), $page, $perPage);
	}

	protected function _getDefaultEmoticonsResponse(array $conditions = array(), $page = 1, $perPage = 20)
	{
		$userEmoticonModel = $this->_getEmoticonModel();

		$fetchOptions = array(
			'page' => $page,
			'perPage' => $perPage,
			'join' => KomuKu_Emoticons_Model_Emoticon::FETCH_USER
		);

		$emoticons = $userEmoticonModel->getEmoticons($conditions, $fetchOptions);
		$emoticons = $userEmoticonModel->prepareEmoticons($emoticons);

		$totalEmoticons = $userEmoticonModel->countEmoticons($conditions);

		return $this->responseView('KomuKu_Emoticons_ViewAdmin_Emoticon_List', 'emoticon_list', array(
			'emoticons' => $emoticons,
			'totalEmoticons' => $totalEmoticons,
			'page' => $page,
			'perPage' => $perPage,
			'names' => isset($conditions['names']) ? implode(',', $conditions['names']) : ''
		));
	}

	public function actionEmoticonsPreview()
	{
		return $this->responseView('KomuKu_Emoticons_ViewAdmin_Emoticon_Preview', 'emoticon_preview', array(
			'emoticon' => $this->_getEmoticonOrError()
		));
	}

	public function actionEmoticonsDelete()
	{
		$emoticon = $this->_getEmoticonOrError();

		if($this->isConfirmedPost())
		{
			$dw = XenForo_DataWriter::create('KomuKu_Emoticons_DataWriter_Emoticon');
			$dw->setExistingData($emoticon['emoticon_id']);
			$dw->delete();

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('logs/emoticons')
			);
		}
		else
		{
			return $this->responseView('KomuKu_Emoticons_ViewAdmin_Emoticon_Delete', 'emoticon_delete', array(
				'emoticon' => $emoticon
			));
		}
	}

	protected function _getEmoticonOrError()
	{
		$id = $this->_input->filterSingle('id', XenForo_Input::UINT);
		$record = $this->_getEmoticonModel()->getEmoticonById($id, array(
			'join' => KomuKu_Emoticons_Model_Emoticon::FETCH_USER
		));

		if(!$record)
		{
			throw $this->responseException(
				$this->responseError(new XenForo_Phrase('emoticon_requested_record_not_found'), 404)
			);
		}

		return $this->_getEmoticonModel()->prepareEmoticon($record);
	}

	protected function _getEmoticonModel()
	{
		return $this->getModelFromCache('KomuKu_Emoticons_Model_Emoticon');
	}
}