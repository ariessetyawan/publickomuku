<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Conversation_ViewNewMessages extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Conversation_ViewNewMessages
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$addOns = XenForo_Application::get('addOns');
		if(isset($addOns['KomuKu_Messenger']))
		{
			// Fixed the bug conflict with add-on: [KomuKu] Messenger (Realtime Chatting)
			return parent::renderHtml();
		}

		foreach($this->_params['messages'] as &$message)
		{
			$message['message'] = $this->attachArray($message);
		}

		return parent::renderHtml();
	}

}