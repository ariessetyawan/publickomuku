<?php
 
 //######################## Extra Thread View Settings By KomuKu ###########################
class KomuKu_ThreadExtras_Model_Model extends XenForo_Model 
{
    //Set up the daily post requirement to enter this thread
	public function dailyPosts(array $thread, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		
		$db = $this->_getDb();
		
		if(isset($thread['daily_posts']) AND $thread['daily_posts'] != 0)
	    {
	   	    if (!$viewingUser['user_id'])
	   	    {
	   	  	    $errorPhraseKey = 'must_be_registered';
	   	  	    return false;
	   	    }
	   	  
		    $daycut = time() - 86400;
		   
		    $query = $db->fetchRow('
		        SELECT COUNT(*) AS total_posts 
		        FROM  kmk_post 
		        WHERE post_date > ?
		        AND user_id = ?
		    ', array($daycut, $viewingUser['user_id']));
		   
		    if($query['total_posts'] <= $thread['daily_posts'])
		    {
				$errorPhraseKey = array('daily_posts_enter_thread', 'username' => $viewingUser['username'], 'dailyposts' => $thread['daily_posts']);
	            return false;
			        
		    }
		}
		
		return true;
		
	}
	
	//Set up the thread count requirement to enter this thread
	public function threadCount(array $thread, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
	{
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
		
		$db = $this->_getDb();
		
		if(isset($thread['thread_count']) AND $thread['thread_count'] != 0)
	    {
	   	    if (!$viewingUser['user_id'])
	   	    {
	   	  	    $errorPhraseKey = 'must_be_registered';
	   	  	    return false;
	   	    }
	   	  
		    $query = $db->fetchRow('
		           SELECT COUNT(*) AS total_threads 
		           FROM  kmk_thread 
		           WHERE user_id= ?
		    ', $viewingUser['user_id']); 
		   
		    if($query['total_threads'] <= $thread['thread_count'])
		    {
				$errorPhraseKey = array('threads_enter_thread', 'username' => $viewingUser['username'], 'threadcount' => $thread['thread_count']);
	            return false;
			        
		    }
		}
		
		return true;
	}
}