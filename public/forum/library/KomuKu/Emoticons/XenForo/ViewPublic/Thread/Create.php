<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Thread_Create extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Thread_Create
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		if(!empty($this->_params['draft']))
		{
			$this->attach('draft.message');
		}

		return parent::renderHtml();
	}
}
