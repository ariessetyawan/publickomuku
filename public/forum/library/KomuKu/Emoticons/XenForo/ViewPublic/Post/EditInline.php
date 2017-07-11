<?php

class KomuKu_Emoticons_XenForo_ViewPublic_Post_EditInline extends XFCP_KomuKu_Emoticons_XenForo_ViewPublic_Post_EditInline
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('post.message', $this->_params['post']['user_id']);
		return parent::renderHtml();
	}
}
