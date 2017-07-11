<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Thread_ViewNewPosts extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Thread_ViewNewPosts
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		foreach($this->_params['posts'] as &$post)
		{
			$post['message'] = $this->attachArray($post);
		}

		return parent::renderHtml();
	}
}
