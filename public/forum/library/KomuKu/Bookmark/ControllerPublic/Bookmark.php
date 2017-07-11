<?php

class KomuKu_Bookmark_ControllerPublic_Bookmark extends XenForo_ControllerPublic_Abstract
{	
	public function actionList()
	{
		//########################################
		// list
		//########################################			
		
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		}
	
		// get userId
		$userId = XenForo_Visitor::getUserId();	
		
		// get options from Admin CP -> Options -> Bookmark -> Sort By
		$sortBy = XenForo_Application::get('options')->bookmarkSortBy;	
		
		// get options from Admin CP -> Options -> Bookmark -> Sort Order
		$sortOrder = XenForo_Application::get('options')->bookmarkSortOrder;		
		
		if ($sortBy == 'postDate')
		{
			if ($sortOrder == 'desc')
			{
				$orderBy = 'kmk_post.post_date, DESC';
			}
			
			if ($sortOrder == 'asc')
			{
				$orderBy = 'kmk_post.post_date, ASC';
			}
		}
		
		if ($sortBy == 'bookmarkDate')
		{
			if ($sortOrder == 'desc')
			{			
				$orderBy = 'kmk_bookmark.bookmark_id, DESC';
			}
			
			if ($sortOrder == 'asc')
			{			
				$orderBy = 'kmk_bookmark.bookmark_id, ASC';
			}			
		}					
		
		// get database
		$db = XenForo_Application::get('db');			
		
		// run query
		$bookmarkResults = $db->fetchAll("
		SELECT kmk_bookmark.bookmark_id,
		kmk_post.post_id,
		kmk_post.user_id,
		kmk_post.username,
		kmk_node.node_id AS forum_id,
		kmk_node.title AS forum_title,
		kmk_thread.title,		
		kmk_post.post_date,
		kmk_bookmark.note
		FROM kmk_bookmark
		INNER JOIN kmk_post ON kmk_post.post_id = kmk_bookmark.post_id
		INNER JOIN kmk_thread ON kmk_thread.thread_id = kmk_post.thread_id
		INNER JOIN kmk_node ON kmk_node.node_id = kmk_thread.node_id
		WHERE kmk_bookmark.user_id = ?
		AND kmk_post.message_state = 'visible'
		ORDER BY ?
		", array($userId, $orderBy));	
		
		// declare variable
		$i = 0;
		
		// get child node titles
		foreach ($bookmarkResults as $k => $v)
		{						
			// includeTitleInUrls option
			$forum_link = XenForo_Link::buildIntegerAndTitleUrlComponent($v['forum_id'], $v['forum_title'], true);
			
			// merge arrays
			$bookmarkResults[$i] = array_merge($bookmarkResults[$i], array('forum_link' => $forum_link));
			
			// increment variable
			$i = $i + 1;			
		}			
		
		// prepare viewParams
		$viewParams = array(
			'bookmarkResults' => $bookmarkResults
		); 
		
		// send to template
		return $this->responseView('KomuKu_Bookmark_ViewPublic_Bookmark', 'KomuKu_bookmark_list', $viewParams);
	}
	
	public function actionEdit()
	{
		//########################################
		// edit
		//########################################
		
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		}
		
		// get name from route
		$bookmarkId = $this->_input->filterSingle('bookmark_id', XenForo_Input::STRING);
		
		// get userId
		$userId = XenForo_Visitor::getUserId();		

		// get database
		$db = XenForo_Application::get('db');
		
		// run query
		$result = $db->fetchOne("
		SELECT bookmark_id
		FROM kmk_bookmark
		WHERE bookmark_id = ?
		AND user_id = ?
		", array($bookmarkId,$userId));
		
		// throw error if no result
		if (!$result)
		{
			throw $this->getNoPermissionResponseException();
		}				
		
		// run query
		$results = $db->fetchRow("
			SELECT bookmark_id, note
			FROM kmk_bookmark		
			WHERE bookmark_id = ?
			AND user_id = ?
		", array($bookmarkId,$userId));							
		
		// prepare viewParams
		$viewParams = array(
			'bookmark_id' => $results['bookmark_id'],
			'note' => $results['note']
		);					

		// send to template
		return $this->responseView('KomuKu_Bookmark_ViewPublic_Bookmark', 'KomuKu_bookmark_edit', $viewParams);					
	}
	
	public function actionDelete()
	{
		//########################################
		// delete
		//########################################	
			
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		}		

		// get name from route, example /forums/bookmark/delete?bookmark_id=1234  
		$bookmarkId = $this->_input->filterSingle('bookmark_id', XenForo_Input::UINT);
		
		// get userId
		$userId = XenForo_Visitor::getUserId();
		
		// get database
		$db = XenForo_Application::get('db');
		
		// run query
		$result = $db->fetchOne("
		SELECT bookmark_id
		FROM kmk_bookmark
		WHERE bookmark_id = ?
		AND user_id = ?
		", array($bookmarkId,$userId));
		
		// throw error if no result
		if (!$result)
		{
			throw $this->getNoPermissionResponseException();
		}		
		
		// delete row
		$db->query("
			DELETE FROM kmk_bookmark
			WHERE bookmark_id = ?
			AND user_id = ?
		", array($bookmarkId,$userId));			

		// return to bookmark list
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('bookmark/list')
		);
	}
	
	public function actionDeleteSelected()
	{
		//########################################
		// delete selected
		//########################################	
			
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		}
		
		// make sure data comes from $_POST
		$this->_assertPostOnly();			

		// get bookmark_ids
		$bookmarkIds = $this->_input->filterSingle('bookmark_ids', XenForo_Input::ARRAY_SIMPLE);
		
		if (!empty($bookmarkIds))
		{
			// create whereclause
			$whereclause = 'WHERE (kmk_bookmark.bookmark_id = ' . implode(' OR kmk_bookmark.bookmark_id = ', $bookmarkIds);
			$whereclause = $whereclause . ')';
		
			// get database
			$db = XenForo_Application::get('db');		
			
			// delete row(s)
			$db->query("
				DELETE FROM kmk_bookmark
				$whereclause
				");	
	
			// return to bookmark list
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('bookmark/list')
			);
		}
		
		if (empty($bookmarkIds))
		{
			return $this->responseError(new XenForo_Phrase('bookmark_please_select_bookmarks'));
		}
		
	}	
	
	public function actionUpdate()
	{
		//########################################
		// update
		//########################################	
				
		// get permission
		if (!XenForo_Visitor::getInstance()->hasPermission('bookmarkGroupID', 'bookmarkID'))
		{
			throw $this->getNoPermissionResponseException();
		}
				
		// get bookmarkId
		$bookmarkId = $this->_input->filterSingle('bookmark_id', XenForo_Input::UINT);
		
		// get userId
		$userId = XenForo_Visitor::getUserId();		
		
		// get note
		$note = $this->_input->filterSingle('note', XenForo_Input::STRING);
		
		// get database
		$db = XenForo_Application::get('db');
		
		// run query
		$result = $db->fetchOne("
		SELECT bookmark_id
		FROM kmk_bookmark
		WHERE bookmark_id = ?
		AND user_id = ?
		", array($bookmarkId,$userId));
		
		// throw error if no result
		if (!$result)
		{
			throw $this->getNoPermissionResponseException();
		}

		$db->query("
		UPDATE kmk_bookmark SET
			note = ?
			WHERE bookmark_id = ?
			AND user_id = ?
		", array($note,$bookmarkId,$userId));	

		// return to bookmark list
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('bookmark/list')
		);		
	}	
}