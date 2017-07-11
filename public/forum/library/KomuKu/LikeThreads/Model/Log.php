<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_Model_Log extends XFCP_KomuKu_LikeThreads_Model_Log
{
    //Get thread likes by id
    public function getLikeLogById($id)
	{
		return $this->_getDb()->fetchRow('
				SELECT *
				FROM kmk_liked_threads
				WHERE like_id = ?
			', $id
		);
	}
	
	//Search for likes given by user 
	public function getLikesLogsByUserId($userId)
	{
		return $this->fetchAllkeyed('
			SELECT DISTINCT l.*, u.*, th.title AS title
	           FROM kmk_liked_threads l
	           LEFT JOIN kmk_user u ON (l.user_id=u.user_id)
		       LEFT JOIN kmk_thread th ON (th.thread_id=l.thread_id)
			   WHERE l.user_id = ?
			   ORDER BY l.like_date DESC
			   ', 'like_id', $userId
		);
	}
	
    //Get all likes
	public function getLikesLog(array $fetchOptions = array())
	{
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults('
			   SELECT DISTINCT l.*, u.*, th.title AS title
	           FROM kmk_liked_threads l
	           LEFT JOIN kmk_user u ON (l.user_id=u.user_id)
		       LEFT JOIN kmk_thread th ON (th.thread_id=l.thread_id)
	           ORDER BY l.like_date DESC
			', $limitOptions['limit'], $limitOptions['offset']
		), 'like_id');
	}
	
	//Count all thread likes
	public function countLikesLog()
	{
		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM kmk_liked_threads
		');
	}
	
	//Delete thread like entry
	public function deleteLikeEntry($id)
	{
		$dw = XenForo_DataWriter::create('KomuKu_LikeThreads_DataWriter_LikeThreads');
		$dw->setExistingData($id);
		
		$dw->delete();
		return $dw;
	}
	
	//Clear all thread likes
	public function clearAllLikes()
	{
		$this->_getDb()->query('TRUNCATE TABLE kmk_liked_threads');
		
		//Reset like count to 0 for all threads
		$this->_getDb()->query('UPDATE kmk_thread SET like_count = 0');
	}
	
	//Recount. See below
	public function recountLikes()
	{		
		@set_time_limit(0);
		ignore_user_abort(true);
		XenForo_Application::getDb()->setProfiler(false); 
		$db = $this->_getDb();
			
		
		//Delete like(s) given in threads that do not exists anymore
		$db->query("DELETE l.*
 		            FROM kmk_liked_threads AS l
					LEFT JOIN kmk_thread AS thread 
					ON (l.thread_id = thread.thread_id)
					WHERE thread.thread_id IS NULL
		");	

	    //Delete like(s) given by users that do not exists anymore
	    $db->query("DELETE l.* 
		            FROM kmk_liked_threads AS l
					LEFT JOIN kmk_user AS user 
					ON (l.user_id = user.user_id)
					WHERE user.user_id IS NULL
		");			  
        
		XenForo_Db::commit($db);
	}
	
}