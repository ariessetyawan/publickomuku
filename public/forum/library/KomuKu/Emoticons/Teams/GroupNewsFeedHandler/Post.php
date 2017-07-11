<?php

class KomuKu_Emoticons_Teams_GroupNewsFeedHandler_Post extends XFCP_KomuKu_Emoticons_Teams_GroupNewsFeedHandler_Post
{
	use KomuKu_Emoticons_Traits_Message;

	public function renderContent(array $item, $content, array $extraParams, XenForo_View $view)
	{
		$content['message'] = $this->attachArray($content);

		return parent::renderContent($item, $content, $extraParams, $view);
	}
}