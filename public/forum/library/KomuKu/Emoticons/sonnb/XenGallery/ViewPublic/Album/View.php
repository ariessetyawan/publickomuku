<?php

class KomuKu_Emoticons_sonnb_XenGallery_ViewPublic_Album_View extends XFCP_KomuKu_Emoticons_sonnb_XenGallery_ViewPublic_Album_View
{
	use KomuKu_Emoticons_Traits_Message;
	
	public function renderHtml()
	{
		$this->attach('album.description', $this->_params['album']['user_id']);

		if(!empty($this->_params['album']['comments']))
		{
			foreach($this->_params['album']['comments'] as &$comment)
			{
				$comment['message'] = $this->attachArray($comment);
			}
		}

		return parent::renderHtml();
	}
}