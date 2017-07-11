<?php

class KomuKu_Emoticons_Teams_ViewPublic_Event_Add extends XFCP_KomuKu_Emoticons_Teams_ViewPublic_Event_Add
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		if(isset($this->_params['event']['event_description']))
		{
			$this->attach('event.event_description', $this->_params['event']['user_id']);	
		}
		
		return parent::renderHtml();
	}
}