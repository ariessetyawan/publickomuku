<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_NewsFeedHandler_LikeThreads extends XenForo_NewsFeedHandler_Abstract
{
	/**
	 * @var KomuKu_LikeThreads_Model_LikeThreads
	 */
	protected $_likeModel = null;
	
	/**
	 * @var XenForo_Model_Thread
	 */
	protected $_threadModel = null;
	
	/**
	 * Fetches related content by IDs
	 *
	 * @param array $contentIds
	 * @param XenForo_Model_NewsFeed $model
	 * @param array $viewingUser Information about the viewing user (keys: user_id, permission_combination_id, permissions)
	 *
	 * @return array
	 */	
	public function getContentByIds(array $contentIds, $model, array $viewingUser)
	{
		$likeModel = $this->_getLikeModel();
		$threadModel = $this->_getThreadModel();
		
		$likes = $likeModel->getLikesByIds($contentIds, array(
				'join' => KomuKu_LikeThreads_Model_LikeThreads::FETCH_THREAD | KomuKu_LikeThreads_Model_LikeThreads::FETCH_FORUM,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		
		$likes = $likeModel->unserializePermissionsInList($likes, 'node_permission_cache');
		
		foreach ($likes AS &$like)
		{
			$like['hasPreview'] = $threadModel->hasPreview($like, $like, $like['permissions'], $viewingUser);
		}
		
		return $likes;
	}
	
	/**
	 * Determines if the given news feed item is viewable.
	 *
	 * @param array $item
	 * @param mixed $content
	 * @param array $viewingUser
	 *
	 * @return boolean
	 */	
	public function canViewNewsFeedItem(array $review, $content, array $viewingUser)
	{
		return $this->_likeModel->canViewNewsFeedLikes($content, $viewingUser);
	}
	
	/**
	 * Returns the primary key names for threads
	 *
	 * @return array thread_id, forum_id
	 */
	protected function _getContentPrimaryKeynames()
	{
		return array(
				'like_id', 'thread_id', 'title', 'prefix_id', 'hasPreview',
				'message','user_id', 'username'
		);
	}
	
	/**
	 * @var XenForo_Model_Thread
	 */
	protected function _getThreadModel()
	{
		if (!$this->_threadModel)
		{
			$this->_threadModel = XenForo_Model::create('XenForo_Model_Thread');
		}
	
		return $this->_threadModel;
	}
	
	/**
	 * @var KomuKu_LikeThreads_Model_LikeThreads
	 */
	protected function _getLikeModel()
	{
		if (!$this->_likeModel)
		{
			$this->_likeModel = XenForo_Model::create('KomuKu_LikeThreads_Model_LikeThreads');
		}
	
		return $this->_likeModel;
	}
	
}