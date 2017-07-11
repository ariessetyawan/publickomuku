<?php

class KomuKu_Bookmark_ControllerPublic_Thread extends XFCP_KomuKu_Bookmark_ControllerPublic_Thread
{	
	public function actionIndex()
	{
		//########################################
		// This will add a bookmark variable to
		// $parent->params. Purpose is to be 
		// able to show Bookmark or Unbookmark
		// link below each post.
		//########################################
							
		// get parent	
		$parent = parent::actionIndex();
		
		// return parent action if this is a redirect or other non View response	 
		if (!$parent instanceof XenForo_ControllerResponse_View)
		{
			return $parent;
		}		

		//########################################
		// get $posts
		//########################################		
		
		$posts = $parent->params['posts'];
		
		//########################################
		// get $bookmarkResults
		//########################################	
		
		// get userId
		$userId = XenForo_Visitor::getUserId();				
		
		// get database
		$db = XenForo_Application::get('db');

		// run query
		$bookmarkResults = $db->fetchCol("
		SELECT post_id
		FROM kmk_bookmark
		WHERE kmk_bookmark.user_id = ?
		", $userId);			
		
		//########################################
		// add bookmark to $parent->params
		//########################################

		foreach ($posts as $k => $v)
		{
			if (in_array($k, $bookmarkResults))
			{
				$parent->params['posts'][$k] += array(
					'bookmark' => true
				);
			}
			else
			{
				$parent->params['posts'][$k] += array(
					'bookmark' => false
				);
			}
		}

		// return parent
		return $parent;	
	}
}