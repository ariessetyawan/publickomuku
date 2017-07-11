<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Thread_View extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Thread_View
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		foreach($this->_params['posts'] as &$post)
		{
			$post['message'] = $this->attachArray($post);
		}

		if (!empty($this->_params['canQuickReply']) && isset($this->_params['thread']['draft_message']))
		{
			$this->attach('thread.draft_message');
		}

		return parent::renderHtml();
	}
}
