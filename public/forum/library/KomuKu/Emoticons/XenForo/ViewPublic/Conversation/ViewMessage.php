<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Conversation_ViewMessage extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Conversation_ViewMessage
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('message.message', $this->_params['message']['user_id']);
		return parent::renderHtml();
	}
}