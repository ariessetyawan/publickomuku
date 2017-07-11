<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Thread_ViewPosts extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Thread_ViewPosts
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

	public function renderJson()
	{
		foreach($this->_params['posts'] as &$post)
		{
			$post['message'] = $this->attachArray($post);
		}

		return parent::renderJson();
	}
}

