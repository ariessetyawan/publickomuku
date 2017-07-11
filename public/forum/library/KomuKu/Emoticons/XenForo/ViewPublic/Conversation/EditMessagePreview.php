<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Conversation_EditMessagePreview extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Conversation_EditMessagePreview
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('message', $this->_params['conversationMessage']['user_id']);
		return parent::renderHtml();
	}
}