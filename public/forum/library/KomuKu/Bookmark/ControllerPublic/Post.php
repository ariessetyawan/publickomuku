<?php

class KomuKu_Bookmark_ControllerPublic_Post extends XFCP_KomuKu_Bookmark_ControllerPublic_Post
{	
	public function actionBookmark()
	{
		//########################################
		// bookmark link below post
		//########################################
			
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		} 
		
		// get post_id from route, example /forums/posts/12345/bookmark-add
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		
		// get post variables
		$post = $this->getModelFromCache('XenForo_Model_Post')->getPostById($postId);
		
		// prepare viewParams
		$viewParams = array(
			'post' => $post
		);		
		
		// send to template
		return $this->responseView('KomuKu_FlagPost_ViewPublic_Post','KomuKu_bookmark',$viewParams);		
	}
	
	public function actionBookmarkSave()
	{
		//########################################
		// save bookmark action from overlay
		//########################################
				
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		}  
			
		// make sure data comes from $_POST
		$this->_assertPostOnly();		
		
		// get postId from route, example /forums/posts/12345/bookmark-save  
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		
		// get post variable
		$post = $this->getModelFromCache('XenForo_Model_Post')->getPostById($postId);		
		
		// get userId
		$userId = XenForo_Visitor::getUserId();	
		
		// get post_id from route, example /forums/posts/12345/bookmark-save  
		$note = $this->_input->filterSingle('note', XenForo_Input::STRING);					
		
		// bookmark post if postId is numeric
		if (is_numeric($postId))
		{
			// get database
			$db = XenForo_Application::get('db');		
			
			// insert or update row
			$db->query("
				INSERT INTO kmk_bookmark
					(post_id, user_id, note)
				VALUES 
					(?,?,?)
				ON DUPLICATE KEY UPDATE
					note = VALUES(note)
			", array($postId, $userId, $note));
			
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('posts', $post),
				new XenForo_Phrase('bookmark_post_successfully_bookmarked')
			);			
		}
	}
	
	public function actionBookmarkUnbookmark()
	{
		//########################################
		// unbookmark link below post
		//########################################
			
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		} 
		
		// get post_id from route, example /forums/posts/12345/bookmark-add
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		
		// get post variables
		$post = $this->getModelFromCache('XenForo_Model_Post')->getPostById($postId);
		
		// prepare viewParams
		$viewParams = array(
			'post' => $post
		);		
		
		// send to template
		return $this->responseView('KomuKu_FlagPost_ViewPublic_Post','KomuKu_bookmark_unbookmark',$viewParams);		
	}		
	
	public function actionBookmarkUnbookmarkSave()
	{
		//########################################
		// save unbookmark action from overlay
		//########################################	
			
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		}

		// get postId from route, example /forums/posts/12345/bookmark/unbookmark  
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		
		// get post variable
		$post = $this->getModelFromCache('XenForo_Model_Post')->getPostById($postId);		
		
		// get userId
		$userId = XenForo_Visitor::getUserId();	

		// get database
		$db = XenForo_Application::get('db');
		
		// delete row
		$db->query("
			DELETE FROM kmk_bookmark
			WHERE post_id = ?
			AND user_id = ?
		", array($postId,$userId));			

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('posts', $post),
			new XenForo_Phrase('bookmark_post_successfully_unbookmarked')
		);
	}
}