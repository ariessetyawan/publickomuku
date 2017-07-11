<?php

class KomuKu_Emoticons_sonnb_XenGallery_ViewPublic_Album_Comments extends XFCP_KomuKu_Emoticons_sonnb_XenGallery_ViewPublic_Album_Comments
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderJson()
	{
		foreach($this->_params['comments'] as &$comment)
		{
			$comment['message'] = $this->attachArray($comment);
		}

		return parent::renderJson();
	}
}