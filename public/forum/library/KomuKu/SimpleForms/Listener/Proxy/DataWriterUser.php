<?php

class KomuKu_SimpleForms_Listener_Proxy_DataWriterUser extends XFCP_KomuKu_SimpleForms_Listener_Proxy_DataWriterUser
{
	protected function _preDelete()
	{
		$responseModel = new KomuKu_SimpleForms_Model_Response();
		
		$action = XenForo_Application::getOptions()->deleteUserLogic;
		switch ($action)
		{
			case 'delete':
			{
				$responseModel->deleteUserResponses($this->get('user_id'));
				break;
			}
			
			case 'reassign':
			{
				$responseModel->reassignUserResponses($this->get('user_id'));
				break;
			}
		}
		
		parent::_preDelete();
	}
}