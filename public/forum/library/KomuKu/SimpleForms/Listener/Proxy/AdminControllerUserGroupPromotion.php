<?php

class KomuKu_SimpleForms_Listener_Proxy_AdminControllerUserGroupPromotion extends XFCP_KomuKu_SimpleForms_Listener_Proxy_AdminControllerUserGroupPromotion
{
	public function actionAdd()
	{
		return $this->_injectForm(parent::actionAdd());
	}
	
	public function actionEdit()
	{
		return $this->_injectForm(parent::actionEdit());
	}
	
	/**
	 * Inject the kmkform__form parameter into the response
	 * 
	 * @param XenForo_ControllerResponse_View $response
	 * @return XenForo_ControllerResponse_View
	 */
	protected function _injectForm(XenForo_ControllerResponse_View $response)
	{
		$response->params['kmkform__forms'] = $this->_getFormModel()->getForms();
		
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