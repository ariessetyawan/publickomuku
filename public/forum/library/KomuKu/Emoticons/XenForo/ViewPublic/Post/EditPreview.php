<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Post_EditPreview extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Post_EditPreview
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('post.message', $this->_params['post']['user_id']);
		return parent::renderHtml();
	}
}
