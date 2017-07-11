<?php

class KomuKu_Emoticons_Siropu_Chat_ViewPublic_Edit extends XFCP_KomuKu_Emoticons_Siropu_Chat_ViewPublic_Edit
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderJson()
	{
		$this->attach('message.message_text', $this->_params['message']['message_user_id']);

		return parent::renderJson();
	}
}