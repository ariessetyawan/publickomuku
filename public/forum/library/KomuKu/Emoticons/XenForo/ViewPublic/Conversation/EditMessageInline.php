<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Conversation_EditMessageInline extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Conversation_EditMessageInline
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('conversationMessage.message', $this->_params['conversationMessage']['user_id']);

		return parent::renderHtml();
	}
}