<?php

class KomuKu_SimpleForms_Listener_Proxy_AdminControllerForum extends XFCP_KomuKu_SimpleForms_Listener_Proxy_AdminControllerForum
{
	public function actionEdit()
	{
		return $this->_injectFormOptions(parent::actionEdit());
	}
	
	public function actionAdd()
	{
		return $this->_injectFormOptions(parent::actionAdd());
	}
	
	public function _injectFormOptions(XenForo_ControllerResponse_Abstract $response)
	{
		if (get_class($response) == 'XenForo_ControllerResponse_Reroute')
		{
			return $response;
		}
		
		$formModel = $this->_getFormModel();
		
		$value = null;
		if (array_key_exists('kmkform__form_id', $response->params['forum']))
		{
			$value = $response->params['forum']['kmkform__form_id'];
		}
		
		$response->params['formOptions'] = $formModel->getFormOptionsArray($formModel->getForms(), $value);
		
		return $response;
	}
	
	protected function _validateField($dataWriterName, array $data = array(), array $options = array(), array $extraData = array())
	{
		$data = array_merge($this->_getFieldValidationInputParams(), $data);
		
		if (substr($data['name'], 0, 5) == 'kmkform__')
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				'',
				new XenForo_Phrase('redirect_field_validated', array('name' => $data['name'], 'value' => $data['value']))
			);
		}
		
		return parent::_validateField($dataWriterName, $data, $options, $extraData);
	}
	
	public function actionSave()
	{
		$response = parent::actionSave();
		
		$forumFormId = $this->_input->filterSingle('kmkform__forum_form_id', XenForo_Input::UINT);
		$formId = $this->_input->filterSingle('kmkform__form_id', XenForo_Input::UINT);
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		
		$writer = XenForo_DataWriter::create('KomuKu_SimpleForms_DataWriter_ForumForm');
		
		if ($forumFormId) 
		{
			$writer->setExistingData($forumFormId);
		}
		
		if ($forumFormId && !$formId)
		{
			$writer->delete();
		}
		
		if ($formId && $forumId)
		{
			$writer->bulkSet(array(
				'form_id' => $formId,
				'forum_id' => $forumId,
				'replace_button' => $this->_input->filterSingle('kmkform__replace_button', XenForo_Input::UINT),
				'button_text' => $this->_input->filterSingle('kmkform__button_text', XenForo_Input::STRING)
			));

			$writer->save();
		}
		
		return $response;
	}
	
	/**
	 * Searches for a forum by the left-most prefix of a title (for auto-complete(.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionSearchTitle()
	{
		$q = $this->_input->filterSingle('q', XenForo_Input::STRING);

		if ($q !== '')
		{
			$forums = $this->_getForumModel()->getForums(
				array('title' => array($q , 'r')),
				array('limit' => 10)
			);
		}
		else
		{
			$forums = array();
		}

		$viewParams = array(
			'forums' => $forums
		);

		return $this->responseView(
			'KomuKu_SimpleForms_ViewAdmin_Forum_SearchTitle',
			'',
			$viewParams
		);
	}	
	
	/**
	 * @return KomuKu_SimpleForms_Model_Form
	 */
	protected function _getFormModel()
	{
		return XenForo_Model::create('KomuKu_SimpleForms_Model_Form');
	}
}