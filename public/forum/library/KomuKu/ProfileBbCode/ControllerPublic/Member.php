<?php

class KomuKu_ProfileBbCode_ControllerPublic_Member extends XFCP_KomuKu_ProfileBbCode_ControllerPublic_Member
{
	public function actionPost()
	{
		if (XenForo_Application::get('options')->mr_pbbc_profileOtherSettings['wysiwyg'])
		{
			$_POST['message'] = $this->getHelper('Editor')->getMessageText('message', $this->_input);
		}
		return parent::actionPost();
	}

}