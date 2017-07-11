<?php

class KomuKu_Emoticons_XenResource_ViewPublic_Resource_Preview extends XFCP_KomuKu_Emoticons_XenResource_ViewPublic_Resource_Preview
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('message', $this->_params['resource']['user_id']);
		return parent::renderHtml();
	}
}
