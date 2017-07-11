<?php

class KomuKu_Emoticons_Siropu_Chat_ViewPublic_Public extends XFCP_KomuKu_Emoticons_Siropu_Chat_ViewPublic_Public
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		if(empty($this->_params['chatMessages'])) 
		{
			return parent::renderHtml();
		}

		foreach($this->_params['chatMessages'] as &$message) 
		{
			$message['message_text'] = $this->attachArray($message, 'user_id', 'message_text');
		}

		return parent::renderHtml();
	}

	public function renderJson()
	{
		if(empty($this->_params['chatMessages'])) 
		{
			return parent::renderJson();
		}

		foreach($this->_params['chatMessages'] as &$message) 
		{
			$message['message_text'] = $this->attachArray($message, 'user_id', 'message_text');
		}

		$lastMessage = Siropu_Chat_Helper::prepareLastRow($this->_params['chatMessages'], $this->_params['data']);

		$response = parent::renderJson();
		$response = json_decode($response, true);

		// Fixed display incorrect message in last-row
		$attached = KomuKu_Emoticons_String::attach($lastMessage['user_id'], '');
		$response['lastRow'] = str_replace($attached, '', $response['lastRow']);

		return Xenforo_ViewRenderer_Json::jsonEncodeForOutput($response);
	}
}