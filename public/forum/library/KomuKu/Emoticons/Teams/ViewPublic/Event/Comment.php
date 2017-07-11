<?php

class KomuKu_Emoticons_Teams_ViewPublic_Event_Comment extends XFCP_KomuKu_Emoticons_Teams_ViewPublic_Event_Comment
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderJson()
	{
		$this->attach('comment.message', $this->_params['comment']['user_id']);
		return parent::renderJson();
	}
}