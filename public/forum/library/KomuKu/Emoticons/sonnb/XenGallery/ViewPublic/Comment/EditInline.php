<?php

class KomuKu_Emoticons_sonnb_XenGallery_ViewPublic_Comment_EditInline extends XFCP_KomuKu_Emoticons_sonnb_XenGallery_ViewPublic_Comment_EditInline
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderJson()
	{
		$this->attach('comment.message', $this->_params['comment']['user_id']);
		return parent::renderJson();
	}
}