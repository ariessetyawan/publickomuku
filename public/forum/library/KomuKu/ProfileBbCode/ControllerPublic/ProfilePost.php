<?php

class KomuKu_ProfileBbCode_ControllerPublic_ProfilePost extends XFCP_KomuKu_ProfileBbCode_ControllerPublic_ProfilePost
{
	public function actionSave()
	{
		if (XenForo_Application::get('options')->mr_pbbc_profileOtherSettings['wysiwyg'])
		{
			$_POST['message'] = $this->getHelper('Editor')->getMessageText('message', $this->_input);
		}
		return parent::actionSave();
	}

}