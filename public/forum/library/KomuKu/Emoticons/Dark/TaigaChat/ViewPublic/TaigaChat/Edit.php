<?php

class KomuKu_Emoticons_Dark_TaigaChat_ViewPublic_TaigaChat_Edit extends XFCP_KomuKu_Emoticons_Dark_TaigaChat_ViewPublic_TaigaChat_Edit
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderJson()
	{
		foreach($this->_params['taigachat']['messages'] as &$message)
		{
			$message['message'] = $this->attachArray($message);
		}

		return parent::renderJson();
	}
}