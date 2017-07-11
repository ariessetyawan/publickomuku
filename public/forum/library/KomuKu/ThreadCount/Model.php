<?php

/*
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_Model extends XenForo_Model
{
	public function recount()
	{
		$this->_getDb()->query("UPDATE kmk_user AS user
                                SET thread_count = (
                                	SELECT COUNT(*)
                                	FROM kmk_thread AS thread
                                    LEFT JOIN kmk_forum AS forum ON (forum.node_id = thread.node_id)
                                	WHERE thread.user_id = user.user_id
                                	AND thread.discussion_state = 'visible'
                                    AND forum.count_messages = 1
                                 )");
	}
	
	protected function _getDb()
	{
	    if ($this->_db === null)
	    {
	        $this->_db = XenForo_Application::getDb();
	    }
	
	    return $this->_db;
	}

}