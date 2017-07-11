<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Conversation_View extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Conversation_View
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		foreach($this->_params['messages'] as &$message)
		{
			$message['message'] = $this->attachArray($message);
		}

		if(!empty($this->_params['conversation']['draft_message']))
		{
			$this->attach('conversation.draft_message');
		}

		return parent::renderHtml();
	}
}