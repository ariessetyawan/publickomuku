<?php

class KomuKu_Emoticons_XenResource_ViewPublic_Version_Add extends XFCP_KomuKu_Emoticons_XenResource_ViewPublic_Version_Add
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		if(!empty($this->_params['message']))
		{
			$this->attach('message', $this->_params['resource']['user_id']);
		}

		return parent::renderHtml();
	}
}
