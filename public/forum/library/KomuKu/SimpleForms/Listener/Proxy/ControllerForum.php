<?php

class KomuKu_SimpleForms_Listener_Proxy_ControllerForum extends XFCP_KomuKu_SimpleForms_Listener_Proxy_ControllerForum
{
	public function actionForum()
	{
	    $response = parent::actionForum();
	    
	    if ($response instanceof XenForo_ControllerResponse_View)
	    { 
		  $response = $this->_injectForm($response);
	    }
	    
	    return $response;
	}

	public function actionCreateThread()
	{
	    $forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);

		$ftpHelper = $this->getHelper('ForumThreadPost');
		$forum = $ftpHelper->assertForumValidAndViewable($forumId ? $forumId : $forumName);

		// redirect to form
		if (array_key_exists('kmkform__form_id', $forum) && $forum['kmkform__form_id'])
		{
		    $this->getRequest()->setParam('form_id', $forum['kmkform__form_id']);
		    
		    return $this->responseReroute('KomuKu_SimpleForms_ControllerPublic_Form', 'respond');
		}
		
	    return parent::actionCreateThread();
	}	
	
	/**
	 * Inject the kmkform__form parameter into the response
	 *
	 * @param XenForo_ControllerResponse_View $response
	 * @return XenForo_ControllerResponse_View
	 */
	protected function _injectForm(XenForo_ControllerResponse_View $response)
	{
		if (array_key_exists('forum', $response->params) && array_key_exists('kmkform__form_id', $response->params['forum']))
		{
			$response->params['form'] = $this->_getFormModel()->getFormById($response->params['forum']['kmkform__form_id']); 
		}
	
		return $response;
	}
	
	/**
	 * @return KomuKu_SimpleForms_Model_Form
	 */
	protected function _getFormModel()
	{
		return XenForo_Model::create('KomuKu_SimpleForms_Model_Form');
	}
}