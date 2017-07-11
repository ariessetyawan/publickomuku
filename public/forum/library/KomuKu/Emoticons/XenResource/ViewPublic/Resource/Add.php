<?php

class KomuKu_Emoticons_XenResource_ViewPublic_Resource_Add extends XFCP_KomuKu_Emoticons_XenResource_ViewPublic_Resource_Add
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		if(isset($this->_params['resource']['description']))
		{
			$this->attach('resource.description', $this->_params['resource']['user_id']);
		}

		return parent::renderHtml();
	}
}
