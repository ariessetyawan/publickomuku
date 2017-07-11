<?php

class Brivium_ForumCensorship_Model_Thread extends XFCP_Brivium_ForumCensorship_Model_Thread
{
	public function prepareThread(array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		$oldThreadTitle = $thread['title'];
		$thread = parent::prepareThread($thread, $forum, $nodePermissions, $viewingUser);
		$excludeForums = XenForo_Application::get('options')->ForumCensorship_excluded;
		if (!empty($thread['node_id']) && in_array($thread['node_id'], $excludeForums))
		{
			$thread['title'] = $oldThreadTitle;
			$thread['titleCensored'] = false;
		}
		return $thread;
	}	
}