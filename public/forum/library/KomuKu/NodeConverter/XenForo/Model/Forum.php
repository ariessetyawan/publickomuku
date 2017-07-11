<?php

class KomuKu_NodeConverter_XenForo_Model_Forum extends XFCP_KomuKu_NodeConverter_XenForo_Model_Forum
{
	public function getForumById($id, array $fetchOptions = array())
	{
		$forum = parent::getForumById($id, $fetchOptions);

		if (!empty($forum) && empty($forum['discussion_count']))
		{
			$forum['canConvert'] = true;
		}

		return $forum;
	}
}