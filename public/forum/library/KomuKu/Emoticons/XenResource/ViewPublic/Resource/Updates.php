<?php

class KomuKu_Emoticons_XenResource_ViewPublic_Resource_Updates extends XFCP_KomuKu_Emoticons_XenResource_ViewPublic_Resource_Updates
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		foreach($this->_params['updates'] as &$update)
		{
			$update['message'] = $this->attachArray($update);
		}

		return parent::renderHtml();
	}
}
