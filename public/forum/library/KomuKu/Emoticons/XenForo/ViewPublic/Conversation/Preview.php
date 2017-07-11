<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Conversation_Preview extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Conversation_Preview
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('message');
		return parent::renderHtml();
	}
}