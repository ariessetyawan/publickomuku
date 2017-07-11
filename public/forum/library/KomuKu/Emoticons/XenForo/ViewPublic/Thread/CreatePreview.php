<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Thread_CreatePreview extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Thread_CreatePreview
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('message');
		return parent::renderHtml();
	}
}
