<?php

class KomuKu_Emoticons_Teams_ViewPublic_Event_View extends XFCP_KomuKu_Emoticons_Teams_ViewPublic_Event_View
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('event.event_description', $this->_params['event']['user_id']);

		foreach($this->_params['comments'] as &$comment)
		{
			$comment['message'] = $this->attachArray($comment);
		}

		return parent::renderHtml();
	}
}