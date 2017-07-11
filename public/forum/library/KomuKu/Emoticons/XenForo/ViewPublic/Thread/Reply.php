<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Thread_Reply extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Thread_Reply
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('defaultMessage');
		return parent::renderHtml();
	}
}
