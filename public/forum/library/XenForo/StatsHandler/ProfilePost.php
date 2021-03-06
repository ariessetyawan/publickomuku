<?php

class XenForo_StatsHandler_ProfilePost extends XenForo_StatsHandler_Abstract
{
	public function getStatsTypes()
	{
		return array(
			'profile_post' => new XenForo_Phrase('profile_posts'),
			'profile_post_like' => new XenForo_Phrase('profile_post_likes')
		);
	}

	public function getData($startDate, $endDate)
	{
		$db = $this->_getDb();

		$profilePosts = $db->fetchPairs(
			$this->_getBasicDataQuery('kmk_profile_post', 'post_date', 'message_state = ?'),
			array($startDate, $endDate, 'visible')
		);

		$profilePostLikes = $db->fetchPairs(
			$this->_getBasicDataQuery('kmk_liked_content', 'like_date', 'content_type = ?'),
			array($startDate, $endDate, 'profile_post')
		);

		return array(
			'profile_post' => $profilePosts,
			'profile_post_like' => $profilePostLikes
		);
	}
}