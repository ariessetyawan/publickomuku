<?php

class KomuKu_Emoticons_XenResource_ViewPublic_Update_EditInline extends XFCP_KomuKu_Emoticons_XenResource_ViewPublic_Update_EditInline
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderHtml()
	{
		$this->attach('update.message', $this->_params['update']['user_id']);
		return parent::renderHtml();
	}
}
