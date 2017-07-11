<?php

class KomuKu_Emoticons_Dark_TaigaChat_ViewPublic_TaigaChat_List extends XFCP_KomuKu_Emoticons_Dark_TaigaChat_ViewPublic_TaigaChat_List
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		foreach($this->_params['taigachat']['messages'] as &$message)
		{
			$message['message'] = $this->attachArray($message);
		}

		return parent::renderHtml();
	}

	public function renderJson()
	{
		foreach($this->_params['taigachat']['messages'] as &$message)
		{
			$message['message'] = $this->attachArray($message);
		}

		return parent::renderJson();
	}
}