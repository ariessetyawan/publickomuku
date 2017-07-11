<?php

class KomuKu_ForumModerators_ControllerPublic_Forum extends XFCP_KomuKu_ForumModerators_ControllerPublic_Forum
{
	public function actionForum()
	{
		//########################################
		// Shows moderator names with link to each
		// moderator.
		//########################################
		
		// get parent		
		$parent = parent::actionForum();
		
		// get options from Admin CP -> Options -> Forum Moderators -> Show Link 1
		$showLink1 = XenForo_Application::get('options')->forumModeratorsShowLink1;	
		
		// get options from Admin CP -> Options -> Forum Moderators -> Show Link 2
		$showLink2 = XenForo_Application::get('options')->forumModeratorsShowLink2;		
		
		// continue if true
		if ($showLink1 OR $showLink2)
		{
			// declare variables
			$mod = array();
			$superMod = array();
			$moderators	= array();
			$parentNodeId = '';
			$whereclause1 = '';
			$whereclause2 = '';	
			$nodeHierarchy = array();		
		
			// get forumId and forumName
			$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
			
			// get forumName (URL Portion)
			$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
	
			// get database
			$db = XenForo_Application::get('db');
	
			//########################################
			// get moderators
			//########################################
	
			// if using (URL Portion) get forumId
			if ($forumId == 0)
			{
				$forumId = $db->fetchOne("
				SELECT node_id
				FROM kmk_node
				WHERE node_name = ?
				", $forumName);	
			}
			
			// continue only if we have a forumId number
			if ($forumId > 0)
			{				
				//########################################
				// create whereclause1
				
				// get breadcrumb data
				$breadcrumbData = $db->fetchOne("
					SELECT breadcrumb_data
					FROM kmk_node
					WHERE node_id = ?
				", $forumId);				
	
				// unserialize blob data
				$results = unserialize($breadcrumbData);
				
				// make sure we have data
				if (!empty($results))
				{
					// get nodeHierarchy
					foreach ($results as $k => $v)
					{
						$nodeHierarchy[] = $v['node_id'];	
					}
				}
				
				if (!empty($nodeHierarchy))
				{
					// create whereclause1
					$whereclause1 = 'OR (kmk_moderator_content.content_id = ' . implode(' OR kmk_moderator_content.content_id = ', $nodeHierarchy);
					$whereclause1 = $whereclause1 . ')';					
				}
				
				//########################################
				// run moderators query
				
				$mod = $db->fetchAll("
				SELECT kmk_user.*
				FROM kmk_moderator_content
				INNER JOIN kmk_user ON kmk_user.user_id = kmk_moderator_content.user_id
				WHERE kmk_moderator_content.content_id = ?
				AND kmk_moderator_content.content_type = 'node'
				$whereclause1
				ORDER BY kmk_user.username ASC
				", $forumId);
			}
			
			//########################################
			// exclude moderators
			//########################################
			
			// get options from Admin CP -> Options -> Forum Moderators -> Exclude Moderators
			$excludeModerators = XenForo_Application::get('options')->forumModeratorsExcludeModerators;
			
			// create whereclause1
			if ($excludeModerators != '')
			{
				// remove trailing comma if there is one
				$excludeModerators = rtrim($excludeModerators, ',');
								
				// put into an array
				$excludeModeratorsArray = explode(',', $excludeModerators);
		
				foreach ($mod as $k => $v)
				{								
					// check for excluded moderators
					if (in_array($v['user_id'], $excludeModeratorsArray))
					{
						unset($mod[$k]);
					}
				}
			}
	
			//########################################
			// get super moderators
			//########################################
			
			// get options from Admin CP -> Options -> Forum Moderators -> Exclude Super Moderators
			$excludeSuperModerators = XenForo_Application::get('options')->forumModeratorsExcludeSuperModerators;
			
			// continue if need to exclude super moderators
			if ($excludeSuperModerators != '')
			{
				// remove trailing comma if there is one
				$excludeSuperModerators = rtrim($excludeSuperModerators, ',');
								
				// put into an array
				$excludeSuperModeratorsArray = explode(',', $excludeSuperModerators);
	
				// create whereclause2
				$whereclause2 = 'AND (kmk_moderator.user_id != ' . implode(' AND kmk_moderator.user_id != ', $excludeSuperModeratorsArray);
				$whereclause2 = $whereclause2 . ')';	
			}
			
			// get super moderators
			$superMod = $db->fetchAll("
			SELECT kmk_user.*
			FROM kmk_moderator
			INNER JOIN kmk_user ON kmk_user.user_id = kmk_moderator.user_id
			WHERE kmk_moderator.is_super_moderator = '1'
			$whereclause2
			ORDER BY username ASC
			");

			// merge arrays
			$moderators = array_merge($mod, $superMod);
			
			// sort multi-dimensional array by value
			function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
				$sort_col = array();
				foreach ($arr as $key=> $row) {
					$sort_col[$key] = $row[$col];
				}
			
				array_multisort($sort_col, $dir, $arr);
			}
			
			// sort by username
			array_sort_by_column($moderators, 'username');			
			
			// count moderators
			$modCount = count($moderators);		
	
			// prepare viewParams
			if ($parent instanceOf XenForo_ControllerResponse_View)
			{
				$viewParams = array(
					'modCount' => $modCount,
					'moderators' => $moderators
				);
				
				// add viewParams to parent params
				$parent->params += $viewParams;
			}
		}
		
		// return parent
		return $parent;
	}
	
	public function actionModerators()
	{
		//########################################
		// Shows moderator link which brings up
		// an overlay.
		//########################################	
			
		// declare variables
		$mod = array();
		$superMod = array();
		$moderators	= array();
		$parentNodeId = '';
		$whereclause1 = '';
		$whereclause2 = '';	
		$nodeHierarchy = array();						
		
		// get forumId and forumName
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		
		// get forumName (URL Portion)
		$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);								

		// get database
		$db = XenForo_Application::get('db');
		
		//########################################
		// get moderators
		//########################################		
		
		// get forumTitle
		if ($forumId > 0)
		{
			$forumTitle = $db->fetchOne("
			SELECT title
			FROM kmk_node
			WHERE node_id = ?
			", $forumId);	
		}
		
		// get forumTitle (URL Portion)
		if ($forumId == '')
		{
			$forumTitle = $db->fetchOne("
			SELECT title
			FROM kmk_node
			WHERE title = ?
			", $forumName);	
		}		

		// if using (URL Portion) get forumId
		if ($forumId == 0)
		{
			$forumId = $db->fetchOne("
			SELECT node_id
			FROM kmk_node
			WHERE node_name = ?
			", $forumName);	
		}
		
		// continue only if we have a forumId number
		if ($forumId > 0)
		{
			// get viewable nodes
			$viewableNodes = $this->getModelFromCache('XenForo_Model_Node')->getViewableNodeList();
			
			// make sure forumId is viewable
			if (!array_key_exists($forumId, $viewableNodes))
			{
				throw $this->getNoPermissionResponseException();
			}

			//########################################
			// create whereclause
			
			// get breadcrumb data
			$breadcrumbData = $db->fetchOne("
				SELECT breadcrumb_data
				FROM kmk_node
				WHERE node_id = ?
			", $forumId);				

			// unserialize blob data
			$results = unserialize($breadcrumbData);
			
			// get nodeHierarchy
			foreach ($results as $k => $v)
			{
				$nodeHierarchy[] = $v['node_id'];	
			}
			
			if (!empty($nodeHierarchy))
			{
				// create whereclause2
				$whereclause1 = 'OR (kmk_moderator_content.content_id = ' . implode(' OR kmk_moderator_content.content_id = ', $nodeHierarchy);
				$whereclause1 = $whereclause1 . ')';					
			}
			
			// run query
			$mod = $db->fetchAll("
			SELECT kmk_user.*
			FROM kmk_moderator_content
			INNER JOIN kmk_user ON kmk_user.user_id = kmk_moderator_content.user_id
			WHERE kmk_moderator_content.content_id = ?
			AND kmk_moderator_content.content_type = 'node'
			$whereclause1
			ORDER BY kmk_user.username ASC
			", $forumId);
		}
		
		//########################################
		// exclude moderators
		//########################################
		
		// get options from Admin CP -> Options -> Forum Moderators -> Exclude Moderators
		$excludeModerators = XenForo_Application::get('options')->forumModeratorsExcludeModerators;
		
		if ($excludeModerators != '')
		{
			// remove trailing comma if there is one
			$excludeModerators = rtrim($excludeModerators, ',');
							
			// put into an array
			$excludeModeratorsArray = explode(',', $excludeModerators);
	
			foreach ($mod as $k => $v)
			{				
				// check for excluded moderators
				if (in_array($v['user_id'], $excludeModeratorsArray))
				{
					unset($mod[$k]);
				}
			}			
		}		

		//########################################
		// get super moderators
		//########################################
		
		// get options from Admin CP -> Options -> Forum Moderators -> Exclude Super Moderators
		$excludeSuperModerators = XenForo_Application::get('options')->forumModeratorsExcludeSuperModerators;
		
		// continue if need to exclude super moderators
		if ($excludeSuperModerators != '')
		{
			// remove trailing comma if there is one
			$excludeSuperModerators = rtrim($excludeSuperModerators, ',');
							
			// put into an array
			$excludeSuperModeratorsArray = explode(',', $excludeSuperModerators);

			// create whereclause2
			$whereclause2 = 'AND (kmk_moderator.user_id != ' . implode(' AND kmk_moderator.user_id != ', $excludeSuperModeratorsArray);
			$whereclause2 = $whereclause2 . ')';	
		}		
		
		// get super moderators
		$superMod = $db->fetchAll("
		SELECT kmk_user.*
		FROM kmk_moderator
		INNER JOIN kmk_user ON kmk_user.user_id = kmk_moderator.user_id
		WHERE kmk_moderator.is_super_moderator = '1'
		$whereclause2
		ORDER BY username ASC
		");	

		// merge arrays
		$moderators = array_merge($mod, $superMod);

		// count moderators
		$modCount = count($moderators);	

		//########################################
		// $moderators will have last_activity
		// but this data is only updated every
		// hour. We need to check if the session 
		// table has more current information.
		//########################################
		
		for ($i=0; $i<$modCount; $i++)
		{
			// get session view_date if there is one
			$viewDate = $db->fetchOne("
			SELECT view_date
			FROM kmk_session_activity
			WHERE user_id = ?
			", $moderators[$i]['user_id']);
			
			if ($viewDate != '')
			{
				$moderators[$i]['last_activity'] = $viewDate;
			}
		}		

		// sort multi-dimensional array by value
		function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
			$sort_col = array();
			foreach ($arr as $key=> $row) {
				$sort_col[$key] = $row[$col];
			}
		
			array_multisort($sort_col, $dir, $arr);
		}
		
		// sort by username
		array_sort_by_column($moderators, 'username');
		
		// count moderators
		$modCount = count($moderators);				
		
		// prepare viewParams
		$viewParams = array(
			'modCount' => $modCount,
			'moderators' => $moderators,
			'forumTitle' => $forumTitle
		);
		
		// send to template
		return $this->responseView('KomuKu_ForumModerators_ViewPublic_Forum','KomuKu_forummoderators_overlay',$viewParams);
	}
}