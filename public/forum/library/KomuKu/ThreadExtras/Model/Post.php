<?php

//######################## Extra Thread View Settings By KomuKu ###########################
class KomuKu_ThreadExtras_Model_Post extends XFCP_KomuKu_ThreadExtras_Model_Post
{
	public function getPostInsertMessageState(array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
	    $parent = parent::getPostInsertMessageState($thread, $forum, $nodePermissions, $viewingUser);
		
	    $visitor = XenForo_Visitor::getInstance();
		
		//Moderate users posts for this thread
		if(isset($thread['user_moderation']) AND $thread['user_moderation'] != "")
	    {
	       if(in_array($visitor['username'], explode( ",", $thread['user_moderation'])))
	       {
	         return 'moderated'; 
	       }
	    }
		
		return $parent;
    }
	
}