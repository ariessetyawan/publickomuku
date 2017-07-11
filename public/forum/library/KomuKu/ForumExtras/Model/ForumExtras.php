<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_Model_ForumExtras extends XenForo_Model
{
	//Get Extra Forum View Settings by id
	public function getForumextraById($forumextraId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM `kmk_forum_extra_view_settings`
			WHERE id = ?
		', $forumextraId);
	}

    //Get Extra Forum View Settings by title
	public function getForumextraByTitle($forumextraTitle)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM `kmk_forum_extra_view_settings`
			WHERE id = ?
		', $forumextraTitle);
	}

	//Get all Extra Forum View Settings
	public function getAllForumextras()
	{
		return $this->fetchAllKeyed('
			SELECT extra.*, 
			node.*
			FROM `kmk_forum_extra_view_settings` AS extra
			LEFT JOIN `kmk_node` AS node ON
			(extra.node_id = node.node_id)	
		', 'id');
	}

	//Get all Extra Forum View Settings for the cache data
	public function getAllForumextrasForCache()
	{
		return $this->getAllForumextras();
	}

	//Rebuild the Extra Forum View Settings cache
	public function rebuildForumextrasCache()
	{
		$forumextras = $this->getAllForumextrasForCache();
		$this->_getDataRegistryModel()->set('forumextras', $forumextras);

		return $forumextras;
	}
	
	//Delete the Extra Forum View Settings cache upon deletion of the Extra Forum View Settings
	public function deleteForumextrasCache()
	{
		$this->_getDataRegistryModel()->delete('forumextras');
	}
}